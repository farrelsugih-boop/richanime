<?php
session_start();
$error = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'farrelsugih' && $password === 'farrelsugih') {
        $_SESSION['admin_logged_in'] = true;
        // Fix for Vercel: Set a persistent cookie because sessions are stateless
        setcookie('admin_session', 'farrelsugih', time() + (86400 * 30), "/"); 
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Anime Download</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Admin Panel</h1>
            <p class="subtitle">Please login to continue</p>
        </header>

        <div class="admin-card">
            <?php if ($error): ?>
                <p style="color: #ff4500; margin-bottom: 1rem; text-align: center;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login" class="btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
