<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('student');

$studentId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT qa.*, q.title, q.subject, q.passmark 
    FROM quiz_attempts qa 
    JOIN quizzes q ON qa.quiz_id = q.id 
    WHERE qa.student_id = ? 
    ORDER BY qa.started_at DESC
");
$stmt->execute([$studentId]);
$results = $stmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-bar-chart"></i> My Results</h2>
        <p class="text-muted mb-0">Review your past quiz performances</p>
    </div>
    <button onclick="window.print()" class="btn btn-primary d-print-none"><i class="bi bi-printer"></i> Print Results</button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Quiz Title</th>
                        <th>Subject</th>
                        <th>Date Taken</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['title']) ?></td>
                            <td><?= htmlspecialchars($r['subject']) ?></td>
                            <td><?= date('M d, Y', strtotime($r['started_at'])) ?></td>
                            <td>
                                <?php if ($r['status'] === 'graded'): ?>
                                    <span class="fw-bold"><?= $r['score'] ?> / <?= $r['total_marks'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'graded'): ?>
                                    <?php 
                                    $percent = ($r['total_marks'] > 0) ? round(($r['score'] / $r['total_marks']) * 100) : 0; 
                                    $passmark = $r['passmark'] ?? 50;
                                    $passed = $percent >= $passmark;
                                    $badgeClass = $passed ? 'bg-success' : 'bg-danger';
                                    $statusText = $passed ? 'PASS' : 'FAIL';
                                    ?>
                                    <span class="badge <?= $badgeClass ?> px-2 py-1 rounded-pill"><?= $percent ?>% - <?= $statusText ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'graded'): ?>
                                    <a href="view_result.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Show Corrections</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled>Pending...</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($results)): ?>
                        <tr><td colspan="6" class="text-center py-3">No results found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
