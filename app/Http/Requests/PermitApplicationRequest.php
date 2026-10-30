<?php

namespace App\Http\Requests;

use App\Models\PermitApplication;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PermitApplicationRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_temporary' => ['boolean'],
            'practice_name' => ['required', 'string', 'max:150'],
            'practice_address' => ['required', 'string', 'max:500'],
            'practice_email' => ['nullable', 'email', 'max:150'],
            'practice_phone' => ['nullable', 'string', 'max:30'],
            'practice_fax' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * One primary practice permit at a time; temporary locations are unlimited.
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            /** @var User $user */
            $user = $this->user();
            $current = $this->route('application');

            if (! $this->boolean('is_temporary') && $user->hasActivePrimaryPermit($current instanceof PermitApplication ? $current : null)) {
                $validator->errors()->add('is_temporary', __('You already have a primary practice permit or application. Apply for this location as a temporary practice.'));
            }
        }];
    }
}
