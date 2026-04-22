<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) && ($_COOKIE['admin_session'] ?? '') !== 'farrelsugih') {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../db.php';

$anime_count = $pdo->query("SELECT COUNT(*) FROM anime")->fetchColumn();
$episode_count = $pdo->query("SELECT COUNT(*) FROM episodes")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Anime Download</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Dashboard</h1>
            <div class="nav-admin">
                <a href="index.php" class="active">Dashboard</a>
                <a href="anime.php">Anime</a>
                <a href="episodes.php">Episodes</a>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <div class="admin-card" style="display: flex; gap: 2rem; justify-content: space-around; text-align: center;">
            <div>
                <h2 style="font-size: 3rem; color: var(--accent-color);"><?php echo $anime_count; ?></h2>
                <p class="subtitle">Anime Titles</p>
            </div>
            <div style="width: 1px; background: var(--glass-border);"></div>
            <div>
                <h2 style="font-size: 3rem; color: var(--accent-color);"><?php echo $episode_count; ?></h2>
                <p class="subtitle">Total Episodes</p>
            </div>
        </div>

        <div style="margin-top: 2rem; width: 100%;">
            <a href="anime.php" class="link-item" style="margin-bottom: 1rem;">Manage Anime Titles</a>
            <a href="episodes.php" class="link-item">Manage Episodes & Links</a>
        </div>
    </div>
</body>
</html>
