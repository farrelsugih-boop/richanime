<?php
session_start();
session_destroy();
// Clear the Vercel fix cookie
setcookie('admin_session', '', time() - 3600, "/");
header('Location: login.php');
exit;
?>
