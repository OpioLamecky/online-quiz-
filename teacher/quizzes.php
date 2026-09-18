<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('teacher');

// Handle form submission to create quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $class_level = $_POST['class_level'];
    $duration = $_POST['duration'];
    $passmark = $_POST['passmark'] ?? 50;
    $timezone = new DateTimeZone('Africa/Kampala');
    $startDate = DateTime::createFromFormat('Y-m-d\\TH:i', $_POST['start_time'], $timezone);
    $endDate = DateTime::createFromFormat('Y-m-d\\TH:i', $_POST['end_time'], $timezone);

    if (!$startDate || !$endDate || $endDate <= $startDate) {
        die('Please provide a valid quiz schedule with an end time after the start time.');
    }

    // Store scheduled instants explicitly in UTC; teachers enter Kampala local time.
    $utc = new DateTimeZone('UTC');
    $start_time = $startDate->setTimezone($utc)->format('Y-m-d H:i:sP');
    $end_time = $endDate->setTimezone($utc)->format('Y-m-d H:i:sP');
    
    // Auto-migrate DB
    try {
        $pdo->exec("ALTER TABLE quizzes ADD COLUMN passmark INT DEFAULT 50");
    } catch(PDOException $e) {}
    
    $stmt = $pdo->prepare("INSERT INTO quizzes (teacher_id, title, subject, class_level, duration_minutes, passmark, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft')");
    $stmt->execute([$_SESSION['user_id'], $title, $subject, $class_level, $duration, $passmark, $start_time, $end_time]);
    
    header('Location: quizzes.php?success=1');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_approval') {
    $quiz_id = $_POST['quiz_id'];
    $stmt = $pdo->prepare("UPDATE quizzes SET status = 'pending' WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$quiz_id, $_SESSION['user_id']]);
    header('Location: quizzes.php?submitted=1');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE teacher_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$quizzes = $stmt->fetchAll();

$userStmt = $pdo->prepare("SELECT subject, class_level FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$teacherData = $userStmt->fetch();
$teacherSubjects = !empty($teacherData['subject']) ? array_map('trim', explode(',', $teacherData['subject'])) : [];
$teacherClasses = !empty($teacherData['class_level']) ? array_map('trim', explode(',', $teacherData['class_level'])) : [];

require_once '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-journal-text"></i> Manage Quizzes</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuizModal"><i class="bi bi-plus-circle"></i> Create Quiz</button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">Quiz created successfully as draft!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($_GET['submitted'])): ?>
    <div class="alert alert-info alert-dismissible fade show">Quiz submitted for approval.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $q): ?>
                        <?php
                            $statusClass = strtolower($q['status']);
                            $statusText = ucfirst($q['status']);
                            if ($q['status'] === 'live') {
                                $now = new DateTime();
                                $start = new DateTime($q['start_time']);
                                $end = new DateTime($q['end_time']);
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
                            <td><?= htmlspecialchars($q['title']) ?></td>
                            <td><?= htmlspecialchars($q['class_level']) ?></td>
                            <td><?= $q['duration_minutes'] ?> min</td>
                            <td><span class="status-badge status-<?= $statusClass ?>"><?= htmlspecialchars($statusText) ?></span></td>
                            <td>
                                <a href="manage_quiz.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Manage Questions"><i class="bi bi-list-check"></i></a>
                                <?php if ($q['status'] === 'draft'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="submit_approval">
                                        <input type="hidden" name="quiz_id" value="<?= $q['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Submit for Approval"><i class="bi bi-send"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($quizzes)): ?>
                        <tr><td colspan="5" class="text-center py-3">No quizzes found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Quiz Modal -->
<div class="modal fade" id="createQuizModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Quiz Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Subject</label>
                            <select name="subject" class="form-select" required>
                                <option value="">Select Subject...</option>
                                <?php foreach($teacherSubjects as $tsubj): ?>
                                    <?php if(!empty($tsubj)): ?>
                                        <option value="<?= htmlspecialchars($tsubj) ?>"><?= htmlspecialchars($tsubj) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Class Level</label>
                            <select name="class_level" class="form-select" required>
                                <option value="">Select Class...</option>
                                <?php foreach($teacherClasses as $tclass): ?>
                                    <?php if(!empty($tclass)): ?>
                                        <option value="<?= htmlspecialchars($tclass) ?>"><?= htmlspecialchars($tclass) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Duration (minutes)</label>
                            <input type="number" name="duration" class="form-control" min="1" value="60" required>
                        </div>
                        <div class="col-md-6">
                            <label>Passmark (%)</label>
                            <input type="number" name="passmark" class="form-control" min="1" max="100" value="50" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Start Time</label>
                            <input type="datetime-local" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>End Time</label>
                            <input type="datetime-local" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Quiz</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const startTimeInput = document.querySelector('input[name="start_time"]');
    const endTimeInput = document.querySelector('input[name="end_time"]');
    const durationInput = document.querySelector('input[name="duration"]');

    function calculateDuration() {
        if (startTimeInput.value && endTimeInput.value) {
            const start = new Date(startTimeInput.value);
            const end = new Date(endTimeInput.value);
            
            if (end > start) {
                const diffMs = end - start;
                const diffMins = Math.round(diffMs / 60000); // convert ms to minutes
                durationInput.value = diffMins;
                
                // Add a brief visual highlight to show it was auto-calculated
                durationInput.style.transition = 'all 0.3s ease';
                durationInput.style.backgroundColor = '#d1e7dd';
                setTimeout(() => {
                    durationInput.style.backgroundColor = '';
                }, 800);
            }
        }
    }

    startTimeInput.addEventListener('change', calculateDuration);
    endTimeInput.addEventListener('change', calculateDuration);
});
</script>

<?php require_once '../includes/footer.php'; ?>
