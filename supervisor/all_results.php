<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('supervisor');

$supervisorId = $_SESSION['user_id'];
$subjStmt = $pdo->prepare("SELECT subject, class_level FROM users WHERE id = ?");
$subjStmt->execute([$supervisorId]);
$supervisorData = $subjStmt->fetch();

$supervisorSubjects = !empty($supervisorData['subject']) ? array_map('trim', explode(',', $supervisorData['subject'])) : [];
$supervisorClasses = !empty($supervisorData['class_level']) ? array_map('trim', explode(',', $supervisorData['class_level'])) : [];

$stmt = $pdo->prepare("
    SELECT qa.*, q.title as quiz_title, q.subject as quiz_subject, q.class_level as quiz_class, u.name as student_name, u.class_level as student_class
    FROM quiz_attempts qa 
    JOIN quizzes q ON qa.quiz_id = q.id 
    JOIN users u ON qa.student_id = u.id
    ORDER BY qa.started_at DESC
");
$stmt->execute();
$allResults = $stmt->fetchAll();

$results = [];
foreach ($allResults as $r) {
    if (in_array(trim($r['quiz_subject']), $supervisorSubjects) && in_array(trim($r['quiz_class']), $supervisorClasses)) {
        $results[] = $r;
    }
}

$subjDisplay = !empty($supervisorSubjects) ? implode(', ', $supervisorSubjects) : 'None';
$classDisplay = !empty($supervisorClasses) ? implode(', ', $supervisorClasses) : 'None';

require_once '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-bar-chart"></i> Department Results</h2>
        <p class="text-muted mb-0">Student quiz performances for <strong><?= htmlspecialchars($subjDisplay) ?></strong> (Classes: <strong><?= htmlspecialchars($classDisplay) ?></strong>)</p>
    </div>
    <button onclick="window.print()" class="btn btn-primary d-print-none"><i class="bi bi-printer"></i> Print Results</button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Quiz Title</th>
                        <th>Date Taken</th>
                        <th>Score</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['student_name']) ?></td>
                            <td><?= htmlspecialchars($r['student_class']) ?></td>
                            <td><?= htmlspecialchars($r['quiz_title']) ?></td>
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
                                    $badgeClass = $percent >= 50 ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $percent ?>%</span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($results)): ?>
                        <tr><td colspan="6" class="text-center py-3">No results found for your department.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
