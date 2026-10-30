<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermitApplicationRequest;
use App\Models\PermitApplication;
use App\Models\User;
use App\PermitStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('create', PermitApplication::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('applicant/applications/Index', [
            'applications' => $user->permitApplications()->latest('submitted_at')->get(),
            'readiness' => $this->readiness($user),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        Gate::authorize('create', PermitApplication::class);

        /** @var User $user */
        $user = $request->user();
        $readiness = $this->readiness($user);

        if (! $readiness['ready']) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Complete your profile and documents before applying.')]);

            return to_route('applications.index');
        }

        return Inertia::render('applicant/applications/Form', [
            'application' => null,
            'mustBeTemporary' => $user->hasActivePrimaryPermit(),
        ]);
    }

    public function store(PermitApplicationRequest $request): RedirectResponse
    {
        Gate::authorize('create', PermitApplication::class);

        /** @var User $user */
        $user = $request->user();
        abort_unless($this->readiness($user)['ready'], 422, __('Complete your profile and documents before applying.'));

        $application = PermitApplication::submit($user, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Application submitted.')]);

        return to_route('applications.show', $application);
    }

    public function show(PermitApplication $application): Response
    {
        Gate::authorize('view', $application);

        return Inertia::render('applicant/applications/Show', [
            'application' => $application->load('events.user:id,name'),
            'canEdit' => Gate::allows('update', $application),
            'canPrint' => Gate::allows('print', $application),
        ]);
    }

    public function edit(Request $request, PermitApplication $application): Response
    {
        Gate::authorize('update', $application);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('applicant/applications/Form', [
            'application' => $application,
            'mustBeTemporary' => $user->hasActivePrimaryPermit($application),
        ]);
    }

    public function update(PermitApplicationRequest $request, PermitApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        $application->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Application updated.')]);

        return to_route('applications.show', $application);
    }

    public function destroy(PermitApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        $application->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Application withdrawn.')]);

        return to_route('applications.index');
    }

    /**
     * What still stands between the applicant and a submittable application.
     *
     * @return array{ready: bool, hasProfile: bool, missingDocuments: list<string>, hasActivePrimary: bool}
     */
    private function readiness(User $user): array
    {
        $hasProfile = $user->applicantProfile()->exists();
        $missing = array_map(fn ($type) => $type->label(), $user->missingDocuments());

        return [
            'ready' => $hasProfile && $missing === [],
            'hasProfile' => $hasProfile,
            'missingDocuments' => $missing,
            'hasActivePrimary' => $user->permitApplications()->where('is_temporary', false)->whereIn('status', PermitStatus::active())->exists(),
        ];
    }
}
