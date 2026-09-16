<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('student');

$studentId = $_SESSION['user_id'];
$attemptId = $_GET['id'] ?? null;

if (!$attemptId) {
    header('Location: results.php');
    exit();
}

// Fetch attempt and quiz details
$stmt = $pdo->prepare("
    SELECT qa.*, q.title, q.subject, q.duration_minutes, q.passmark 
    FROM quiz_attempts qa 
    JOIN quizzes q ON qa.quiz_id = q.id 
    WHERE qa.id = ? AND qa.student_id = ?
");
$stmt->execute([$attemptId, $studentId]);
$attempt = $stmt->fetch();

if (!$attempt) {
    die("Result not found or access denied.");
}

if ($attempt['status'] !== 'graded') {
    die("This quiz has not been graded yet.");
}

// Fetch questions and student's answers
$qStmt = $pdo->prepare("
    SELECT q.id, q.question_text, q.type, q.options_json, q.correct_answer, 
           aa.student_answer, aa.is_correct, aa.marks_awarded, qq.marks 
    FROM questions q 
    JOIN quiz_questions qq ON q.id = qq.question_id 
    JOIN attempt_answers aa ON q.id = aa.question_id 
    WHERE aa.attempt_id = ? AND qq.quiz_id = ?
    ORDER BY qq.order_index ASC
");
$qStmt->execute([$attemptId, $attempt['quiz_id']]);
$questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>
<div class="row align-items-center mb-5">
    <div class="col-lg-7">
        <h2 class="display-6 fw-bold text-primary mb-2">
            <span class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-2">
                <i class="bi bi-journal-check"></i>
            </span>
            Quiz Feedback
        </h2>
        <p class="text-muted lead mb-0">
            Reviewing results for <span class="text-dark fw-bold"><?= htmlspecialchars($attempt['title']) ?></span>
        </p>
    </div>
    <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
        <?php
        $percent = ($attempt['total_marks'] > 0) ? round(($attempt['score'] / $attempt['total_marks']) * 100) : 0;
        $passmark = $attempt['passmark'] ?? 50;
        $passed = $percent >= $passmark;
        $statusColor = $passed ? 'success' : 'danger';
        ?>
        <div class="score-card shadow-sm rounded-4 p-3 bg-white border d-inline-flex align-items-center">
            <div class="bg-<?= $statusColor ?> bg-opacity-10 text-<?= $statusColor ?> rounded-circle p-3 me-3">
                <i class="bi bi-<?= $passed ? 'patch-check-fill' : 'exclamation-octagon-fill' ?> fs-3"></i>
            </div>
            <div class="text-start me-4">
                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Final Score</small>
                <span class="fs-4 fw-bold text-<?= $statusColor ?>"><?= $attempt['score'] ?> / <?= $attempt['total_marks'] ?></span>
            </div>
            <div class="border-start ps-4">
                <span class="badge bg-<?= $statusColor ?> rounded-pill px-4 py-2 fw-bold"><?= $passed ? 'PASSED' : 'FAILED' ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white border-0 p-4">
                <h5 class="fw-bold mb-0">Detailed Performance Review</h5>
            </div>
            <div class="card-body p-4">
                <?php foreach ($questions as $index => $q): ?>
                    <div class="question-feedback-item mb-5 p-4 rounded-4 border <?= $q['is_correct'] ? 'border-success border-opacity-10 bg-success bg-opacity-5' : 'border-danger border-opacity-10 bg-danger bg-opacity-5' ?>">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="d-flex">
                                <span class="badge bg-<?= $q['is_correct'] ? 'success' : 'danger' ?> me-3 rounded-pill d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                    <?= ($index + 1) ?>
                                </span>
                                <h5 class="fw-bold text-dark mb-0 pt-1"><?= htmlspecialchars($q['question_text']) ?></h5>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?= $q['is_correct'] ? 'success' : 'danger' ?> border-0 rounded-pill px-3 py-2">
                                    <i class="bi bi-<?= $q['is_correct'] ? 'check-circle' : 'x-circle' ?> me-1"></i>
                                    <?= $q['is_correct'] ? 'Correct' : 'Incorrect' ?>
                                </span>
                                <div class="mt-2 small fw-bold text-muted"><?= $q['marks_awarded'] ?> / <?= $q['marks'] ?> Marks</div>
                            </div>
                        </div>
                        
                        <div class="ms-5 ps-2">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="bg-white p-3 rounded-3 border">
                                        <small class="text-muted d-block text-uppercase fw-bold mb-2" style="font-size: 0.6rem;">Your Answer</small>
                                        <div class="fw-bold <?= $q['is_correct'] ? 'text-success' : 'text-danger' ?>">
                                            <?php if ($q['student_answer'] === null || $q['student_answer'] === ''): ?>
                                                <span class="text-muted fst-italic fw-normal">No response</span>
                                            <?php else: ?>
                                                <?= htmlspecialchars($q['student_answer']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!$q['is_correct']): ?>
                                    <div class="col-md-6">
                                        <div class="bg-white p-3 rounded-3 border border-success border-opacity-50">
                                            <small class="text-success d-block text-uppercase fw-bold mb-2" style="font-size: 0.6rem;">Correct Answer</small>
                                            <div class="fw-bold text-success">
                                                <i class="bi bi-check2-circle me-1"></i>
                                                <?= htmlspecialchars($q['correct_answer']) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="text-center mt-5">
                    <a href="results.php" class="btn btn-primary fw-bold px-5 py-3 rounded-pill shadow-sm">
                        <i class="bi bi-arrow-left me-2"></i>Return to My Results
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
