<?php

namespace App\Models;

use App\Exceptions\InvalidPermitTransition;
use App\PermitStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PermitApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A request for a practice permit at one location, and the permit it becomes once approved.
 *
 * @property int $id
 * @property int $applicant_id
 * @property bool $is_temporary
 * @property string $practice_name
 * @property string $practice_address
 * @property string|null $practice_email
 * @property string|null $practice_phone
 * @property string|null $practice_fax
 * @property PermitStatus $status
 * @property string|null $permit_number
 * @property string|null $rejection_reason
 * @property CarbonImmutable|null $expires_on
 * @property string|null $verification_code
 * @property CarbonImmutable $submitted_at
 * @property CarbonImmutable|null $decided_at
 * @property int|null $decided_by
 * @property CarbonImmutable|null $expiry_reminded_at
 */
#[Fillable(['is_temporary', 'practice_name', 'practice_address', 'practice_email', 'practice_phone', 'practice_fax'])]
class PermitApplication extends Model
{
    /** @use HasFactory<PermitApplicationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_temporary' => 'boolean',
            'status' => PermitStatus::class,
            'expires_on' => 'immutable_date',
            'submitted_at' => 'immutable_datetime',
            'decided_at' => 'immutable_datetime',
            'expiry_reminded_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * @return HasMany<PermitEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(PermitEvent::class)->orderBy('created_at')->orderBy('id');
    }

    /**
     * Approved permits whose expiry date has passed.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function lapsed(Builder $query): void
    {
        $query->where('status', PermitStatus::Approved)->whereDate('expires_on', '<', today());
    }

    /**
     * Approved permits expiring within the given number of days.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function expiringWithin(Builder $query, int $days): void
    {
        $query->where('status', PermitStatus::Approved)
            ->whereDate('expires_on', '>=', today())
            ->whereDate('expires_on', '<=', today()->addDays($days));
    }

    /**
     * File a new application and record its first event.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function submit(User $applicant, array $attributes): self
    {
        return DB::transaction(function () use ($applicant, $attributes): self {
            $application = new self($attributes);
            $application->applicant()->associate($applicant);
            $application->forceFill(['status' => PermitStatus::Pending, 'submitted_at' => now()])->save();
            $application->events()->create(['user_id' => $applicant->id, 'to_status' => PermitStatus::Pending]);

            return $application;
        });
    }

    public function startReview(User $reviewer): void
    {
        $this->transition(PermitStatus::InReview, $reviewer);
    }

    /**
     * Grant the permit. It lapses with the applicant's professional license,
     * or a year from today for student placements.
     */
    public function approve(User $reviewer, string $permitNumber): void
    {
        $this->transition(PermitStatus::Approved, $reviewer, "Permit number {$permitNumber}", function (self $application) use ($reviewer, $permitNumber): void {
            $applicant = $application->applicant()->with('applicantProfile')->firstOrFail();
            $profession = $applicant->profession;
            $expiresOn = $profession?->permitExpiry($applicant->applicantProfile?->license_expires_on, CarbonImmutable::today());

            if ($expiresOn === null || $expiresOn->isPast()) {
                throw new InvalidPermitTransition("The applicant's professional license has expired, so no permit can be issued against it.");
            }

            $application->forceFill([
                'permit_number' => $permitNumber,
                'expires_on' => $expiresOn,
                'verification_code' => Str::upper(Str::random(12)),
                'decided_at' => now(),
                'decided_by' => $reviewer->id,
                'rejection_reason' => null,
            ]);
        });
    }

    public function reject(User $reviewer, string $reason): void
    {
        $this->transition(PermitStatus::Rejected, $reviewer, $reason, function (self $application) use ($reviewer, $reason): void {
            $application->forceFill([
                'rejection_reason' => $reason,
                'decided_at' => now(),
                'decided_by' => $reviewer->id,
            ]);
        });
    }

    /**
     * Mark a lapsed permit as expired. Called by the scheduler, so there is no actor.
     */
    public function expire(): void
    {
        $this->transition(PermitStatus::Expired, null, 'Reached its expiry date');
    }

    public function isValidOn(CarbonImmutable $date): bool
    {
        return $this->status === PermitStatus::Approved && $this->expires_on !== null && $this->expires_on->gte($date->startOfDay());
    }

    /**
     * Move to a new status under a row lock, so two reviewers acting at
     * once cannot both decide the same application.
     *
     * @param  (callable(self): void)|null  $apply
     */
    private function transition(PermitStatus $to, ?User $actor, ?string $note = null, ?callable $apply = null): void
    {
        DB::transaction(function () use ($to, $actor, $note, $apply): void {
            $locked = self::query()->lockForUpdate()->findOrFail($this->id);
            $from = $locked->status;

            if (! $from->canTransitionTo($to)) {
                throw InvalidPermitTransition::between($from, $to);
            }

            if ($apply !== null) {
                $apply($locked);
            }

            $locked->forceFill(['status' => $to])->save();
            $locked->events()->create([
                'user_id' => $actor?->id,
                'from_status' => $from,
                'to_status' => $to,
                'note' => $note,
            ]);

            $this->setRawAttributes($locked->getAttributes(), true);
        });
    }
}
