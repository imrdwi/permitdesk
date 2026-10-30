<?php

namespace App\Http\Controllers\Applicant;

use App\Gender;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicantProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The applicant's personal and professional details, which every application uses.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('applicant/Profile', [
            'profile' => $user->applicantProfile,
            'profession' => ['value' => $user->profession?->value, 'label' => $user->profession?->label(), 'licenseLabel' => $user->profession?->licenseLabel()],
            'genders' => array_map(fn (Gender $gender) => $gender->value, Gender::cases()),
        ]);
    }

    public function update(ApplicantProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->applicantProfile()->updateOrCreate([], $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile saved.')]);

        return to_route('applicant.profile.edit');
    }
}
