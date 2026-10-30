<?php

namespace App\Policies;

use App\Models\PermitApplication;
use App\Models\User;
use App\PermitStatus;
use App\UserRole;

class PermitApplicationPolicy
{
    public function view(User $user, PermitApplication $application): bool
    {
        return $user->isOffice() || $application->applicant_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Applicant;
    }

    /**
     * Applicants may correct or withdraw an application until the office starts reviewing it.
     */
    public function update(User $user, PermitApplication $application): bool
    {
        return $application->applicant_id === $user->id && $application->status === PermitStatus::Pending;
    }

    public function delete(User $user, PermitApplication $application): bool
    {
        return $user->isOffice() || $this->update($user, $application);
    }

    public function process(User $user): bool
    {
        return $user->isOffice();
    }

    /**
     * The printed permit exists only once approved; the applicant may print their own.
     */
    public function print(User $user, PermitApplication $application): bool
    {
        return in_array($application->status, [PermitStatus::Approved, PermitStatus::Expired], true) && $this->view($user, $application);
    }
}
