<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Office accounts, managed by the head of office.
 */
class StaffController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('office/staff/Index', [
            'staff' => User::query()->whereIn('role', [UserRole::Head, UserRole::Staff])->orderBy('name')->get(['id', 'name', 'email', 'role']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('office/staff/Form', ['staffMember' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $staff = new User($validated);
        $staff->forceFill(['role' => UserRole::from($validated['role']), 'email_verified_at' => now()])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account created.')]);

        return to_route('office.staff.index');
    }

    public function edit(User $user): Response
    {
        abort_unless($user->isOffice(), 404);

        return Inertia::render('office/staff/Form', ['staffMember' => $user->only('id', 'name', 'email', 'role')]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isOffice(), 404);

        $validated = $this->validated($request, $user);
        $user->fill(collect($validated)->only('name', 'email')->all());

        if (filled($validated['password'] ?? null)) {
            $user->password = $validated['password'];
        }

        if ($user->isNot($request->user())) {
            $user->role = UserRole::from($validated['role']);
        }

        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account updated.')]);

        return to_route('office.staff.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isOffice(), 404);
        abort_if($user->is($request->user()), 422, __('You cannot delete your own account here.'));

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account deleted.')]);

        return to_route('office.staff.index');
    }

    /**
     * @return array{name: string, email: string, role: string, password?: string|null}
     */
    private function validated(Request $request, ?User $user = null): array
    {
        /** @var array{name: string, email: string, role: string, password?: string|null} */
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user?->id)],
            'role' => ['required', Rule::in([UserRole::Head->value, UserRole::Staff->value])],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::defaults()],
        ]);
    }
}
