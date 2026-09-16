<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('teacher');

$teacherId = $_SESSION['user_id'];

// Stats
$myQuizzes = $pdo->prepare("SELECT COUNT(*) FROM quizzes WHERE teacher_id = ?");
$myQuizzes->execute([$teacherId]);
$totalQuizzes = $myQuizzes->fetchColumn();

$myQuestions = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE teacher_id = ?");
$myQuestions->execute([$teacherId]);
$totalQuestions = $myQuestions->fetchColumn();

// Recent Quizzes
$recentQuizzesStmt = $pdo->prepare("SELECT id, title, subject, class_level, status, created_at, start_time, end_time FROM quizzes WHERE teacher_id = ? ORDER BY created_at DESC LIMIT 5");
$recentQuizzesStmt->execute([$teacherId]);
$recentQuizzes = $recentQuizzesStmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="row align-items-center mb-5">
    <div class="col-lg-8">
        <h2 class="display-6 fw-bold text-primary mb-2">
            <span class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-2">
                <i class="bi bi-person-workspace"></i>
            </span>
            Teacher Dashboard
        </h2>
        <p class="text-muted lead mb-0">Welcome back, Professor! Here's an overview of your academic materials.</p>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="quizzes.php" class="btn btn-primary fw-bold px-4 py-2 rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-2"></i>Create New Quiz
        </a>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="bi bi-journal-text fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Total Quizzes</small>
                        <h3 class="fw-bold mb-0"><?= $totalQuizzes ?></h3>
                    </div>
                </div>
                <a href="quizzes.php" class="small fw-bold text-decoration-none">Manage all quizzes <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="bi bi-database fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Question Bank</small>
                        <h3 class="fw-bold mb-0"><?= $totalQuestions ?></h3>
                    </div>
                </div>
                <a href="questions.php" class="small fw-bold text-success text-decoration-none">Manage questions <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                        <i class="bi bi-bar-chart fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Total Results</small>
                        <h3 class="fw-bold mb-0">View</h3>
                    </div>
                </div>
                <a href="all_results.php" class="small fw-bold text-info text-decoration-none">Analyze performance <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                <div class="bg-light rounded-pill px-3 py-2 mb-2 d-inline-block mx-auto">
                    <i class="bi bi-calendar3 me-2 text-primary"></i>
                    <span class="small fw-bold text-dark"><?= date('M d, Y') ?></span>
                </div>
                <small class="text-muted">Today's Date</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white border-0 p-4 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0">Recently Created Quizzes</h5>
        <a href="quizzes.php" class="btn btn-sm btn-light fw-bold rounded-pill px-3">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Quiz Info</th>
                        <th>Class Level</th>
                        <th>Created Date</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recentQuizzes) > 0): ?>
                        <?php foreach ($recentQuizzes as $quiz): ?>
                            <?php
                                $statusClass = strtolower($quiz['status']);
                                $statusText = ucfirst($quiz['status']);
                                if ($quiz['status'] === 'live') {
                                    $now = new DateTime();
                                    $start = new DateTime($quiz['start_time']);
                                    $end = new DateTime($quiz['end_time']);
                                    if ($now < $start) {
                                        $statusClass = 'upcoming';
                                        $statusText = 'Upcoming';
                                    } elseif ($now > $end) {
                                        $statusClass = 'expired';
                                        $statusText = 'Expired';
                                    }
                                }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-2 p-2 me-3 text-primary">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($quiz['title']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($quiz['subject']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-3"><?= htmlspecialchars($quiz['class_level']) ?></span>
                                </td>
                                <td>
                                    <span class="small text-muted"><?= date('M d, Y', strtotime($quiz['created_at'])) ?></span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $statusClass ?>">
                                        <?= htmlspecialchars($statusText) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="manage_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-sm btn-light rounded-circle p-2 me-1" title="Edit">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <a href="all_results.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-sm btn-light rounded-circle p-2" title="View Results">
                                            <i class="bi bi-bar-chart-fill text-info"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x display-6 d-block mb-3"></i>
                                You haven't created any quizzes yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
