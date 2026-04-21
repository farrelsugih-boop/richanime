<?php
require_once 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM anime WHERE id = ?");
$stmt->execute([$id]);
$anime = $stmt->fetch();

if (!$anime) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM episodes WHERE anime_id = ? ORDER BY episode_number ASC");
$stmt->execute([$id]);
$episodes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($anime['title']); ?> - Episodes</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <a href="index.php" style="color: var(--accent-color); text-decoration: none; font-size: 0.9rem; margin-bottom: 1rem; display: inline-block;">← Back to List</a>
            <h1><?php echo htmlspecialchars($anime['title']); ?></h1>
            <p class="subtitle"><?php echo htmlspecialchars($anime['description'] ?: 'Available Episodes'); ?></p>
        </header>

        <div class="link-list">
            <?php if (empty($episodes)): ?>
                <p style="text-align: center; color: var(--text-secondary);">No episodes available yet.</p>
            <?php endif; ?>
            
            <?php foreach ($episodes as $e): ?>
            <a href="<?php echo htmlspecialchars($e['download_link']); ?>" class="link-item" target="_blank">
                Episode <?php echo $e['episode_number']; ?> — <span style="color: var(--accent-color);">[Click to Download]</span>
            </a>
            <?php endforeach; ?>
        </div>

        <footer style="margin-top: 3rem; text-align: center; color: var(--text-secondary); font-size: 0.8rem;">
            #BeHappy❤️
        </footer>
    </div>
</body>
</html>
