<?php
require_once 'includes/db.php';

$error = '';
$success = '';

// Common school subjects for the dropdown
$subjects = [
    'Mathematics',
    'English Language',
    'Physics',
    'Chemistry',
    'Biology',
    'History',
    'Geography',
    'Business Studies',
    'Accounting',
    'Computer Science',
    'Agriculture',
    'Art',
    'Music',
    'Physical Education',
    'Religious Education',
    'French',
    'Literature in English',
    'Economics',
    'Government',
    'Further Mathematics'
];

// Common class levels
$classLevels = [
    'S1', 'S2', 'S3', 'S4',
    'S5', 'S6',
    'Form 1', 'Form 2', 'Form 3', 'Form 4',
    'Grade 10', 'Grade 11', 'Grade 12'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    // Hardcode role to student for public registration
    $role = 'student';
    $class_level = $_POST['class_level'] ?? null;
    // Get selected subjects as comma-separated string
    $selected_subjects = $_POST['subjects'] ?? [];
    $subject = !empty($selected_subjects) ? implode(', ', $selected_subjects) : null;

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, class_level, subject) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $hash, $role, $class_level, $subject])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'An error occurred during registration.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KoKCS Online Assessment Portal - Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .subject-tag {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--radius-md);
            margin: 0.25rem;
            font-size: 0.875rem;
        }
        .subject-tag .remove-tag {
            margin-left: 0.5rem;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        .subject-tag .remove-tag:hover {
            opacity: 1;
        }
        .subject-checkbox {
            margin-bottom: 0.75rem;
        }
    </style>
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
                        <i class="bi bi-person-plus-fill display-1 opacity-90"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-4 tracking-tight" style="text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        Join Our Learning Community
                    </h1>
                    <p class="lead mb-0 opacity-90" style="text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        Sign up today and start your journey to academic excellence with KoKCS.
                    </p>
                </div>
            </div>
            
            <!-- Right Side: Registration Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5">
                <div class="w-100" style="max-width: 500px;">
                    <div class="mb-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-4 p-3 mb-4" style="width: 64px; height: 64px;">
                            <i class="bi bi-person-plus-fill fs-2"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-1">Student Registration</h2>
                        <p class="text-muted mb-2">Create your KoKCS Online Assessment Portal account</p>
                        <small class="text-info"><i class="bi bi-info-circle me-1"></i>Teachers & Staff must be registered by an Administrator.</small>
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
                            <div class="fw-semibold"><?= htmlspecialchars($success) ?> <a href="index.php" class="text-decoration-none fw-bold">Go to Login</a></div>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="register.php">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="name" class="form-label fw-bold">Full Name</label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name" placeholder="Enter your full name" required>
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label fw-bold">Email Address</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="name@example.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="class_level" class="form-label fw-bold">Class Level</label>
                                    <select class="form-select form-select-lg" id="class_level" name="class_level" required>
                                        <option value="">Select your class level</option>
                                        <?php foreach ($classLevels as $level): ?>
                                            <option value="<?= htmlspecialchars($level) ?>"><?= htmlspecialchars($level) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Subjects (Select multiple)</label>
                                    <button type="button" class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-between" data-bs-toggle="collapse" data-bs-target="#subjectsCollapse" aria-expanded="false" aria-controls="subjectsCollapse">
                                        <span class="d-flex align-items-center">
                                            <i class="bi bi-book me-2"></i>
                                            <span id="selectedCount">0 subjects selected</span>
                                        </span>
                                        <i class="bi bi-chevron-down transition-all"></i>
                                    </button>
                                </div>
                                <div class="col-12">
                                    <div class="collapse" id="subjectsCollapse">
                                        <div class="card bg-light border-0 rounded-4 p-4 mt-2">
                                            <div class="row">
                                                <?php foreach ($subjects as $index => $subj): ?>
                                                    <div class="col-md-4 col-sm-6">
                                                        <div class="form-check subject-checkbox">
                                                            <input class="form-check-input" type="checkbox" value="<?= htmlspecialchars($subj) ?>" id="subject_<?= $index ?>" name="subjects[]">
                                                            <label class="form-check-label fw-medium" for="subject_<?= $index ?>">
                                                                <?= htmlspecialchars($subj) ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="bg-light rounded-4 p-3" id="selectedSubjectsContainer" style="display: none;">
                                        <small class="text-muted d-block mb-2 fw-semibold"><i class="bi bi-check2-all me-1"></i>Selected Subjects:</small>
                                        <div id="selectedSubjects"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-bold">Password</label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Create a password" required minlength="6">
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label fw-bold">Confirm Password</label>
                                    <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required minlength="6">
                                </div>
                            </div>
                            <div class="d-grid mt-5">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold py-3 rounded-4">
                                    <i class="bi bi-person-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    <div class="mt-5 pt-4 text-center border-top">
                        <span class="text-muted">Already have an account?</span> 
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
    <script>
        // Update selected subjects display and count
        const subjectCheckboxes = document.querySelectorAll('input[name="subjects[]"]');
        const selectedCount = document.getElementById('selectedCount');
        const selectedSubjects = document.getElementById('selectedSubjects');
        const selectedSubjectsContainer = document.getElementById('selectedSubjectsContainer');
        
        function updateSelectedSubjects() {
            const checked = Array.from(subjectCheckboxes).filter(cb => cb.checked);
            selectedCount.textContent = `${checked.length} subject${checked.length !== 1 ? 's' : ''} selected`;
            
            if (checked.length > 0) {
                selectedSubjectsContainer.style.display = 'block';
                selectedSubjects.innerHTML = checked.map(cb => `
                    <span class="subject-tag">
                        ${cb.value}
                        <span class="remove-tag" onclick="removeSubject('${cb.id}')">
                            <i class="bi bi-x"></i>
                        </span>
                    </span>
                `).join('');
            } else {
                selectedSubjectsContainer.style.display = 'none';
            }
        }
        
        function removeSubject(id) {
            const checkbox = document.getElementById(id);
            if (checkbox) {
                checkbox.checked = false;
                updateSelectedSubjects();
            }
        }
        
        // Add event listeners
        subjectCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedSubjects);
        });
        
        // Password match check
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePasswords() {
            if (confirmPassword.value && password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
                confirmPassword.classList.add('is-invalid');
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.classList.remove('is-invalid');
            }
        }
        
        password.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);
    </script>
</body>
</html>