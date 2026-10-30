<?php

namespace App;

enum UserRole: string
{
    case Head = 'head';
    case Staff = 'staff';
    case Applicant = 'applicant';

    /**
     * Office roles review applications; applicants only see their own.
     */
    public function isOffice(): bool
    {
        return $this !== self::Applicant;
    }
}
