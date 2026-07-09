<?php
// =============================================================
// 7NVENT - PHPUnit bootstrap
//
// Only loads the pure, side-effect-free classes under app/Support/.
// Controllers are NOT loaded here on purpose — they call Auth::required(),
// db(), redirect(), etc. and would need a full HTTP/DB/session context to
// run, which is out of scope for this unit-test pass. The Support classes
// were extracted specifically so the business-rule math (stock status
// thresholds, FIFO urgency classification) could be tested in isolation.
// =============================================================

require_once __DIR__ . '/../app/Support/StockStatus.php';
require_once __DIR__ . '/../app/Support/FifoPriority.php';
