<?php

namespace App;

use Carbon\CarbonImmutable;

enum Profession: string
{
    case Doctor = 'doctor';
    case Nurse = 'nurse';
    case Midwife = 'midwife';
    case Pharmacist = 'pharmacist';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::Doctor => 'Doctor',
            self::Nurse => 'Nurse',
            self::Midwife => 'Midwife',
            self::Pharmacist => 'Pharmacist',
            self::Student => 'Student placement',
        };
    }

    /**
     * The registration the permit is based on. Students practise under their university instead.
     */
    public function licenseLabel(): ?string
    {
        return match ($this) {
            self::Doctor => 'STR',
            self::Nurse => 'SIP or STR',
            self::Midwife => 'SIB or STR',
            self::Pharmacist => 'SIA or STRA',
            self::Student => null,
        };
    }

    public function requiresLicense(): bool
    {
        return $this->licenseLabel() !== null;
    }

    /**
     * @return list<DocumentType>
     */
    public function requiredDocuments(): array
    {
        return match ($this) {
            self::Doctor => [
                DocumentType::ApplicationLetter, DocumentType::Diploma, DocumentType::ProfessionalLicense,
                DocumentType::PracticeStatement, DocumentType::SupervisorApproval, DocumentType::Photo,
                DocumentType::ProfessionalRecommendation, DocumentType::IdentityCard,
            ],
            self::Nurse => [
                DocumentType::ApplicationLetter, DocumentType::Diploma, DocumentType::ProfessionalLicense,
                DocumentType::PracticeStatement, DocumentType::HealthCertificate, DocumentType::Photo,
                DocumentType::ProfessionalRecommendation, DocumentType::IdentityCard,
            ],
            self::Midwife, self::Pharmacist => [
                DocumentType::ApplicationLetter, DocumentType::Diploma, DocumentType::ProfessionalLicense,
                DocumentType::PracticeStatement, DocumentType::SupervisorApproval, DocumentType::HealthCertificate,
                DocumentType::Photo, DocumentType::ProfessionalRecommendation, DocumentType::IdentityCard,
            ],
            self::Student => [
                DocumentType::ApplicationLetter, DocumentType::Diploma, DocumentType::UniversityLetter,
                DocumentType::Photo, DocumentType::StudentCard,
            ],
        };
    }

    /**
     * When an approved permit lapses: with the professional license, or a year from approval for students.
     */
    public function permitExpiry(?CarbonImmutable $licenseExpiresOn, CarbonImmutable $approvedOn): ?CarbonImmutable
    {
        return $this->requiresLicense() ? $licenseExpiresOn : $approvedOn->addYear();
    }
}
