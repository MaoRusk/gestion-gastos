<?php
/* Database configuration - Using environment variables for production */
// Use environment variables if available (for Render.com), otherwise use defaults for local development
// Start with individual env vars / defaults
define('DB_TYPE', getenv('DB_TYPE') ?: 'postgresql'); // 'mysql', 'postgresql', or 'sqlite'
define('DB_SERVER', getenv('DB_HOST') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USER') ?: 'root');
// Default local PostgreSQL password for development. Change or set env var DB_PASSWORD in production.
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '1234');
define('DB_NAME', getenv('DB_NAME') ?: 'fime_gastos_db');
define('DB_PORT', getenv('DB_PORT') ?: '5432'); // PostgreSQL default port

// If a DATABASE_URL is provided (e.g. Render/Heroku), parse it and override the above values.
// Expected format: postgres://user:pass@host:port/dbname or mysql://... or sqlite:///path
$database_url = getenv('DATABASE_URL') ?: getenv('DATABASE_URL_STRING') ?: false;
if ($database_url) {
    $parts = parse_url($database_url);
    if ($parts !== false) {
        if (isset($parts['scheme'])) {
            $scheme = strtolower($parts['scheme']);
            if (in_array($scheme, ['postgres', 'postgresql'])) {
                define('DB_TYPE', 'postgresql');
            } elseif (in_array($scheme, ['mysql'])) {
                define('DB_TYPE', 'mysql');
            } elseif ($scheme === 'sqlite') {
                define('DB_TYPE', 'sqlite');
            }
        }
        if (isset($parts['host'])) {
            define('DB_SERVER', $parts['host']);
        }
        if (isset($parts['port'])) {
            define('DB_PORT', $parts['port']);
        }
        if (isset($parts['user'])) {
            define('DB_USERNAME', $parts['user']);
        }
        if (isset($parts['pass'])) {
            define('DB_PASSWORD', $parts['pass']);
        }
        if (isset($parts['path'])) {
            // path starts with '/', remove it
            $db = ltrim($parts['path'], '/');
            if ($db !== '') {
                define('DB_NAME', $db);
            }
        }
    }
}
define('DB_FILE', 'database/fime_gastos_db.db');

// Create database directory if it doesn't exist
if (!file_exists('database')) {
    mkdir('database', 0755, true);
}

// Try to connect to database
if (DB_TYPE === 'sqlite' && extension_loaded('pdo_sqlite')) {
    /* Attempt to connect to SQLite database using PDO */
    try {
        $pdo = new PDO("sqlite:" . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Create a mysqli-like interface using PDO
        $link = new stdClass();
        $link->pdo = $pdo;
        $link->type = 'sqlite';
    } catch(PDOException $e) {
        die("ERROR: Could not connect to SQLite. " . $e->getMessage());
    }
} elseif (DB_TYPE === 'postgresql' && extension_loaded('pdo_pgsql')) {
    /* Attempt to connect to PostgreSQL database using PDO */
    try {
        $dsn = "pgsql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Create a mysqli-like interface using PDO
        $link = new stdClass();
        $link->pdo = $pdo;
        $link->type = 'postgresql';
    } catch(PDOException $e) {
        // Mostrar información de depuración en caso de error
        $error_msg = "ERROR: Could not connect to PostgreSQL.\n";
        $error_msg .= "Connection String: pgsql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME . "\n";
        $error_msg .= "User: " . DB_USERNAME . "\n";
        $error_msg .= "Error: " . $e->getMessage() . "\n\n";
        $error_msg .= "Debug: Visita /debug_config.php para ver la configuración completa.";
        die($error_msg);
    }
} elseif (extension_loaded('mysqli')) {
    /* Attempt to connect to MySQL database using mysqli */
    $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if($link === false){
        die("ERROR: Could not connect to MySQL. " . mysqli_connect_error());
    }
    // Add type property to mysqli connection object
    if (is_object($link)) {
        $link->type = 'mysql';
    }
} elseif (extension_loaded('pdo_mysql')) {
    /* Attempt to connect to MySQL database using PDO */
    try {
        $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Create a mysqli-like interface using PDO
        $link = new stdClass();
        $link->pdo = $pdo;
        $link->type = 'mysql';
    } catch(PDOException $e) {
        die("ERROR: Could not connect to MySQL. " . $e->getMessage());
    }
} else {
    die("ERROR: No database extensions available. Please install php-sqlite3, php-mysqli, php-pdo_mysql, or php-pdo_pgsql");
}

$gmailid = ''; // YOUR gmail email
$gmailpassword = ''; // YOUR gmail password
$gmailusername = ''; // YOUR gmail User name

// Include database compatibility functions if needed
// CRITICAL: When using PDO (link->pdo exists), we MUST load compatibility functions
// BEFORE any mysqli functions are called, because native mysqli won't work with stdClass $link
if (isset($link) && isset($link->pdo)) {
    // Using PDO - MUST load compatibility functions
    // Note: Even if mysqli is loaded, we'll handle it in database_compat.php
    require_once __DIR__ . '/../includes/database_compat.php';
} elseif (isset($link) && !extension_loaded('mysqli') && (extension_loaded('pdo_mysql') || extension_loaded('pdo_sqlite') || extension_loaded('pdo_pgsql'))) {
    // Not using PDO but mysqli not available and PDO is available
    require_once __DIR__ . '/../includes/database_compat.php';
}

?>