<?php
// auth.php - Common authentication functions
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.html");
        exit();
    }
}

function getUsername() {
    return $_SESSION['username'] ?? '';
}

/*function getName() {
    return $_SESSION['name'] ?? '';
}*/

function logout() {
    session_unset();
    session_destroy();
    header("Location: login.html");
    exit();
}
?>