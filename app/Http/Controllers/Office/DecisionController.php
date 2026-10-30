<?php

namespace App\Http\Controllers\Office;

use App\Exceptions\InvalidPermitTransition;
use App\Http\Controllers\Controller;
use App\Models\PermitApplication;
use App\Models\User;
use App\Notifications\PermitDecided;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Moves an application through review. Each action is a separate endpoint
 * so a stale page cannot skip a step: the model rejects invalid transitions.
 */
class DecisionController extends Controller
{
    public function review(Request $request, PermitApplication $application): RedirectResponse
    {
        return $this->decide($request, $application, fn (User $reviewer) => $application->startReview($reviewer), __('Review started.'));
    }

    public function approve(Request $request, PermitApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'permit_number' => ['required', 'string', 'max:50', Rule::unique('permit_applications', 'permit_number')],
        ]);

        return $this->decide($request, $application, fn (User $reviewer) => $application->approve($reviewer, $validated['permit_number']), __('Permit approved.'), notify: true);
    }

    public function reject(Request $request, PermitApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        return $this->decide($request, $application, fn (User $reviewer) => $application->reject($reviewer, $validated['reason']), __('Application rejected.'), notify: true);
    }

    /**
     * @param  Closure(User): void  $action
     */
    private function decide(Request $request, PermitApplication $application, Closure $action, string $message, bool $notify = false): RedirectResponse
    {
        Gate::authorize('process', PermitApplication::class);

        /** @var User $reviewer */
        $reviewer = $request->user();

        try {
            $action($reviewer);
        } catch (InvalidPermitTransition $exception) {
            throw ValidationException::withMessages(['decision' => $exception->getMessage()]);
        }

        if ($notify) {
            $application->applicant?->notify(new PermitDecided($application));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('office.applications.show', $application);
    }
}
