<?php
session_start();

// Set session untuk alert logout
$_SESSION['logout_message'] = 'Anda telah berhasil logout!';

// Hancurkan semua session data
session_destroy();

// Redirect ke login page
header('Location: login.php');
exit();
?>