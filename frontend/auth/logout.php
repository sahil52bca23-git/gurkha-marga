<?php
session_start();
require_once '../includes/jwt.php';

// Clear session
session_unset();
session_destroy();

// Clear JWT cookie
JWT::clearTokenCookie();

// Redirect to login
header('Location: ../index.php');
exit();
?>