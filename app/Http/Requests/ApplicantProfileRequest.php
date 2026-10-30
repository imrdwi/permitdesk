<?php

namespace App\Http\Requests;

use App\Gender;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicantProfileRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();
        $needsLicense = (bool) $user->profession?->requiresLicense();

        return [
            'full_name' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:500'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:-17 years'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'graduation_year' => ['required', 'integer', 'between:1950,'.now()->year],
            'license_number' => [$needsLicense ? 'required' : 'prohibited', 'nullable', 'string', 'max:50'],
            'license_expires_on' => [$needsLicense ? 'required' : 'prohibited', 'nullable', 'date'],
            'university' => [$needsLicense ? 'nullable' : 'required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'mobile' => ['required', 'string', 'max:30'],
        ];
    }
}
