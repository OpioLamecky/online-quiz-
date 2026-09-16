<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($pdo, $email, $password)) {
        $role = $_SESSION['user_role'];
        if ($role === 'admin') header('Location: admin/dashboard.php');
        elseif ($role === 'supervisor') header('Location: supervisor/dashboard.php');
        elseif ($role === 'teacher') header('Location: teacher/dashboard.php');
        elseif ($role === 'student') header('Location: student/dashboard.php');
        exit();
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KoKCS Online Assessment Portal - Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
                    <div class="mb-6">
                        <div class="bg-white bg-opacity-10 rounded-4 p-4 d-inline-flex mb-4">
                            <i class="bi bi-pencil-square display-1 opacity-100"></i>
                        </div>
                    </div>
                    <h1 class="display-3 fw-bold mb-4 tracking-tight" style="text-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                        KoKCS Online Assessment Portal
                    </h1>
                    <p class="lead mb-6 opacity-95 fs-4" style="text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        Empowering students through innovative assessment and real-time analytics.
                    </p>
                    
                    <!-- Feature Icons -->
                    <div class="row g-4 mt-6">
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded-3 p-4">
                                <i class="bi bi-clock-history display-5 mb-2"></i>
                                <h6 class="fw-semibold">Real-time Analytics</h6>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded-3 p-4">
                                <i class="bi bi-shield-lock display-5 mb-2"></i>
                                <h6 class="fw-semibold">Secure Platform</h6>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded-3 p-4">
                                <i class="bi bi-phone display-5 mb-2"></i>
                                <h6 class="fw-semibold">Mobile Ready</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5">
                <div class="w-100" style="max-width: 420px;">
                    <div class="mb-6">
                        <div class="d-inline-flex align-items-center justify-content-center bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-4 p-3 mb-4 shadow-lg" style="width: 72px; height: 72px;">
                            <i class="bi bi-shield-lock-fill fs-1"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-1" style="font-size: 2.25rem;">Welcome Back!</h2>
                        <p class="text-muted">Sign in to continue to your dashboard</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 shadow rounded-4 d-flex align-items-center mb-5 p-4" role="alert">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-3 me-4">
                                <i class="bi bi-exclamation-circle-fill text-danger fs-4"></i>
                            </div>
                            <div class="fw-semibold fs-5"><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php">
                        <div class="mb-5">
                            <label for="email" class="form-label fw-semibold text-gray-700 mb-2">Email Address</label>
                            <div class="position-relative">
                                <div class="position-absolute start-0 top-0 d-flex align-items-center ps-4 h-100">
                                    <i class="bi bi-envelope text-primary fs-5"></i>
                                </div>
                                <input type="email" class="form-control form-control-lg ps-12 py-4 rounded-4 bg-light border-0" id="email" name="email" placeholder="name@example.com" required>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="password" class="form-label fw-semibold text-gray-700 mb-0">Password</label>
                                <a href="forgot.php" class="text-primary fw-semibold text-decoration-none hover:text-primary-dark transition">
                                    Forgot Password?
                                </a>
                            </div>
                            <div class="position-relative">
                                <div class="position-absolute start-0 top-0 d-flex align-items-center ps-4 h-100">
                                    <i class="bi bi-lock text-primary fs-5"></i>
                                </div>
                                <input type="password" class="form-control form-control-lg ps-12 pe-14 py-4 rounded-4 bg-light border-0" id="password" name="password" placeholder="••••••••" required>
                                <button class="btn btn-link position-absolute end-0 top-0 d-flex align-items-center h-100 px-4 text-muted" type="button" id="togglePassword">
                                    <i class="bi bi-eye fs-5"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-6 d-flex align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input rounded-2 me-2" id="remember" style="width: 1.25rem; height: 1.25rem;">
                                <label class="form-check-label fw-semibold text-gray-700 ms-1" for="remember">Keep me signed in</label>
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-4 py-4 fw-semibold text-lg shadow-lg transition-all hover-lift" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                                Access Dashboard <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-7 pt-5 border-top">
                        <p class="text-muted fw-medium mb-0">
                            New to KoKCS? <a href="register.php" class="text-primary fw-bold text-decoration-none ms-1">Create an account</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>

</html>