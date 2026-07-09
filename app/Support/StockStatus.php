<?php
// =============================================================
// 7NVENT - Stock Status helper
//
// Pure, side-effect-free logic for deriving an inventory item's status
// from its quantity and par level. Previously this same 3-line if/elseif
// was copy-pasted in InventoryController::store(), ::update(), and
// QRController::qrUpdate() — three independent places that could drift
// out of sync. Centralizing it here also makes it unit-testable without
// touching the database.
// =============================================================

class StockStatus {

    public const OUT_OF_STOCK = 'Out of Stock';
    public const LOW_STOCK    = 'Low Stock';
    public const IN_STOCK     = 'In-Stock';

    /**
     * Determine status from current quantity vs. par level.
     *
     * Rules:
     *   - quantity == 0            -> Out of Stock
     *   - 0 < quantity <= par level -> Low Stock
     *   - quantity > par level      -> In-Stock
     *
     * A par level of 0 means "no threshold set" — any positive quantity
     * is treated as In-Stock rather than perpetually Low Stock.
     */
    public static function determine(int $quantity, int $parLevel): string {
        if ($quantity <= 0) {
            return self::OUT_OF_STOCK;
        }
        if ($parLevel > 0 && $quantity <= $parLevel) {
            return self::LOW_STOCK;
        }
        return self::IN_STOCK;
    }

    /** Percentage of par level currently in stock, capped at 100. */
    public static function percentOfPar(int $quantity, int $parLevel): int {
        if ($parLevel <= 0) {
            return 100;
        }
        return (int)min(100, round($quantity / $parLevel * 100));
    }
}
