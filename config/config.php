<?php
// =============================================================
// 7NVENT - Configuration File
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
// CHANGE THIS before any real deployment — a placeholder dev secret is
// intentionally obvious so it doesn't get mistaken for something safe to
// ship as-is.
define('JWT_SECRET',      'CHANGE_ME_7nvent_dev_secret_2026');
define('JWT_TTL_SECONDS', 60 * 60 * 12); // 12 hours

date_default_timezone_set('Asia/Kuala_Lumpur');

error_reporting(E_ALL);
ini_set('display_errors', 1);