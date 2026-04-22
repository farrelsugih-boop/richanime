<?php
session_start();
require_once __DIR__ . '/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM episodes WHERE id = ?");
$stmt->execute([$id]);
$ep = $stmt->fetch();

if (!$ep) {
    header('Location: index.php');
    exit;
}

// Redirect logic
$ad_redirect_count = (int)$ep['ad_redirect_count'];
$ad_link = $ep['ad_link'];
$download_link = $ep['download_link'];

// If no redirects needed
if ($ad_redirect_count <= 0) {
    header('Location: ' . $download_link);
    exit;
}

// Track steps in session
$session_key = "ep_redir_" . $id;
if (!isset($_SESSION[$session_key])) {
    $_SESSION[$session_key] = 0;
}

// If user clicks "Unlock" (comes from POST or GET param)
if (isset($_POST['unlock'])) {
    $_SESSION[$session_key]++;
    
    // If we've reached the limit
    if ($_SESSION[$session_key] >= $ad_redirect_count) {
        unset($_SESSION[$session_key]);
        header('Location: ' . $download_link);
        exit;
    } else {
        // Redirect to AD link and let them come back
        header('Location: ' . $ad_link);
        exit;
    }
}

// Prepare display
$current_step = $_SESSION[$session_key] + 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unlock Download - Step <?php echo $current_step; ?>/<?php echo $ad_redirect_count; ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <meta http-equiv="refresh" content="10;url=<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>"> <!-- Auto refresh to check session if needed -->
</head>
<body>
    <div class="container">
        <header>
            <h1>Unlocking Download...</h1>
            <p class="subtitle">Step <?php echo $current_step; ?> of <?php echo $ad_redirect_count; ?></p>
        </header>

        <div class="admin-card" style="text-align: center;">
            
            <form method="POST">
                <button type="submit" name="unlock" class="btn-primary" onclick="window.open('<?php echo htmlspecialchars($ad_link); ?>', '_blank')">
                    <?php echo $current_step == $ad_redirect_count ? 'Get Final Download Link' : 'Open Ad Link & Continue'; ?>
                </button>
            </form>
            
            <p style="margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-secondary);">
                Don't see anything? Make sure pop-ups are allowed or click the button manually.
            </p>
        </div>

        <footer style="margin-top: 3rem; text-align: center; color: var(--text-secondary); font-size: 0.8rem;">
            #BeHappy❤️
        </footer>
    </div>
</body>
</html>
