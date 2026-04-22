<?php
require_once __DIR__ . '/db.php';
$animes = $pdo->query("SELECT * FROM anime ORDER BY title ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Download - Premium Links</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="shortcut icon" href="sukuna.png" type="image/x-icon">
</head>
<body>
    <div class="container">
        <header>
            <img src="sukuna.png" alt="Logo" class="profile-img">
            <h1>Anime Download</h1>
            <p class="subtitle">Select a title to see available episodes</p>
        </header>

        <div class="link-list">
            <?php if (empty($animes)): ?>
                <p style="text-align: center; color: var(--text-secondary);">No anime titles added yet.</p>
            <?php endif; ?>
            
            <?php foreach ($animes as $a): ?>
            <a href="anime.php?id=<?php echo $a['id']; ?>" class="link-item">
                <?php echo htmlspecialchars($a['title']); ?>
            </a>
            <?php endforeach; ?>
        </div>

        <footer style="margin-top: 3rem; text-align: center; color: var(--text-secondary); font-size: 0.8rem;">
            #BeHappy❤️
        </footer>
    </div>
</body>
</html>
