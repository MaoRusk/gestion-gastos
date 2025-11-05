<?php
/* Database configuration - Using environment variables for production */
// Use environment variables if available (for Render.com), otherwise use defaults for local development
define('DB_TYPE', getenv('DB_TYPE') ?: 'postgresql'); // 'mysql', 'postgresql', or 'sqlite'
define('DB_SERVER', getenv('DB_HOST') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'fime_gastos');
define('DB_PORT', getenv('DB_PORT') ?: '5432'); // PostgreSQL default port
define('DB_FILE', 'database/fime_gastos.db');

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
        $pdo = new PDO("pgsql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Create a mysqli-like interface using PDO
        $link = new stdClass();
        $link->pdo = $pdo;
        $link->type = 'postgresql';
    } catch(PDOException $e) {
        die("ERROR: Could not connect to PostgreSQL. " . $e->getMessage());
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
if (!extension_loaded('mysqli') && (extension_loaded('pdo_mysql') || extension_loaded('pdo_sqlite') || extension_loaded('pdo_pgsql'))) {
    require_once 'includes/database_compat.php';
}

?>