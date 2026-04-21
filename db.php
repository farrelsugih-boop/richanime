<?php
$db_file = __DIR__ . '/database.sqlite';
$is_new = !file_exists($db_file);

try {
    $pdo = new PDO("sqlite:$db_file");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if ($is_new) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS anime (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS episodes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            anime_id INTEGER NOT NULL,
            episode_number INTEGER NOT NULL,
            download_link TEXT NOT NULL,
            ad_link TEXT,
            ad_redirect_count INTEGER DEFAULT 0,
            FOREIGN KEY (anime_id) REFERENCES anime(id) ON DELETE CASCADE
        )");
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
