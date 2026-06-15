<?php

namespace App\Enums;

enum ComplianceStatus: string
{
    case Compliant = 'compliant';
    case PartiallyCompliant = 'partially_compliant';
    case NonCompliant = 'non_compliant';

    public function label(): string
    {
        return match ($this) {
            self::Compliant => 'Compliant',
            self::PartiallyCompliant => 'Partially Compliant',
            self::NonCompliant => 'Non-Compliant',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Compliant => 'green',
            self::PartiallyCompliant => 'yellow',
            self::NonCompliant => 'red',
        };
    }
}
