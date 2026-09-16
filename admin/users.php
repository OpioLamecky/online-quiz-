<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

// Common school subjects
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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $role = $_POST['role'];
            $class_level = !empty($_POST['class_level']) ? trim($_POST['class_level']) : null;
            // Handle multiple subjects for students
            if ($role === 'student' && isset($_POST['subjects'])) {
                $selected_subjects = $_POST['subjects'];
                $subject = !empty($selected_subjects) ? implode(', ', $selected_subjects) : null;
            } else {
                $subject = !empty($_POST['subject']) ? trim($_POST['subject']) : null;
            }
            
            // Check if email exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = "A user with this email already exists.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, class_level, subject) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $password, $role, $class_level, $subject]);
                $success = "User added successfully.";
            }
            
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['user_id'];
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $class_level = !empty($_POST['class_level']) ? trim($_POST['class_level']) : null;
            // Handle multiple subjects for students
            if ($role === 'student' && isset($_POST['subjects'])) {
                $selected_subjects = $_POST['subjects'];
                $subject = !empty($selected_subjects) ? implode(', ', $selected_subjects) : null;
            } else {
                $subject = !empty($_POST['subject']) ? trim($_POST['subject']) : null;
            }
            
            // Check if email exists for ANOTHER user
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->execute([$email, $id]);
            if ($checkStmt->fetch()) {
                $error = "This email is already taken by another user.";
            } else {
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password_hash=?, role=?, class_level=?, subject=? WHERE id=?");
                    $stmt->execute([$name, $email, $password, $role, $class_level, $subject, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, class_level=?, subject=? WHERE id=?");
                    $stmt->execute([$name, $email, $role, $class_level, $subject, $id]);
                }
                $success = "User updated successfully.";
            }
            
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['user_id'];
            if ($id != $_SESSION['user_id']) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
                $stmt->execute([$id]);
                $success = "User deleted successfully.";
            } else {
                $error = "You cannot delete your own account.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error occurred: " . $e->getMessage();
    }
}

$stmt = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold"><i class="bi bi-people me-2"></i>Manage Students</h2>
        <p class="text-muted mb-0">View and manage student accounts</p>
    </div>
    <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-plus-circle me-2"></i>Add Student
    </button>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center p-4 mb-4" role="alert">
        <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
        <div class="fw-semibold"><?= htmlspecialchars($error) ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success border-0 rounded-4 d-flex align-items-center p-4 mb-4" role="alert">
        <i class="bi bi-check-circle-fill fs-4 me-3"></i>
        <div class="fw-semibold"><?= htmlspecialchars($success) ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Class</th>
                        <th>Subjects</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-2 p-2 me-3">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div class="fw-semibold"><?= htmlspecialchars($user['name']) ?></div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-3 fw-bold"><?= htmlspecialchars($user['class_level']) ?></span></td>
                            <td>
                                <?php if ($user['subject']): ?>
                                    <?php 
                                    $subjList = array_map('trim', explode(',', $user['subject']));
                                    foreach ($subjList as $subj): 
                                    ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-2 me-1 mb-1" style="font-size: 0.75rem;"><?= htmlspecialchars($subj) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="text-muted small"><?= date('M d, Y', strtotime($user['created_at'])) ?></span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary rounded-3" 
                                        onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['name'])) ?>', '<?= htmlspecialchars(addslashes($user['email'])) ?>', '<?= $user['role'] ?>', '<?= htmlspecialchars(addslashes($user['class_level'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($user['subject'] ?? '')) ?>')">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="role" value="student">
                <div class="modal-header border-0 px-5 pt-5 pb-4">
                    <div>
                        <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Add New Student</h5>
                        <p class="text-muted small mb-0">Create a new student account</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-5 pb-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control form-control-lg" placeholder="Student's full name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="student@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Create password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Class Level</label>
                            <select name="class_level" class="form-select form-select-lg" required>
                                <option value="">Select class level</option>
                                <?php foreach ($classLevels as $level): ?>
                                    <option value="<?= htmlspecialchars($level) ?>"><?= htmlspecialchars($level) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Subjects (Select multiple)</label>
                            <div class="bg-light rounded-4 p-4">
                                <div class="row">
                                    <?php foreach ($subjects as $index => $subj): ?>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="<?= htmlspecialchars($subj) ?>" id="add_subject_<?= $index ?>" name="subjects[]">
                                                <label class="form-check-label" for="add_subject_<?= $index ?>">
                                                    <?= htmlspecialchars($subj) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-5 pb-5 pt-0">
                    <button type="button" class="btn btn-secondary fw-bold rounded-3 px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4 py-2">
                        <i class="bi bi-check-circle me-2"></i>Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="hidden" name="role" value="student">
                <div class="modal-header border-0 px-5 pt-5 pb-4">
                    <div>
                        <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i>Edit Student</h5>
                        <p class="text-muted small mb-0">Update student account information</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-5 pb-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" id="edit_email" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password (leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Class Level</label>
                            <select name="class_level" id="edit_class_level" class="form-select form-select-lg" required>
                                <option value="">Select class level</option>
                                <?php foreach ($classLevels as $level): ?>
                                    <option value="<?= htmlspecialchars($level) ?>"><?= htmlspecialchars($level) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Subjects (Select multiple)</label>
                            <div class="bg-light rounded-4 p-4">
                                <div class="row">
                                    <?php foreach ($subjects as $index => $subj): ?>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="<?= htmlspecialchars($subj) ?>" id="edit_subject_<?= $index ?>" name="subjects[]">
                                                <label class="form-check-label" for="edit_subject_<?= $index ?>">
                                                    <?= htmlspecialchars($subj) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-5 pb-5 pt-0">
                    <button type="button" class="btn btn-secondary fw-bold rounded-3 px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4 py-2">
                        <i class="bi bi-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Parse subject string into array
function parseSubjects(subjectStr) {
    if (!subjectStr) return [];
    return subjectStr.split(',').map(s => s.trim()).filter(s => s.length > 0);
}

function openEditModal(id, name, email, role, class_level, subject) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    
    // Set class level
    const classSelect = document.getElementById('edit_class_level');
    for (let i = 0; i < classSelect.options.length; i++) {
        if (classSelect.options[i].value === class_level) {
            classSelect.selectedIndex = i;
            break;
        }
    }
    
    // Uncheck all subjects first
    const subjectCheckboxes = document.querySelectorAll('#editUserModal input[name="subjects[]"]');
    subjectCheckboxes.forEach(cb => cb.checked = false);
    
    // Check selected subjects
    const selectedSubjects = parseSubjects(subject);
    subjectCheckboxes.forEach(cb => {
        if (selectedSubjects.includes(cb.value)) {
            cb.checked = true;
        }
    });
    
    var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}
</script>

<?php require_once '../includes/footer.php'; ?>
