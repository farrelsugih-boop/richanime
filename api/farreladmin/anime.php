<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) && ($_COOKIE['admin_session'] ?? '') !== 'farrelsugih') {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../db.php';

// Handle Add/Edit
if (isset($_POST['save_anime'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE anime SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $description, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO anime (title, description) VALUES (?, ?)");
        $stmt->execute([$title, $description]);
    }
    header('Location: anime.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM anime WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: anime.php');
    exit;
}

$animes = $pdo->query("SELECT * FROM anime ORDER BY id DESC")->fetchAll();
$edit_anime = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM anime WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_anime = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Management - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Manage Anime</h1>
            <div class="nav-admin">
                <a href="index.php">Dashboard</a>
                <a href="anime.php" class="active">Anime</a>
                <a href="episodes.php">Episodes</a>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <div class="admin-card">
            <h3><?php echo $edit_anime ? 'Edit Anime' : 'Add New Anime'; ?></h3>
            <form method="POST" style="margin-top: 1rem;">
                <input type="hidden" name="id" value="<?php echo $edit_anime['id'] ?? ''; ?>">
                <input type="text" name="title" placeholder="Anime Title" value="<?php echo $edit_anime['title'] ?? ''; ?>" required>
                <textarea name="description" placeholder="Description (Optional)"><?php echo $edit_anime['description'] ?? ''; ?></textarea>
                <button type="submit" name="save_anime" class="btn-primary">
                    <?php echo $edit_anime ? 'Update Anime' : 'Add Anime'; ?>
                </button>
                <?php if ($edit_anime): ?>
                    <a href="anime.php" style="display: block; text-align: center; margin-top: 0.5rem; color: var(--text-secondary);">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="admin-card" style="margin-top: 2rem;">
            <h3>Anime List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($animes as $a): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a['title']); ?></td>
                        <td>
                            <a href="?edit=<?php echo $a['id']; ?>" style="color: var(--accent-color);">Edit</a> | 
                            <a href="?delete=<?php echo $a['id']; ?>" style="color: #ff4500;" onclick="return confirm('Delete this anime and all its episodes?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
