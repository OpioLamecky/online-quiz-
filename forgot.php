<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        // In a real app, you would:
        // 1. Check if the email exists
        // 2. Generate a password reset token
        // 3. Send an email with a reset link
        
        // For this demo, we'll just show a success message
        $success = 'If an account exists for this email, you will receive a password reset link shortly.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KoKCS Online Assessment Portal - Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-white">
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            <!-- Left Side: Branding & Visuals -->
            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center position-relative overflow-hidden login-bg-container">
                <!-- Gradient Overlay -->
                <div class="position-absolute top-0 start-0 w-100 h-100 login-gradient-overlay z-1"></div>
                
                <!-- Subtle Texture -->
                <div class="position-absolute top-0 start-0 w-100 h-100 login-texture-overlay z-2"></div>
                
                <div class="text-center text-white position-relative z-3 p-5">
                    <div class="mb-5">
                        <i class="bi bi-key-fill display-1 opacity-90"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-4 tracking-tight" style="text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        Reset Your Password
                    </h1>
                    <p class="lead mb-0 opacity-90" style="text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        Don't worry, it happens to the best of us! We'll help you get back on track.
                    </p>
                </div>
            </div>
            
            <!-- Right Side: Forgot Password Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5">
                <div class="w-100" style="max-width: 400px;">
                    <div class="mb-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-4 p-3 mb-4" style="width: 64px; height: 64px;">
                            <i class="bi bi-key-fill fs-2"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-1">Forgot Password?</h2>
                        <p class="text-muted">No worries, we'll send you a link to reset your password.</p>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center p-4 mb-4" role="alert">
                            <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
                            <div class="fw-semibold"><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center p-4 mb-4" role="alert">
                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                            <div class="fw-semibold"><?= htmlspecialchars($success) ?></div>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="forgot.php">
                            <div class="mb-4">
                                <label for="email" class="form-label small fw-bold text-uppercase tracking-wider text-muted">Email Address</label>
                                <input type="email" class="form-control form-control-lg border-0 bg-light rounded-4 px-4 py-3 fs-6" id="email" name="email" placeholder="name@example.com" required>
                            </div>
                            <div class="d-grid pt-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold shadow-sm transition-all hover-lift">
                                    <i class="bi bi-send me-2"></i>Send Reset Link
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    <div class="text-center mt-5 pt-4 border-top">
                        <span class="text-muted">Remember your password?</span> 
                        <a href="index.php" class="text-decoration-none fw-bold text-primary ms-1">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>