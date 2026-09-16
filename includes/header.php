<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth.php';
$role = $_SESSION['user_role'] ?? '';
$name = $_SESSION['user_name'] ?? '';
$basePath = appBasePath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KoKCS Online Assessment Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="<?= htmlspecialchars($basePath . '/assets/css/style.css') ?>" rel="stylesheet">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= htmlspecialchars($role ? $basePath . '/' . $role . '/dashboard.php' : $basePath . '/index.php') ?>">
                <i class="bi bi-rocket-takeoff-fill me-2"></i>KoKCS Online Assessment Portal
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($role === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($basePath . '/admin/dashboard.php') ?>"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($basePath . '/admin/users.php') ?>"><i class="bi bi-people me-1"></i> Students</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($basePath . '/admin/staff.php') ?>"><i class="bi bi-shield-lock me-1"></i> Staff</a></li>
                    <?php elseif ($role === 'supervisor'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($basePath . '/supervisor/dashboard.php') ?>"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($basePath . '/supervisor/approve_quizzes.php') ?>"><i class="bi bi-check2-circle me-1"></i> Approvals</a></li>
                    <?php elseif ($role === 'teacher'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($basePath . '/teacher/dashboard.php') ?>"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($basePath . '/teacher/questions.php') ?>"><i class="bi bi-database me-1"></i> Question Bank</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($basePath . '/teacher/quizzes.php') ?>"><i class="bi bi-journal-text me-1"></i> Quizzes</a></li>
                    <?php elseif ($role === 'student'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($basePath . '/student/dashboard.php') ?>"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($basePath . '/student/results.php') ?>"><i class="bi bi-trophy me-1"></i> My Results</a></li>
                    <?php endif; ?>
                </ul>
                <div class="navbar-nav align-items-center">
                    <div class="nav-item dropdown me-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-person-fill fs-5"></i>
                            </div>
                            <span class="fw-semibold"><?= htmlspecialchars($name) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3">
                            <li><h6 class="dropdown-header fw-bold px-3"><?= ucfirst(htmlspecialchars($role)) ?> Account</h6></li>
                            <li><hr class="dropdown-divider mx-3 my-2"></li>
                            <li><a class="dropdown-item text-danger fw-semibold" href="<?= htmlspecialchars($basePath . '/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                    <button id="themeToggle" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center border-2 border-transparent hover-lift" style="width: 44px; height: 44px;" title="Toggle Dark Theme">
                        <i class="bi bi-moon-stars-fill text-muted" id="themeIcon"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <div class="container py-5">