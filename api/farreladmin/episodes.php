<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) && ($_COOKIE['admin_session'] ?? '') !== 'farrelsugih') {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../db.php';

// Handle Single Save
if (isset($_POST['save_episode'])) {
    $anime_id = $_POST['anime_id'];
    $ep_num = $_POST['episode_number'];
    $dl_link = $_POST['download_link'];
    $ad_link = $_POST['ad_link'];
    $redir_count = $_POST['ad_redirect_count'];
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE episodes SET anime_id = ?, episode_number = ?, download_link = ?, ad_link = ?, ad_redirect_count = ? WHERE id = ?");
        $stmt->execute([$anime_id, $ep_num, $dl_link, $ad_link, $redir_count, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO episodes (anime_id, episode_number, download_link, ad_link, ad_redirect_count) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$anime_id, $ep_num, $dl_link, $ad_link, $redir_count]);
    }
    header('Location: episodes.php' . ($anime_id ? '?anime_id='.$anime_id : ''));
    exit;
}

// Handle Mass Save
if (isset($_POST['mass_save'])) {
    $anime_id = $_POST['anime_id'];
    $dl_links = explode("\n", str_replace("\r", "", $_POST['mass_download_links']));
    $ad_links = explode("\n", str_replace("\r", "", $_POST['mass_ad_links']));
    $redir_count = $_POST['mass_redirect_count'] ?: 0;
    
    // Get current max episode number for this anime
    $stmt = $pdo->prepare("SELECT MAX(episode_number) as max_ep FROM episodes WHERE anime_id = ?");
    $stmt->execute([$anime_id]);
    $current_max = $stmt->fetch()['max_ep'] ?: 0;

    foreach ($dl_links as $index => $dl) {
        $dl = trim($dl);
        if (empty($dl)) continue;
        
        $current_max++;
        $ad = isset($ad_links[$index]) ? trim($ad_links[$index]) : (isset($ad_links[0]) ? trim($ad_links[0]) : '');
        
        $stmt = $pdo->prepare("INSERT INTO episodes (anime_id, episode_number, download_link, ad_link, ad_redirect_count) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$anime_id, $current_max, $dl, $ad, $redir_count]);
    }
    header('Location: episodes.php?anime_id=' . $anime_id);
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM episodes WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: episodes.php' . (isset($_GET['anime_id']) ? '?anime_id='.$_GET['anime_id'] : ''));
    exit;
}

// Handle Mass Delete
if (isset($_POST['mass_delete'])) {
    $ids = $_POST['episode_ids'] ?? [];
    if (!empty($ids)) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM episodes WHERE id IN ($placeholders)");
        $stmt->execute($ids);
    }
    header('Location: episodes.php?anime_id=' . $_POST['anime_id']);
    exit;
}

$animes = $pdo->query("SELECT * FROM anime ORDER BY title ASC")->fetchAll();
$selected_anime_id = $_GET['anime_id'] ?? ($animes[0]['id'] ?? null);

$episodes = [];
if ($selected_anime_id) {
    $stmt = $pdo->prepare("SELECT * FROM episodes WHERE anime_id = ? ORDER BY episode_number ASC");
    $stmt->execute([$selected_anime_id]);
    $episodes = $stmt->fetchAll();
}

$edit_ep = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM episodes WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_ep = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Episode Management - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Manage Episodes</h1>
            <div class="nav-admin">
                <a href="index.php">Dashboard</a>
                <a href="anime.php">Anime</a>
                <a href="episodes.php" class="active">Episodes</a>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <div class="admin-card">
            <h3>Filter by Anime</h3>
            <form method="GET">
                <select name="anime_id" onchange="this.form.submit()">
                    <option value="">Select Anime</option>
                    <?php foreach ($animes as $a): ?>
                    <option value="<?php echo $a['id']; ?>" <?php echo $selected_anime_id == $a['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($a['title']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($selected_anime_id): ?>
        <div class="admin-card" style="margin-top: 2rem;">
            <h3><?php echo $edit_ep ? 'Edit Episode' : 'Add Single Episode'; ?></h3>
            <form method="POST" style="margin-top: 1rem;">
                <input type="hidden" name="id" value="<?php echo $edit_ep['id'] ?? ''; ?>">
                <input type="hidden" name="anime_id" value="<?php echo $selected_anime_id; ?>">
                <input type="number" name="episode_number" placeholder="Episode Number" value="<?php echo $edit_ep['episode_number'] ?? ''; ?>" required>
                <input type="text" name="download_link" placeholder="Download Link" value="<?php echo $edit_ep['download_link'] ?? ''; ?>" required>
                <input type="text" name="ad_link" placeholder="Ad Link (Optional)" value="<?php echo $edit_ep['ad_link'] ?? ''; ?>">
                <input type="number" name="ad_redirect_count" placeholder="Ad Redirect Count" value="<?php echo $edit_ep['ad_redirect_count'] ?? '0'; ?>">
                <button type="submit" name="save_episode" class="btn-primary">Save Episode</button>
            </form>
        </div>

        <?php if (!$edit_ep): ?>
        <div class="admin-card" style="margin-top: 2rem;">
            <h3>Mass Input Episodes</h3>
            <p class="subtitle" style="margin-bottom: 1rem;">Paste links line per line. Batch input will auto-increment episode numbers.</p>
            <form method="POST">
                <input type="hidden" name="anime_id" value="<?php echo $selected_anime_id; ?>">
                <textarea name="mass_download_links" rows="5" placeholder="Download Links (One per line)" required></textarea>
                <textarea name="mass_ad_links" rows="3" placeholder="Ad Links (One per line OR single link for all)"></textarea>
                <input type="number" name="mass_redirect_count" placeholder="Default Redirect Count (e.g. 2)">
                <button type="submit" name="mass_save" class="btn-primary">Mass Import</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="admin-card" style="margin-top: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>Episode List</h3>
                <button type="button" onclick="selectAll()" class="badge" style="cursor: pointer; background: var(--text-secondary); border: none;">Select All</button>
            </div>
            
            <form method="POST" id="mass-delete-form">
                <input type="hidden" name="anime_id" value="<?php echo $selected_anime_id; ?>">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Ep</th>
                            <th>Redirects</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($episodes as $e): ?>
                        <tr>
                            <td><input type="checkbox" name="episode_ids[]" value="<?php echo $e['id']; ?>" class="ep-checkbox"></td>
                            <td><?php echo $e['episode_number']; ?></td>
                            <td><span class="badge"><?php echo $e['ad_redirect_count']; ?>x</span></td>
                            <td>
                                <a href="?anime_id=<?php echo $selected_anime_id; ?>&edit=<?php echo $e['id']; ?>" style="color: var(--accent-color);">Edit</a> | 
                                <a href="?anime_id=<?php echo $selected_anime_id; ?>&delete=<?php echo $e['id']; ?>" style="color: #ff4500;" onclick="return confirm('Delete this episode?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top: 1rem;">
                    <button type="submit" name="mass_delete" class="btn-primary" style="background: linear-gradient(135deg, #ff4500 0%, #b22222 100%);" onclick="return confirm('Delete selected episodes?')">Delete Selected</button>
                </div>
            </form>
        </div>

        <script>
            function selectAll() {
                const checkboxes = document.querySelectorAll('.ep-checkbox');
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                checkboxes.forEach(c => c.checked = !allChecked);
            }
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
