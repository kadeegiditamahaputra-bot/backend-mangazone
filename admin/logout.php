<?php
/**
 * MangaZone Repository - Sign Out Session Handler
 * Clears all session data and destroys cookie parameters securely.
 */

session_start();

// Unset all session variables
$_SESSION = array();

// If session cookies are used, expire them immediately
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the server-side session
session_destroy();

// Redirect to GitHub-styled login portal
header("Location: login.php");
exit;