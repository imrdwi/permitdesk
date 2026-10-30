<?php

namespace App\Models;

use App\PermitStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PermitEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One step in a permit's history. Events are only ever appended.
 *
 * @property int $id
 * @property int $permit_application_id
 * @property int|null $user_id
 * @property PermitStatus|null $from_status
 * @property PermitStatus $to_status
 * @property string|null $note
 * @property CarbonImmutable $created_at
 */
#[Fillable(['user_id', 'from_status', 'to_status', 'note'])]
class PermitEvent extends Model
{
    /** @use HasFactory<PermitEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => PermitStatus::class,
            'to_status' => PermitStatus::class,
        ];
    }

    /**
     * @return BelongsTo<PermitApplication, $this>
     */
    public function permitApplication(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class);
    }

    /**
     * Who made the change. Null for changes made by the scheduler.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
