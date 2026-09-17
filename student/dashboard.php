<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('student');

$studentId = $_SESSION['user_id'];

// Get user class level and subjects
$userStmt = $pdo->prepare("SELECT class_level, subject FROM users WHERE id = ?");
$userStmt->execute([$studentId]);
$user = $userStmt->fetch();
$classLevel = $user['class_level'];
$studentSubjectsStr = $user['subject'];

// Parse student subjects into an array
$studentSubjects = [];
if (!empty($studentSubjectsStr)) {
    $studentSubjects = array_map('trim', explode(',', $studentSubjectsStr));
    $studentSubjects = array_filter($studentSubjects); // Remove empty values
}

// Auto-submit any expired in_progress attempts for this student
$autoSubmitStmt = $pdo->prepare("
    UPDATE quiz_attempts qa
    SET status = 'graded', submitted_at = q.end_time
    FROM quizzes q
    WHERE qa.quiz_id = q.id
      AND qa.student_id = ?
      AND qa.status = 'in_progress'
      AND q.end_time <= NOW()
");
$autoSubmitStmt->execute([$studentId]);

// Build the WHERE clause for subjects - only include quizzes for student's registered subjects
$subjectWhereClause = '';
$subjectParams = [];
if (!empty($studentSubjects)) {
    $placeholders = str_repeat('?,', count($studentSubjects) - 1) . '?';
    $subjectWhereClause = "AND q.subject IN ($placeholders)";
    $subjectParams = $studentSubjects;
} else {
    // If student has no registered subjects, show no quizzes
    $subjectWhereClause = "AND 1=0";
}

// Get available quizzes for this student's class and subjects
$availableStmt = $pdo->prepare("
    SELECT q.*, 
    (SELECT COUNT(*) FROM quiz_attempts qa WHERE qa.quiz_id = q.id AND qa.student_id = ?) as attempt_count
    FROM quizzes q 
    WHERE q.status = 'live' 
    AND q.class_level = ?
    $subjectWhereClause
    AND q.end_time >= NOW()
    AND (
        SELECT COUNT(*)
        FROM quiz_attempts qa
        WHERE qa.quiz_id = q.id AND qa.student_id = ?
    ) < q.max_attempts
    ORDER BY q.start_time ASC
");
$availableStmt->execute(array_merge([$studentId, $classLevel], $subjectParams, [$studentId]));
$availableQuizzes = $availableStmt->fetchAll();

// Get recent attempts
$attemptsStmt = $pdo->prepare("
    SELECT qa.*, q.title, q.subject 
    FROM quiz_attempts qa 
    JOIN quizzes q ON qa.quiz_id = q.id 
    WHERE qa.student_id = ? 
    ORDER BY qa.started_at DESC LIMIT 5
");
$attemptsStmt->execute([$studentId]);
$recentAttempts = $attemptsStmt->fetchAll();

// Get missed quizzes (quizzes that ended without an attempt for student's subjects
// Parameter order: [classLevel, ...subjectParams, studentId]
$missedParams = array_merge([$classLevel], $subjectParams, [$studentId]);

$missedStmt = $pdo->prepare("
    SELECT q.id, q.title, q.subject, q.end_time, q.passmark
    FROM quizzes q 
    WHERE q.status = 'live' 
    AND q.class_level = ?
    $subjectWhereClause
    AND q.end_time < NOW()
    AND NOT EXISTS (SELECT 1 FROM quiz_attempts qa WHERE qa.quiz_id = q.id AND qa.student_id = ?)
    ORDER BY q.end_time DESC
");
$missedStmt->execute($missedParams);
$missedQuizzes = $missedStmt->fetchAll();

// Filter only currently active quizzes for the notification count
$activeQuizzesCount = 0;
foreach($availableQuizzes as $q) {
    if (time() >= strtotime($q['start_time'])) {
        $activeQuizzesCount++;
    }
}

require_once '../includes/header.php';
?>
<div class="row g-4 mb-5 align-items-center">
    <div class="col-lg-8">
        <div class="mb-2">
            <span class="text-primary fw-bold text-uppercase tracking-widest small">Student Dashboard</span>
        </div>
        <h1 class="display-5 fw-bold mb-2" style="letter-spacing: -0.02em;">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
        <p class="text-muted lead mb-0">Here's what's happening with your studies.</p>
        <?php if (!empty($studentSubjects)): ?>
            <div class="mt-4 d-flex flex-wrap align-items-center">
                <span class="text-muted fw-semibold me-3">Registered Subjects:</span>
                <?php foreach ($studentSubjects as $subj): ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-semibold me-2 mb-2">
                        <i class="bi bi-book me-1"></i><?= htmlspecialchars($subj) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <div class="d-inline-flex bg-white shadow-sm rounded-4 p-3 border">
            <div class="px-4 border-end">
                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.08em;">Class Level</small>
                <span class="fw-bold text-dark" style="font-size: 1.25rem;"><?= htmlspecialchars($classLevel) ?></span>
            </div>
            <div class="px-4">
                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.08em;">Quizzes Taken</small>
                <span class="fw-bold text-dark" style="font-size: 1.25rem;"><?= count($recentAttempts) ?></span>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center p-4" role="alert">
    <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
    <div class="fw-semibold"><?= htmlspecialchars($_GET['msg']) ?></div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($activeQuizzesCount > 0): ?>
<div class="alert alert-warning border-0 shadow-sm rounded-4 mb-5 d-flex align-items-center p-4" role="alert">
    <div class="bg-warning bg-opacity-10 rounded-4 p-3 me-4" style="width: 64px; height: 64px; display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-bell-fill fs-3 text-warning"></i>
    </div>
    <div>
        <h5 class="alert-heading fw-bold mb-1">Pending Quizzes!</h5>
        <p class="mb-0">You have <span class="fw-bold"><?= $activeQuizzesCount ?></span> active quiz<?= $activeQuizzesCount > 1 ? 'zes' : '' ?> ready for you to take. Don't miss out!</p>
    </div>
</div>
<?php endif; ?>

<div class="row mb-5">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0">Available Quizzes</h4>
                <p class="text-muted small mb-0">Complete these assessments to track your progress</p>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary px-4 py-2 rounded-4 fw-bold">
                <?= count($availableQuizzes) ?> Available
            </span>
        </div>
        
        <?php if (count($availableQuizzes) > 0): ?>
            <div class="row g-4">
                <?php foreach ($availableQuizzes as $quiz): 
                    $hasStarted = time() >= strtotime($quiz['start_time']);
                ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm hover-lift transition-all">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                        <i class="bi bi-journal-text fs-4"></i>
                                    </div>
                                    <?php if ($hasStarted): ?>
                                        <span class="status-badge status-live">Active Now</span>
                                    <?php else: ?>
                                        <span class="status-badge bg-secondary bg-opacity-10 text-secondary border-0">Upcoming</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($quiz['title']) ?></h5>
                                <p class="text-muted small mb-4"><?= htmlspecialchars($quiz['subject']) ?></p>
                                
                                <div class="d-flex flex-wrap gap-3 mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-stopwatch text-muted me-2"></i>
                                        <span class="small fw-medium text-dark"><?= $quiz['duration_minutes'] ?> mins</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle text-muted me-2"></i>
                                        <span class="small fw-medium text-dark">Pass: <?= $quiz['passmark'] ?>%</span>
                                    </div>
                                </div>
                                
                                <?php if (!$hasStarted): ?>
                                    <div class="bg-light rounded-3 p-3 mb-3">
                                        <small class="text-muted d-block mb-1">Starts at</small>
                                        <span class="fw-bold small text-dark"><i class="bi bi-calendar-event me-2"></i><?= date('M d, g:i A', strtotime($quiz['start_time'])) ?></span>
                                    </div>
                                    <button class="btn btn-light w-100 fw-bold py-2" disabled>Coming Soon</button>
                                <?php else: ?>
                                    <div class="bg-danger bg-opacity-10 rounded-3 p-3 mb-3">
                                        <small class="text-danger d-block mb-1">Closes at</small>
                                        <span class="fw-bold small text-danger"><i class="bi bi-calendar-x me-2"></i><?= date('M d, g:i A', strtotime($quiz['end_time'])) ?></span>
                                    </div>
                                    <a href="take_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-primary w-100 fw-bold py-2">Start Quiz</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                <div class="bg-light rounded-4 p-4 d-inline-flex mb-4 mx-auto" style="width: 100px; height: 100px;">
                    <i class="bi bi-emoji-smile display-4 text-muted"></i>
                </div>
                <h5 class="fw-bold mb-2">All caught up!</h5>
                <p class="text-muted mb-0">There are no new quizzes available for you at the moment. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 px-4 py-4">
                <h5 class="fw-bold mb-1">Recent Activity & Results</h5>
                <p class="text-muted small mb-0">Track your performance and review your quiz attempts</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Quiz Details</th>
                                <th>Date Taken</th>
                                <th>Score</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentAttempts) > 0): ?>
                                <?php foreach ($recentAttempts as $attempt): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-2 p-2 me-3">
                                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($attempt['title']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($attempt['subject']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark small fw-medium"><?= date('M d, Y', strtotime($attempt['started_at'])) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($attempt['status'] === 'graded'): ?>
                                                <?php 
                                                $percent = ($attempt['total_marks'] > 0) ? round(($attempt['score'] / $attempt['total_marks']) * 100) : 0; 
                                                $textClass = $percent >= 50 ? 'text-success' : 'text-danger';
                                                $bgClass = $percent >= 50 ? 'bg-success' : 'bg-danger';
                                                ?>
                                                <div class="d-flex align-items-center">
                                                    <span class="fw-bold <?= $textClass ?> me-2"><?= $attempt['score'] ?>/<?= $attempt['total_marks'] ?></span>
                                                    <span class="badge <?= $bgClass ?> bg-opacity-10 <?= $textClass ?> border-0 rounded-pill"><?= $percent ?>%</span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small italic">Processing...</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower(str_replace('_', '', $attempt['status'])) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $attempt['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if ($attempt['status'] === 'graded'): ?>
                                                <a href="view_result.php?id=<?= $attempt['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    <i class="bi bi-eye me-1"></i> Details
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (count($missedQuizzes) > 0): ?>
                                <?php foreach ($missedQuizzes as $missed): ?>
                                    <tr class="bg-light bg-opacity-50">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center opacity-75">
                                                <div class="bg-secondary bg-opacity-10 rounded-2 p-2 me-3">
                                                    <i class="bi bi-file-earmark-x text-muted"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-muted"><?= htmlspecialchars($missed['title']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($missed['subject']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-danger small fw-bold"><i class="bi bi-clock-history me-1"></i>Missed</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold text-danger me-2">0/<?= $missed['passmark'] ?>+</span>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border-0 rounded-pill">0%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge bg-danger bg-opacity-10 text-danger border-0">MISSED</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-light disabled rounded-pill px-3" disabled>
                                                No Attempt
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <?php if (empty($recentAttempts) && empty($missedQuizzes)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox display-6 d-block mb-3"></i>
                                        No recent attempts or missed quizzes found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Automatically refresh page when a quiz starts
    let minTimeUntilStart = Infinity;
    const nowServerTime = <?= time() * 1000 ?>;
    
    <?php foreach ($availableQuizzes as $quiz): ?>
        <?php if (time() < strtotime($quiz['start_time'])): ?>
            {
                let startTimestamp = <?= strtotime($quiz['start_time']) * 1000 ?>;
                let timeUntilStart = startTimestamp - nowServerTime;
                if (timeUntilStart > 0 && timeUntilStart < minTimeUntilStart) {
                    minTimeUntilStart = timeUntilStart;
                }
            }
        <?php endif; ?>
    <?php endforeach; ?>

    if (minTimeUntilStart !== Infinity && minTimeUntilStart < 86400000) { 
        setTimeout(() => {
            window.location.reload();
        }, minTimeUntilStart + 1000);
    }
</script>

<?php require_once '../includes/footer.php'; ?>
