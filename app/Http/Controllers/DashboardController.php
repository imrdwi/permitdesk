<?php

namespace App\Http\Controllers;

use App\Models\PermitApplication;
use App\Models\User;
use App\PermitStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->isOffice()) {
            return Inertia::render('Dashboard', [
                'mode' => 'applicant',
                'checklist' => [
                    ['label' => __('Personal and professional details'), 'done' => $user->applicantProfile()->exists(), 'href' => route('applicant.profile.edit')],
                    ['label' => __('Required documents'), 'done' => $user->missingDocuments() === [], 'href' => route('applicant.documents.index')],
                ],
                'applications' => $user->permitApplications()->latest('submitted_at')->limit(5)->get(['id', 'practice_name', 'status', 'expires_on', 'is_temporary']),
            ]);
        }

        $counts = PermitApplication::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return Inertia::render('Dashboard', [
            'mode' => 'office',
            'stats' => collect(PermitStatus::cases())->map(fn (PermitStatus $status) => [
                'status' => $status->value,
                'label' => $status->label(),
                'value' => (int) ($counts[$status->value] ?? 0),
            ]),
            'expiring' => PermitApplication::query()->expiringWithin(30)
                ->with('applicant.applicantProfile:id,user_id,full_name')
                ->orderBy('expires_on')->limit(8)->get()
                ->map(fn (PermitApplication $permit) => [
                    'id' => $permit->id,
                    'practice_name' => $permit->practice_name,
                    'applicant' => $permit->applicant?->applicantProfile?->full_name,
                    'expires_on' => $permit->expires_on,
                ]),
        ]);
    }
}
