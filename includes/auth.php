<?php
session_start();

function appBasePath() {
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $knownSegments = ['/admin', '/teacher', '/student', '/supervisor'];

    foreach ($knownSegments as $segment) {
        $position = stripos($script, $segment);
        if ($position !== false) {
            return substr($script, 0, $position);
        }
    }

    $base = dirname($script);
    return ($base === '/' || $base === '\\') ? '' : $base;
}

function appUrl($path) {
    $base = appBasePath();
    $path = '/' . ltrim($path, '/');
    return $base . $path;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireRole($role) {
    if (!isLoggedIn()) {
        header('Location: ' . appUrl('index.php'));
        exit();
    }
    if ($_SESSION['user_role'] !== $role) {
        die("Access Denied: You do not have permission to view this page.");
    }
}

function requireAnyRole($roles) {
    if (!isLoggedIn()) {
        header('Location: ' . appUrl('index.php'));
        exit();
    }
    if (!in_array($_SESSION['user_role'], $roles)) {
        die("Access Denied: You do not have permission to view this page.");
    }
}

function login($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: ' . appUrl('index.php'));
    exit();
}
?>
