<?php
// =============================================================
// 7NVENT - Configuration (example)
// Copy this file to config.php and edit the values for your machine.
// config.php is git-ignored, so real credentials never get committed.
// =============================================================

define('DB_HOST',    'localhost');
define('DB_NAME',    '7nvent');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME',    '7NVENT');
define('APP_URL',     'http://localhost/7nvent/public');
define('APP_VERSION', '1.0.0');

define('SESSION_TIMEOUT',    1800);
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_DURATION',   900);

// Used to sign JWTs issued to the Flutter mobile app (stateless bearer-token
// auth — the web app's PHP session cookies don't carry over to a mobile
// client, so /api/* routes use this instead of Auth::check()).
// Generate a long random value, for example: php -r "echo bin2hex(random_bytes(32));"
// Never reuse the example value, and never commit the real one.
define('JWT_SECRET',      'replace-with-a-long-random-string');
define('JWT_TTL_SECONDS', 60 * 60 * 12); // 12 hours

date_default_timezone_set('Asia/Kuala_Lumpur');

error_reporting(E_ALL);
ini_set('display_errors', 1); // set to 0 outside local development