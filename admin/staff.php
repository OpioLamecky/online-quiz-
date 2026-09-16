<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $role = $_POST['role'];
            $subject = !empty($_POST['subject']) ? (is_array($_POST['subject']) ? implode(', ', $_POST['subject']) : trim($_POST['subject'])) : null;
            $class_level = !empty($_POST['class_level']) ? (is_array($_POST['class_level']) ? implode(', ', $_POST['class_level']) : trim($_POST['class_level'])) : null;
            
            // Check if email exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = "A user with this email already exists.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, subject, class_level) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $password, $role, $subject, $class_level]);
                $success = "Staff member added successfully.";
            }
            
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['user_id'];
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $subject = !empty($_POST['subject']) ? (is_array($_POST['subject']) ? implode(', ', $_POST['subject']) : trim($_POST['subject'])) : null;
            $class_level = !empty($_POST['class_level']) ? (is_array($_POST['class_level']) ? implode(', ', $_POST['class_level']) : trim($_POST['class_level'])) : null;
            
            // Check if email exists for ANOTHER user
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->execute([$email, $id]);
            if ($checkStmt->fetch()) {
                $error = "This email is already taken by another user.";
            } else {
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password_hash=?, role=?, subject=?, class_level=? WHERE id=?");
                    $stmt->execute([$name, $email, $password, $role, $subject, $class_level, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, subject=?, class_level=? WHERE id=?");
                    $stmt->execute([$name, $email, $role, $subject, $class_level, $id]);
                }
                $success = "Staff member updated successfully.";
            }
            
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['user_id'];
            if ($id != $_SESSION['user_id']) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
                $stmt->execute([$id]);
                $success = "Staff member deleted successfully.";
            } else {
                $error = "You cannot delete your own account.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error occurred: " . $e->getMessage();
    }
}

$stmt = $pdo->query("SELECT * FROM users WHERE role IN ('teacher', 'supervisor', 'admin') ORDER BY role, created_at DESC");
$users = $stmt->fetchAll();

$availableSubjects = ['Mathematics', 'English', 'Physics', 'Chemistry', 'Biology', 'History', 'Geography', 'Computer Science', 'Literature', 'Fine Art', 'Physical Education', 'Agriculture', 'Religious Education'];
$availableClasses = ['S1', 'S2', 'S3', 'S4', 'S5', 'S6'];

require_once '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-workspace"></i> Manage Staff</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="bi bi-plus-circle"></i> Add Staff</button>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Subjects & Classes</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($user['role'])) ?></span></td>
                            <td>
                                <div><small><strong>Subjects:</strong> <?= htmlspecialchars($user['subject'] ?? '-') ?></small></div>
                                <div><small><strong>Classes:</strong> <?= htmlspecialchars($user['class_level'] ?? '-') ?></small></div>
                            </td>
                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['name'])) ?>', '<?= htmlspecialchars(addslashes($user['email'])) ?>', '<?= $user['role'] ?>', '<?= htmlspecialchars(addslashes($user['subject'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($user['class_level'] ?? '')) ?>')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" class="form-select" id="add_role" onchange="toggleAddFields()" required>
                            <option value="teacher">Teacher</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3" id="add_subject_div">
                        <label class="form-label">Subjects <small class="text-muted">(Select one or more)</small></label>
                        <div class="border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
                            <?php foreach ($availableSubjects as $subj): ?>
                                <div class="form-check">
                                    <input class="form-check-input add-subject-check" type="checkbox" name="subject[]" value="<?= $subj ?>" id="add_subj_<?= md5($subj) ?>">
                                    <label class="form-check-label" for="add_subj_<?= md5($subj) ?>"><?= $subj ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3" id="add_class_div">
                        <label class="form-label">Classes <small class="text-muted">(Select one or more)</small></label>
                        <div class="border rounded p-2 bg-light">
                            <div class="row g-2">
                                <?php foreach ($availableClasses as $cls): ?>
                                    <div class="col-4">
                                        <div class="form-check">
                                            <input class="form-check-input add-class-check" type="checkbox" name="class_level[]" value="<?= $cls ?>" id="add_cls_<?= $cls ?>">
                                            <label class="form-check-label" for="add_cls_<?= $cls ?>"><?= $cls ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" id="edit_role" class="form-select" onchange="toggleEditFields()" required>
                            <option value="teacher">Teacher</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit_subject_div">
                        <label class="form-label">Subjects <small class="text-muted">(Select one or more)</small></label>
                        <div class="border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
                            <?php foreach ($availableSubjects as $subj): ?>
                                <div class="form-check">
                                    <input class="form-check-input edit-subject-check" type="checkbox" name="subject[]" value="<?= $subj ?>" id="edit_subj_<?= md5($subj) ?>">
                                    <label class="form-check-label" for="edit_subj_<?= md5($subj) ?>"><?= $subj ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_class_div">
                        <label class="form-label">Classes <small class="text-muted">(Select one or more)</small></label>
                        <div class="border rounded p-2 bg-light">
                            <div class="row g-2">
                                <?php foreach ($availableClasses as $cls): ?>
                                    <div class="col-4">
                                        <div class="form-check">
                                            <input class="form-check-input edit-class-check" type="checkbox" name="class_level[]" value="<?= $cls ?>" id="edit_cls_<?= $cls ?>">
                                            <label class="form-check-label" for="edit_cls_<?= $cls ?>"><?= $cls ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAddFields() {
    var role = document.getElementById('add_role').value;
    var subjDiv = document.getElementById('add_subject_div');
    var classDiv = document.getElementById('add_class_div');
    if (role === 'admin') {
        subjDiv.style.display = 'none';
        classDiv.style.display = 'none';
    } else {
        subjDiv.style.display = 'block';
        classDiv.style.display = 'block';
    }
}

function toggleEditFields() {
    var role = document.getElementById('edit_role').value;
    var subjDiv = document.getElementById('edit_subject_div');
    var classDiv = document.getElementById('edit_class_div');
    if (role === 'admin') {
        subjDiv.style.display = 'none';
        classDiv.style.display = 'none';
    } else {
        subjDiv.style.display = 'block';
        classDiv.style.display = 'block';
    }
}

function openEditModal(id, name, email, role, subject, class_level) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    
    // Reset all checkboxes
    document.querySelectorAll('.edit-subject-check').forEach(function(cb) { cb.checked = false; });
    document.querySelectorAll('.edit-class-check').forEach(function(cb) { cb.checked = false; });
    
    // Check appropriate subject boxes
    if (subject) {
        var subjects = subject.split(', ');
        document.querySelectorAll('.edit-subject-check').forEach(function(cb) {
            if (subjects.includes(cb.value)) { cb.checked = true; }
        });
    }
    
    // Check appropriate class boxes
    if (class_level) {
        var classes = class_level.split(', ');
        document.querySelectorAll('.edit-class-check').forEach(function(cb) {
            if (classes.includes(cb.value)) { cb.checked = true; }
        });
    }
    
    toggleEditFields();
    
    var modal = new bootstrap.Modal(document.getElementById('editStaffModal'));
    modal.show();
}

// Initial toggle setup
document.addEventListener("DOMContentLoaded", function() {
    toggleAddFields();
});
</script>

<?php require_once '../includes/footer.php'; ?>
