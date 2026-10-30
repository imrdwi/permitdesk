<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\PermitApplication;
use App\PermitStatus;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The monthly report: every application decided or still open in the chosen month.
 */
class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $month = CarbonImmutable::createFromFormat('Y-m', $validated['month'] ?? now()->format('Y-m'))->startOfMonth();
        $range = [$month, $month->endOfMonth()];

        $applications = PermitApplication::withTrashed()
            ->with('applicant:id,name,profession', 'applicant.applicantProfile:id,user_id,full_name')
            ->where(fn ($query) => $query->whereBetween('submitted_at', $range)->orWhereBetween('decided_at', $range))
            ->orderBy('submitted_at')
            ->get()
            ->map(fn (PermitApplication $application) => [
                'id' => $application->id,
                'applicant' => $application->applicant?->applicantProfile->full_name ?? $application->applicant?->name,
                'profession' => $application->applicant?->profession?->label(),
                'practice_name' => $application->practice_name,
                'is_temporary' => $application->is_temporary,
                'status' => $application->status->label(),
                'permit_number' => $application->permit_number,
                'submitted_at' => $application->submitted_at,
                'decided_at' => $application->decided_at,
            ]);

        return Inertia::render('office/Report', [
            'month' => $month->format('Y-m'),
            'monthLabel' => $month->translatedFormat('F Y'),
            'applications' => $applications,
            'totals' => collect(PermitStatus::cases())->mapWithKeys(fn (PermitStatus $status) => [
                $status->label() => $applications->where('status', $status->label())->count(),
            ]),
        ]);
    }
}
