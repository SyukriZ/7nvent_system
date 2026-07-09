<?php

use PHPUnit\Framework\TestCase;

/**
 * Covers app/Support/StockStatus.php
 *
 * These test the exact boundary rules used across InventoryController::store(),
 * InventoryController::update(), and QRController::qrUpdate() — the three
 * places that previously each duplicated this if/elseif logic inline.
 */
final class StockStatusTest extends TestCase
{
    public function testZeroQuantityIsOutOfStock(): void
    {
        $this->assertSame(StockStatus::OUT_OF_STOCK, StockStatus::determine(0, 10));
    }

    public function testNegativeQuantityIsOutOfStock(): void
    {
        // Defensive case: should never happen once controller validation is in
        // place, but the helper itself must still behave sanely if it did.
        $this->assertSame(StockStatus::OUT_OF_STOCK, StockStatus::determine(-5, 10));
    }

    public function testQuantityEqualToParLevelIsLowStock(): void
    {
        $this->assertSame(StockStatus::LOW_STOCK, StockStatus::determine(10, 10));
    }

    public function testQuantityBelowParLevelIsLowStock(): void
    {
        $this->assertSame(StockStatus::LOW_STOCK, StockStatus::determine(3, 10));
    }

    public function testQuantityAboveParLevelIsInStock(): void
    {
        $this->assertSame(StockStatus::IN_STOCK, StockStatus::determine(11, 10));
    }

    public function testParLevelZeroWithPositiveQuantityIsInStock(): void
    {
        // A par level of 0 means "no threshold set" — should not be
        // perpetually flagged Low Stock just because quantity <= 0 is false.
        $this->assertSame(StockStatus::IN_STOCK, StockStatus::determine(5, 0));
    }

    public function testParLevelZeroWithZeroQuantityIsOutOfStock(): void
    {
        $this->assertSame(StockStatus::OUT_OF_STOCK, StockStatus::determine(0, 0));
    }

    public function testPercentOfParCapsAt100(): void
    {
        $this->assertSame(100, StockStatus::percentOfPar(50, 10));
    }

    public function testPercentOfParExactMatch(): void
    {
        $this->assertSame(100, StockStatus::percentOfPar(10, 10));
    }

    public function testPercentOfParHalf(): void
    {
        $this->assertSame(50, StockStatus::percentOfPar(5, 10));
    }

    public function testPercentOfParZeroParLevelReturns100(): void
    {
        $this->assertSame(100, StockStatus::percentOfPar(5, 0));
    }

    public function testPercentOfParZeroQuantity(): void
    {
        $this->assertSame(0, StockStatus::percentOfPar(0, 10));
    }
}
