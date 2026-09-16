<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('supervisor');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $quiz_id = $_POST['quiz_id'];
    $new_status = ($_POST['action'] === 'approve') ? 'approved' : 'draft';
    
    $stmt = $pdo->prepare("UPDATE quizzes SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $quiz_id]);
    
    // Auto make it live if approved (simplified workflow)
    if ($new_status === 'approved') {
        $stmt2 = $pdo->prepare("UPDATE quizzes SET status = 'live' WHERE id = ?");
        $stmt2->execute([$quiz_id]);
    }
    
    $msg = ($new_status === 'approved') ? 'Quiz approved and made live.' : 'Quiz rejected and sent back to draft.';
    header("Location: approve_quizzes.php?msg=" . urlencode($msg));
    exit();
}

$userStmt = $pdo->prepare("SELECT subject, class_level FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$supervisorData = $userStmt->fetch();

$supervisorSubjects = !empty($supervisorData['subject']) ? array_map('trim', explode(',', $supervisorData['subject'])) : [];
$supervisorClasses = !empty($supervisorData['class_level']) ? array_map('trim', explode(',', $supervisorData['class_level'])) : [];

$pendingStmt = $pdo->prepare("SELECT q.*, u.name as teacher_name FROM quizzes q JOIN users u ON q.teacher_id = u.id WHERE q.status = 'pending'");
$pendingStmt->execute();
$allPending = $pendingStmt->fetchAll();

$quizzes = [];
foreach ($allPending as $q) {
    if (in_array(trim($q['subject']), $supervisorSubjects) && in_array(trim($q['class_level']), $supervisorClasses)) {
        $quizzes[] = $q;
    }
}

require_once '../includes/header.php';
?>
<?php
$subjDisplay = !empty($supervisorSubjects) ? implode(', ', $supervisorSubjects) : 'None';
$classDisplay = !empty($supervisorClasses) ? implode(', ', $supervisorClasses) : 'None';
?>
<div class="mb-4">
    <h2><i class="bi bi-check-circle"></i> Approve Quizzes</h2>
    <p class="text-muted">Review quizzes submitted by teachers in <strong><?= htmlspecialchars($subjDisplay) ?></strong> for classes <strong><?= htmlspecialchars($classDisplay) ?></strong></p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_GET['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Teacher</th>
                        <th>Class</th>
                        <th>Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $q): ?>
                        <tr>
                            <td><?= htmlspecialchars($q['title']) ?></td>
                            <td><?= htmlspecialchars($q['teacher_name']) ?></td>
                            <td><?= htmlspecialchars($q['class_level']) ?></td>
                            <td><?= $q['duration_minutes'] ?> min</td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="quiz_id" value="<?= $q['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($quizzes)): ?>
                        <tr><td colspan="5" class="text-center py-3">No quizzes pending approval.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
