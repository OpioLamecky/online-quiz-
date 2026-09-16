<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('teacher');

$teacherId = $_SESSION['user_id'];

// Filters
$quizFilter = $_GET['quiz_id'] ?? '';
$searchName = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'date_desc';

// Get Teacher's Quizzes for Dropdown
$quizzesStmt = $pdo->prepare("SELECT id, title FROM quizzes WHERE teacher_id = ? ORDER BY created_at DESC");
$quizzesStmt->execute([$teacherId]);
$teacherQuizzes = $quizzesStmt->fetchAll();

// Base Query
$sql = "SELECT qa.*, q.title as quiz_title, u.name as student_name, u.class_level
        FROM quiz_attempts qa 
        JOIN quizzes q ON qa.quiz_id = q.id 
        JOIN users u ON qa.student_id = u.id
        WHERE q.teacher_id = :teacher_id";

$params = [':teacher_id' => $teacherId];

if ($quizFilter) {
    $sql .= " AND q.id = :quiz_id";
    $params[':quiz_id'] = $quizFilter;
}

if ($searchName) {
    $sql .= " AND u.name LIKE :search";
    $params[':search'] = "%$searchName%";
}

// Sorting logic
if ($sort === 'perf_desc') {
    $sql .= " ORDER BY (qa.score / NULLIF(qa.total_marks, 0)) DESC";
} elseif ($sort === 'perf_asc') {
    $sql .= " ORDER BY (qa.score / NULLIF(qa.total_marks, 0)) ASC";
} elseif ($sort === 'name_asc') {
    $sql .= " ORDER BY u.name ASC";
} elseif ($sort === 'name_desc') {
    $sql .= " ORDER BY u.name DESC";
} else {
    $sql .= " ORDER BY qa.started_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-bar-chart"></i> Students' Results Analysis</h2>
        <p class="text-muted mb-0">Classify, filter, and sort student performances</p>
    </div>
    <button onclick="window.print()" class="btn btn-primary d-print-none"><i class="bi bi-printer"></i> Print Report</button>
</div>

<!-- Filters & Sorting Card -->
<div class="card mb-4 shadow-sm border-0 bg-white d-print-none">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold text-uppercase">Filter by Quiz</label>
                <select name="quiz_id" class="form-select">
                    <option value="">All Quizzes</option>
                    <?php foreach($teacherQuizzes as $tq): ?>
                        <option value="<?= $tq['id'] ?>" <?= $quizFilter == $tq['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tq['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold text-uppercase">Student Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search name..." value="<?= htmlspecialchars($searchName) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold text-uppercase">Sort By</label>
                <select name="sort" class="form-select">
                    <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Date (Newest First)</option>
                    <option value="perf_desc" <?= $sort === 'perf_desc' ? 'selected' : '' ?>>Performance (Highest First)</option>
                    <option value="perf_asc" <?= $sort === 'perf_asc' ? 'selected' : '' ?>>Performance (Lowest First)</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="py-3">Student Name</th>
                        <th>Class</th>
                        <th>Quiz Title</th>
                        <th>Date Taken</th>
                        <th>Score</th>
                        <th>Classification</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($r['student_name']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($r['class_level']) ?></span></td>
                            <td><?= htmlspecialchars($r['quiz_title']) ?></td>
                            <td><small class="text-muted"><i class="bi bi-calendar"></i> <?= date('M d, Y', strtotime($r['started_at'])) ?></small></td>
                            <td>
                                <?php if ($r['status'] === 'graded'): ?>
                                    <span class="fw-bold text-primary"><?= $r['score'] ?></span> <span class="text-muted small">/ <?= $r['total_marks'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'graded'): ?>
                                    <?php 
                                    $percent = ($r['total_marks'] > 0) ? round(($r['score'] / $r['total_marks']) * 100) : 0; 
                                    if ($percent >= 80) {
                                        $badgeClass = 'bg-success';
                                        $classText = 'Excellent';
                                    } elseif ($percent >= 60) {
                                        $badgeClass = 'bg-primary';
                                        $classText = 'Good';
                                    } elseif ($percent >= 40) {
                                        $badgeClass = 'bg-warning text-dark';
                                        $classText = 'Average';
                                    } else {
                                        $badgeClass = 'bg-danger';
                                        $classText = 'Poor';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?> px-2 py-1 rounded-pill"><?= $percent ?>% - <?= $classText ?></span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($results)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-3"></i>No results matched your filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
