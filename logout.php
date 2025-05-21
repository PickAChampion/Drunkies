<?php
session_start();

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect based on user type
if ($is_admin) {
    header('Location: login.php');
} else {
    header('Location: index.php');
}
exit;
?> 