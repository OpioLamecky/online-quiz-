<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ?");
        if($stmt->execute([$name, $email, $password, $_SESSION['user_id']])) {
            $success = "Settings and password updated successfully.";
        } else {
            $error = "Failed to update settings.";
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        if($stmt->execute([$name, $email, $_SESSION['user_id']])) {
            $success = "Settings updated successfully.";
        } else {
            $error = "Failed to update settings.";
        }
    }
    
    // Update session data
    $_SESSION['user_name'] = $name;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

require_once '../includes/header.php';
?>
<div class="mb-4">
    <h2><i class="bi bi-gear"></i> System Settings</h2>
    <p class="text-muted">Manage your admin profile and system preferences</p>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Admin Profile Settings</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($admin['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Global Preferences</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Currently, global preferences (such as school name and contact details) are handled directly in the code files. Future updates will introduce database-driven global settings.</p>
                <button class="btn btn-outline-secondary" disabled>Advanced Settings (Coming Soon)</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
