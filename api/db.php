<?php
// Load .env file if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
        $_ENV[trim($name)] = trim($value);
    }
}

// Database connection configuration
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$port = getenv('DB_PORT');
$db_url = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL');

try {
    if ($db_url) {
        // Support for Vercel Postgres or single DATABASE_URL string
        // If it starts with postgres://, we need to convert to pgsql:
        if (strpos($db_url, 'postgres://') === 0) {
            $db_url = str_replace('postgres://', 'pgsql:host=', $db_url);
            // This is a simplified conversion, standard PDO postgres DSN is different
            // Better to use individual params if possible, but let's handle common cases
        }
        $pdo = new PDO($db_url);
    } elseif ($host && $db) {
        // Determine driver based on port
        $driver = ($port == '5432') ? 'pgsql' : 'mysql';
        $port = $port ?: ($driver == 'pgsql' ? '5432' : '3306');
        $dsn = "$driver:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
    } else {
        // Fallback to local SQLite (Development only)
        $db_file = __DIR__ . '/../database.sqlite';
        $pdo = new PDO("sqlite:$db_file");
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Optimization: Detect driver for compatible schema creation
    $driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    // SQL Dialect adjustments
    $pk = "INTEGER PRIMARY KEY AUTOINCREMENT";
    $textType = "TEXT";
    if ($driverName == 'mysql') {
        $pk = "INT AUTO_INCREMENT PRIMARY KEY";
        $textType = "LONGTEXT"; // More robust for MySQL
    } elseif ($driverName == 'pgsql') {
        $pk = "SERIAL PRIMARY KEY";
    }

    // Initialize Schema
    $pdo->exec("CREATE TABLE IF NOT EXISTS anime (
        id $pk,
        title VARCHAR(255) NOT NULL,
        description $textType
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS episodes (
        id $pk,
        anime_id INT NOT NULL,
        episode_number INT NOT NULL,
        download_link $textType NOT NULL,
        ad_link $textType,
        ad_redirect_count INT DEFAULT 0
    )");

    // Optimization: Indexing
    // (Note: SQLite handles IF NOT EXISTS for INDEX, but some older MySQL might not)
    try {
        if ($driverName == 'mysql') {
            // Check if index exists first to avoid errors on older MySQL
            $exists = $pdo->query("SHOW INDEX FROM episodes WHERE Key_name = 'idx_anime_id'")->fetch();
            if (!$exists) {
                $pdo->exec("CREATE INDEX idx_anime_id ON episodes(anime_id)");
            }
        } else {
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_anime_id ON episodes(anime_id)");
        }
    } catch (Exception $e) {
        // Index might already exist or not supported by driver, ignore
    }

} catch (PDOException $e) {
    // Hidden in production, but helpful for debugging if specifically requested
    die("Database connection failed. Please check your environment variables.");
}
?>
