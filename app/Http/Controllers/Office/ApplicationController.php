<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\PermitApplication;
use App\PermitStatus;
use App\Profession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('process', PermitApplication::class);

        $applications = PermitApplication::query()
            ->with('applicant:id,name,profession', 'applicant.applicantProfile:id,user_id,full_name')
            ->when($request->string('status')->toString(), function (Builder $query, string $status): void {
                $status === 'expiring'
                    ? $query->expiringWithin(30)
                    : $query->where('status', PermitStatus::tryFrom($status) ?? PermitStatus::Pending);
            })
            ->when($request->string('profession')->toString(), fn (Builder $query, string $profession) => $query
                ->whereRelation('applicant', 'profession', Profession::tryFrom($profession)?->value))
            ->when($request->string('search')->toString(), fn (Builder $query, string $search) => $query->where(fn (Builder $matches) => $matches
                ->where('practice_name', 'like', "%{$search}%")
                ->orWhere('permit_number', 'like', "%{$search}%")
                ->orWhereRelation('applicant', 'name', 'like', "%{$search}%")))
            ->orderByRaw("case status when 'pending' then 0 when 'in_review' then 1 else 2 end")
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (PermitApplication $application) => [
                ...$application->only('id', 'practice_name', 'is_temporary', 'permit_number', 'submitted_at', 'expires_on'),
                'status' => $application->status->value,
                'applicant' => $application->applicant?->applicantProfile->full_name ?? $application->applicant?->name,
                'profession' => $application->applicant?->profession?->label(),
            ]);

        return Inertia::render('office/applications/Index', [
            'applications' => $applications,
            'filters' => $request->only('search', 'status', 'profession'),
            'statuses' => array_map(fn (PermitStatus $status) => ['value' => $status->value, 'label' => $status->label()], PermitStatus::cases()),
            'professions' => array_map(fn (Profession $profession) => ['value' => $profession->value, 'label' => $profession->label()], Profession::cases()),
        ]);
    }

    public function show(PermitApplication $application): Response
    {
        Gate::authorize('process', PermitApplication::class);

        $application->load('applicant.applicantProfile', 'applicant.documents', 'events.user:id,name', 'decider:id,name');
        $applicant = $application->applicant;
        $profile = $applicant?->applicantProfile;

        return Inertia::render('office/applications/Show', [
            'application' => [
                ...$application->only('id', 'is_temporary', 'practice_name', 'practice_address', 'practice_email', 'practice_phone', 'practice_fax',
                    'permit_number', 'rejection_reason', 'expires_on', 'submitted_at', 'decided_at', 'verification_code'),
                'status' => $application->status->value,
                'decider' => $application->decider?->name,
                'events' => $application->events->map(fn ($event) => [
                    'id' => $event->id,
                    'to_status' => $event->to_status->label(),
                    'note' => $event->note,
                    'by' => $event->user->name ?? 'System',
                    'at' => $event->created_at,
                ]),
            ],
            'applicant' => [
                'email' => $applicant?->email,
                'profession' => $applicant?->profession?->label(),
                'licenseLabel' => $applicant?->profession?->licenseLabel(),
                'profile' => $profile,
                'licenseExpired' => $profile?->license_expires_on?->isPast() ?? false,
                'documents' => $applicant?->documents->map(fn ($document) => [
                    'id' => $document->id,
                    'label' => $document->type->label(),
                    'original_name' => $document->original_name,
                ]) ?? [],
                'missingDocuments' => array_map(fn ($type) => $type->label(), $applicant?->missingDocuments() ?? []),
            ],
        ]);
    }

    public function destroy(PermitApplication $application): RedirectResponse
    {
        Gate::authorize('delete', $application);

        $application->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Application deleted.')]);

        return to_route('office.applications.index');
    }
}
