<?php

namespace App\Models;

use App\DocumentType;
use App\PermitStatus;
use App\Profession;
use App\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property UserRole $role
 * @property Profession|null $profession
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'role' => UserRole::class,
            'profession' => Profession::class,
        ];
    }

    /**
     * @return HasOne<ApplicantProfile, $this>
     */
    public function applicantProfile(): HasOne
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    /**
     * @return HasMany<ApplicantDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicantDocument::class);
    }

    /**
     * @return HasMany<PermitApplication, $this>
     */
    public function permitApplications(): HasMany
    {
        return $this->hasMany(PermitApplication::class, 'applicant_id');
    }

    public function isOffice(): bool
    {
        return $this->role->isOffice();
    }

    public function isHead(): bool
    {
        return $this->role === UserRole::Head;
    }

    /**
     * Required documents the applicant has not uploaded yet.
     *
     * @return list<DocumentType>
     */
    public function missingDocuments(): array
    {
        $uploaded = $this->documents()->pluck('type')->all();

        return array_values(array_filter(
            $this->profession?->requiredDocuments() ?? [],
            fn (DocumentType $type): bool => ! in_array($type, $uploaded, true),
        ));
    }

    /**
     * Whether the applicant has the active primary permit or application that blocks another one.
     */
    public function hasActivePrimaryPermit(?PermitApplication $except = null): bool
    {
        return $this->permitApplications()
            ->where('is_temporary', false)
            ->whereIn('status', PermitStatus::active())
            ->when($except, fn ($query) => $query->whereKeyNot($except?->id))
            ->exists();
    }
}
