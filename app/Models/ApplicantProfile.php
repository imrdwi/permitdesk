<?php

namespace App\Models;

use App\Gender;
use Carbon\CarbonImmutable;
use Database\Factories\ApplicantProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $full_name
 * @property string $address
 * @property string $birth_place
 * @property CarbonImmutable $birth_date
 * @property Gender $gender
 * @property int $graduation_year
 * @property string|null $license_number
 * @property CarbonImmutable|null $license_expires_on
 * @property string|null $university
 * @property string|null $phone
 * @property string $mobile
 */
#[Fillable([
    'full_name', 'address', 'birth_place', 'birth_date', 'gender', 'graduation_year',
    'license_number', 'license_expires_on', 'university', 'phone', 'mobile',
])]
class ApplicantProfile extends Model
{
    /** @use HasFactory<ApplicantProfileFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'immutable_date',
            'gender' => Gender::class,
            'graduation_year' => 'integer',
            'license_expires_on' => 'immutable_date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
