<?php

namespace App;

enum DocumentType: string
{
    case ApplicationLetter = 'application_letter';
    case Diploma = 'diploma';
    case ProfessionalLicense = 'professional_license';
    case PracticeStatement = 'practice_statement';
    case SupervisorApproval = 'supervisor_approval';
    case HealthCertificate = 'health_certificate';
    case Photo = 'photo';
    case ProfessionalRecommendation = 'professional_recommendation';
    case IdentityCard = 'identity_card';
    case UniversityLetter = 'university_letter';
    case StudentCard = 'student_card';

    public function label(): string
    {
        return match ($this) {
            self::ApplicationLetter => 'Signed application letter',
            self::Diploma => 'Diploma',
            self::ProfessionalLicense => 'Professional license (STR / SIP)',
            self::PracticeStatement => 'Statement of practice location',
            self::SupervisorApproval => "Supervisor's approval",
            self::HealthCertificate => 'Health certificate',
            self::Photo => 'Passport photo',
            self::ProfessionalRecommendation => 'Professional association recommendation',
            self::IdentityCard => 'Identity card (KTP)',
            self::UniversityLetter => 'Letter from the university',
            self::StudentCard => 'Student card',
        };
    }
}
