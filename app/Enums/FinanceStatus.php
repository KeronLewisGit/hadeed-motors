<?php

namespace App\Enums;

/** Where a pre-qualification sits with the finance desk (separate from the sales status). */
enum FinanceStatus: string
{
    case Received = 'received';
    case Reviewing = 'reviewing';
    case NeedsDocuments = 'needs_documents';
    case PreQualified = 'pre_qualified';
    case Declined = 'declined';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'Received',
            self::Reviewing => 'Reviewing',
            self::NeedsDocuments => 'Needs documents',
            self::PreQualified => 'Pre-qualified',
            self::Declined => 'Declined',
            self::Withdrawn => 'Withdrawn',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Received => 'attention',
            self::PreQualified => 'good',
            self::Declined => 'critical',
            self::Withdrawn => 'muted',
            default => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Received => 'dot',
            self::Reviewing => 'eye',
            self::NeedsDocuments => 'file',
            self::PreQualified => 'check-circle',
            self::Declined => 'x',
            self::Withdrawn => 'arrow-left',
        };
    }
}
