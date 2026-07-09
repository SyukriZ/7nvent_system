<?php

use PHPUnit\Framework\TestCase;

/**
 * Covers app/Support/FifoPriority.php
 *
 * Boundary values mirror the thresholds pulled out of the FIFO Enforcement
 * Queue markup in resources/views/inventory/index.php (days < 0, === 0,
 * <= 7, <= 30, else OK).
 */
final class FifoPriorityTest extends TestCase
{
    public function testAlreadyExpiredIsExpired(): void
    {
        $this->assertSame(FifoPriority::EXPIRED, FifoPriority::classify(-1));
    }

    public function testExpiredSeveralDaysAgoIsExpired(): void
    {
        $this->assertSame(FifoPriority::EXPIRED, FifoPriority::classify(-10));
    }

    public function testZeroDaysIsExpiresToday(): void
    {
        $this->assertSame(FifoPriority::EXPIRES_TODAY, FifoPriority::classify(0));
    }

    public function testOneDayIsUseNow(): void
    {
        $this->assertSame(FifoPriority::USE_NOW, FifoPriority::classify(1));
    }

    public function testSevenDaysIsUseNow(): void
    {
        // Upper boundary of the "USE NOW" bucket (<= 7).
        $this->assertSame(FifoPriority::USE_NOW, FifoPriority::classify(7));
    }

    public function testEightDaysIsUseNext(): void
    {
        // First day past the "USE NOW" boundary.
        $this->assertSame(FifoPriority::USE_NEXT, FifoPriority::classify(8));
    }

    public function testThirtyDaysIsUseNext(): void
    {
        // Upper boundary of the "USE NEXT" bucket (<= 30).
        $this->assertSame(FifoPriority::USE_NEXT, FifoPriority::classify(30));
    }

    public function testThirtyOneDaysIsOk(): void
    {
        // First day past the "USE NEXT" boundary.
        $this->assertSame(FifoPriority::OK, FifoPriority::classify(31));
    }

    public function testFarFutureIsOk(): void
    {
        $this->assertSame(FifoPriority::OK, FifoPriority::classify(365));
    }

    public function testDaysLabelForExpired(): void
    {
        $this->assertSame('5 day(s) ago', FifoPriority::daysLabel(-5));
    }

    public function testDaysLabelForToday(): void
    {
        $this->assertSame('Today!', FifoPriority::daysLabel(0));
    }

    public function testDaysLabelForFutureDays(): void
    {
        $this->assertSame('12 day(s) left', FifoPriority::daysLabel(12));
    }
}
