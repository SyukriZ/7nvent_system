<?php
// =============================================================
// 7NVENT - FIFO Priority helper
//
// Pure classification logic for the FIFO Enforcement Queue, extracted
// out of resources/views/inventory/index.php so it can be unit-tested
// without rendering HTML or touching the database.
// =============================================================

class FifoPriority {

    public const EXPIRED       = 'EXPIRED';
    public const EXPIRES_TODAY = 'EXPIRES_TODAY';
    public const USE_NOW       = 'USE_NOW';
    public const USE_NEXT      = 'USE_NEXT';
    public const OK            = 'OK';

    /**
     * Classify an item's urgency from days remaining until expiry.
     * (Negative days-left means it already expired that many days ago.)
     */
    public static function classify(int $daysLeft): string {
        if ($daysLeft < 0)  return self::EXPIRED;
        if ($daysLeft === 0) return self::EXPIRES_TODAY;
        if ($daysLeft <= 7)  return self::USE_NOW;
        if ($daysLeft <= 30) return self::USE_NEXT;
        return self::OK;
    }

    /** Human-readable "N day(s) left/ago" label for a classification + day count. */
    public static function daysLabel(int $daysLeft): string {
        if ($daysLeft < 0)  return abs($daysLeft) . ' day(s) ago';
        if ($daysLeft === 0) return 'Today!';
        return $daysLeft . ' day(s) left';
    }
}
