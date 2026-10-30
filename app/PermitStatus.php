<?php

namespace App;

enum PermitStatus: string
{
    case Pending = 'pending';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InReview => 'In review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Expired => 'Expired',
        };
    }

    /**
     * The only moves the workflow allows.
     */
    public function canTransitionTo(self $next): bool
    {
        return in_array($next, match ($this) {
            self::Pending => [self::InReview],
            self::InReview => [self::Approved, self::Rejected],
            self::Approved => [self::Expired],
            self::Rejected, self::Expired => [],
        }, true);
    }

    /**
     * Statuses that still occupy the applicant's single primary practice slot.
     *
     * @return list<self>
     */
    public static function active(): array
    {
        return [self::Pending, self::InReview, self::Approved];
    }
}
