<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('supervisor');

$supervisorId = $_SESSION['user_id'];

// Get supervisor's subjects and classes
$stmt = $pdo->prepare("SELECT subject, class_level FROM users WHERE id = ?");
$stmt->execute([$supervisorId]);
$supervisorData = $stmt->fetch();

$supervisorSubjects = !empty($supervisorData['subject']) ? array_map('trim', explode(',', $supervisorData['subject'])) : [];
$supervisorClasses = !empty($supervisorData['class_level']) ? array_map('trim', explode(',', $supervisorData['class_level'])) : [];

// Pending Quizzes
$pendingStmt = $pdo->prepare("SELECT q.id, q.title, q.subject, q.class_level, u.name as teacher_name, q.created_at FROM quizzes q JOIN users u ON q.teacher_id = u.id WHERE q.status = 'pending'");
$pendingStmt->execute();
$allPending = $pendingStmt->fetchAll();

$pendingQuizzes = [];
foreach ($allPending as $q) {
    if (in_array(trim($q['subject']), $supervisorSubjects) && in_array(trim($q['class_level']), $supervisorClasses)) {
        $pendingQuizzes[] = $q;
    }
}

// Stats
$allQuizzesStmt = $pdo->prepare("SELECT subject, class_level FROM quizzes");
$allQuizzesStmt->execute();
$allQuizzes = $allQuizzesStmt->fetchAll();

$totalDeptQuizzesCount = 0;
foreach ($allQuizzes as $q) {
    if (in_array(trim($q['subject']), $supervisorSubjects) && in_array(trim($q['class_level']), $supervisorClasses)) {
        $totalDeptQuizzesCount++;
    }
}

$allTeachersStmt = $pdo->prepare("SELECT subject, class_level FROM users WHERE role = 'teacher'");
$allTeachersStmt->execute();
$allTeachers = $allTeachersStmt->fetchAll();

$totalDeptTeachersCount = 0;
foreach ($allTeachers as $t) {
    $tSubj = !empty($t['subject']) ? array_map('trim', explode(',', $t['subject'])) : [];
    $tClass = !empty($t['class_level']) ? array_map('trim', explode(',', $t['class_level'])) : [];
    
    // Check if there's any intersection
    if (!empty(array_intersect($tSubj, $supervisorSubjects)) && !empty(array_intersect($tClass, $supervisorClasses))) {
        $totalDeptTeachersCount++;
    }
}


require_once '../includes/header.php';
?>
<?php
$subjDisplay = !empty($supervisorSubjects) ? implode(', ', $supervisorSubjects) : 'None';
$classDisplay = !empty($supervisorClasses) ? implode(', ', $supervisorClasses) : 'None';
?>
<div class="row align-items-center mb-5">
    <div class="col-lg-8">
        <h2 class="display-6 fw-bold text-primary mb-2">
            <span class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-2">
                <i class="bi bi-shield-check"></i>
            </span>
            Supervisor Dashboard
        </h2>
        <p class="text-muted lead mb-0">Managing <span class="text-dark fw-bold"><?= htmlspecialchars($subjDisplay) ?></span> for <span class="text-dark fw-bold"><?= htmlspecialchars($classDisplay) ?></span></p>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="approve_quizzes.php" class="btn btn-primary fw-bold px-4 py-2 rounded-pill shadow-sm">
            <i class="bi bi-check-all me-2"></i>Review All Quizzes
        </a>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                        <i class="bi bi-hourglass-split fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Pending Approvals</small>
                        <h3 class="fw-bold mb-0"><?= count($pendingQuizzes) ?></h3>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: <?= (count($pendingQuizzes) > 0 ? 50 : 0) ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="bi bi-journal-check fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Department Quizzes</small>
                        <h3 class="fw-bold mb-0"><?= $totalDeptQuizzesCount ?></h3>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="bi bi-people fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Department Teachers</small>
                        <h3 class="fw-bold mb-0"><?= $totalDeptTeachersCount ?></h3>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white border-0 p-4 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0">Quizzes Awaiting Review</h5>
        <div>
            <a href="all_results.php" class="btn btn-sm btn-light fw-bold rounded-pill px-3 me-2">Performance Data</a>
            <a href="approve_quizzes.php" class="btn btn-sm btn-light fw-bold rounded-pill px-3">View All</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Quiz Title</th>
                        <th>Class Level</th>
                        <th>Teacher</th>
                        <th>Submitted On</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pendingQuizzes) > 0): ?>
                        <?php foreach ($pendingQuizzes as $quiz): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-2 p-2 me-3 text-primary">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($quiz['title']) ?></h6>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-3"><?= htmlspecialchars($quiz['class_level']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person me-2 text-muted"></i>
                                        <span class="small fw-medium"><?= htmlspecialchars($quiz['teacher_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="small text-muted"><?= date('M d, Y', strtotime($quiz['created_at'])) ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="approve_quizzes.php?id=<?= $quiz['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-check2-circle display-6 d-block mb-3 text-success"></i>
                                All clear! No pending quizzes require your attention.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
