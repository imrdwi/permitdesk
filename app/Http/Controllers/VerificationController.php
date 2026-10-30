<?php

namespace App\Http\Controllers;

use App\Models\PermitApplication;
use Illuminate\View\View;

/**
 * Public check of a printed permit: anyone holding one can confirm it is genuine and still valid.
 */
class VerificationController extends Controller
{
    public function __invoke(string $code): View
    {
        $permit = PermitApplication::query()
            ->with('applicant:id,profession', 'applicant.applicantProfile:id,user_id,full_name')
            ->where('verification_code', $code)
            ->first();

        return view('permits.verify', [
            'permit' => $permit,
            'valid' => $permit?->isValidOn(now()->toImmutable()) ?? false,
        ]);
    }
}
