<?php
session_start(); // Start the session to gain access to session variables.

// Check if the user is already logged in; if not, redirect to the login page.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Unset all of the session variables.
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header("location: ../index.php");
exit;
?>
