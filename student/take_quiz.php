<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('student');

$studentId = $_SESSION['user_id'];
$quizId = $_GET['id'] ?? null;

if (!$quizId) {
    header('Location: dashboard.php');
    exit();
}

// Get user's registered subjects
$userStmt = $pdo->prepare("SELECT class_level, subject FROM users WHERE id = ?");
$userStmt->execute([$studentId]);
$user = $userStmt->fetch();

// Parse student subjects
$studentSubjectsStr = $user['subject'];
$studentSubjects = [];
if (!empty($studentSubjectsStr)) {
    $studentSubjects = array_map('trim', explode(',', $studentSubjectsStr));
    $studentSubjects = array_filter($studentSubjects);
}

// Fetch quiz details
$quizStmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND status = 'live'");
$quizStmt->execute([$quizId]);
$quiz = $quizStmt->fetch();

if (!$quiz) {
    die("Quiz not found or not currently available.");
}

// Check if the student is registered for this quiz's subject
$quizSubject = $quiz['subject'];
if (empty($studentSubjects) || !in_array($quizSubject, $studentSubjects)) {
    // Redirect with an error message instead of just showing an error
    header('Location: dashboard.php?msg=You+are+not+registered+for+this+subject.+You+can+only+take+quizzes+for+registered+subjects.');
    exit();
}

// Also check class level
if ($quiz['class_level'] !== $user['class_level']) {
    header('Location: dashboard.php?msg=This+quiz+is+not+available+for+your+class+level.');
    exit();
}

$now = time();
$startTime = strtotime($quiz['start_time']);
$endTime = strtotime($quiz['end_time']);

if ($now < $startTime) {
    die("This quiz has not started yet.");
}
if ($now >= $endTime) {
    die("This quiz has already ended.");
}

// Check if student already submitted (simplified: only 1 attempt allowed)
$attemptCheck = $pdo->prepare("SELECT * FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?");
$attemptCheck->execute([$quizId, $studentId]);
$existingAttempt = $attemptCheck->fetch();

if ($existingAttempt && $existingAttempt['status'] !== 'in_progress') {
    die("You have already completed this quiz.");
}

// If no attempt exists, create one immediately when the student opens the quiz
if (!$existingAttempt) {
    $start_time = date('Y-m-d H:i:s');
    $createAttempt = $pdo->prepare("INSERT INTO quiz_attempts (quiz_id, student_id, started_at, status) VALUES (?, ?, ?, 'in_progress')");
    $createAttempt->execute([$quizId, $studentId, $start_time]);
    $attemptId = $pdo->lastInsertId();
} else {
    $attemptId = $existingAttempt['id'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'] ?? [];
    
    // Fetch questions and correct answers for grading
    $qStmt = $pdo->prepare("SELECT q.id, q.correct_answer, q.type, qq.marks FROM questions q JOIN quiz_questions qq ON q.id = qq.question_id WHERE qq.quiz_id = ?");
    $qStmt->execute([$quizId]);
    $questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalScore = 0;
    $totalMarks = 0;
    
    foreach ($questions as $q) {
        $qId = $q['id'];
        $totalMarks += $q['marks'];
        $studentAnswer = $answers[$qId] ?? null;
        
        $isCorrect = 0;
        $marksAwarded = 0;
        
        if ($studentAnswer !== null) {
            if ($q['type'] === 'multiple_choice' || $q['type'] === 'true_false') {
                if (strtolower(trim($studentAnswer)) === strtolower(trim($q['correct_answer']))) {
                    $isCorrect = 1;
                    $marksAwarded = $q['marks'];
                }
            } else {
                // Short answer might need manual grading, but we do exact match for simplicity here
                if (strtolower(trim($studentAnswer)) === strtolower(trim($q['correct_answer']))) {
                    $isCorrect = 1;
                    $marksAwarded = $q['marks'];
                }
            }
        }
        $totalScore += $marksAwarded;
        
        // Remove any previous answers for this attempt/question if resuming
        $delAnswer = $pdo->prepare("DELETE FROM attempt_answers WHERE attempt_id = ? AND question_id = ?");
        $delAnswer->execute([$attemptId, $qId]);
        
        $insertAnswer = $pdo->prepare("INSERT INTO attempt_answers (attempt_id, question_id, student_answer, is_correct, marks_awarded) VALUES (?, ?, ?, ?, ?)");
        $insertAnswer->execute([$attemptId, $qId, $studentAnswer, $isCorrect, $marksAwarded]);
    }
    
    // Update attempt
    $updateAttempt = $pdo->prepare("UPDATE quiz_attempts SET submitted_at = NOW(), score = ?, total_marks = ?, status = 'graded' WHERE id = ?");
    $updateAttempt->execute([$totalScore, $totalMarks, $attemptId]);
    
    if (isset($_POST['auto_submit'])) {
        header("Location: dashboard.php?msg=Time+is+up.+Your+quiz+has+been+auto-submitted+with+the+answers+you+attempted.");
    } else {
        header("Location: view_result.php?id=" . $attemptId . "&msg=Quiz+submitted+successfully!");
    }
    exit();
}

// Fetch questions for display
$questionsStmt = $pdo->prepare("
    SELECT q.*, qq.marks 
    FROM questions q 
    JOIN quiz_questions qq ON q.id = qq.question_id 
    WHERE qq.quiz_id = ? 
    ORDER BY qq.order_index ASC
");
$questionsStmt->execute([$quizId]);
$quizQuestions = $questionsStmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="quiz-focus-mode">
    <!-- Fixed Top Header -->
    <div class="quiz-header fixed-top bg-white border-bottom shadow-sm py-3" style="z-index: 1030;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-4 col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 d-flex align-items-center">
                            <i class="bi bi-clock-fill me-2"></i>
                            <span id="timer" class="fw-bold">--:--</span>
                        </div>
                    </div>
                </div>
                <div class="col-4 col-md-6 text-center">
                    <h6 class="fw-bold mb-0 d-none d-md-block"><?= htmlspecialchars($quiz['title']) ?></h6>
                    <div class="progress mt-2 mx-auto rounded-pill" style="height: 6px; width: 150px; background-color: #f1f5f9;">
                        <div id="quizProgressBar" class="progress-bar bg-primary transition-all" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
                <div class="col-4 col-md-3 text-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold" onclick="confirmCancel()">
                        Exit <span class="d-none d-md-inline">Quiz</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Centered Question -->
    <div class="container" style="margin-top: 100px; margin-bottom: 100px;">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form id="quizForm" method="POST">
                    <div id="questionsContainer">
                        <?php foreach ($quizQuestions as $index => $q): ?>
                            <div class="question-page" id="q-page-<?= $index ?>" style="<?= $index === 0 ? '' : 'display: none;' ?>">
                                <div class="text-center mb-5">
                                    <span class="text-primary fw-bold text-uppercase tracking-widest small">Question <?= ($index + 1) ?> of <?= count($quizQuestions) ?></span>
                                    <h2 class="fw-bold text-dark mt-2 lh-base"><?= htmlspecialchars($q['question_text']) ?></h2>
                                </div>

                                <div class="options-container">
                                    <?php if ($q['type'] === 'multiple_choice' && !empty($q['options_json'])): ?>
                                        <div class="row g-3">
                                            <?php 
                                            $options = json_decode($q['options_json'], true); 
                                            if (is_array($options)):
                                                foreach ($options as $optIndex => $opt): 
                                            ?>
                                                <div class="col-12">
                                                    <div class="option-item border rounded-4 p-4 transition-all bg-white shadow-sm" onclick="selectOption(this)">
                                                        <div class="form-check m-0 d-flex align-items-center">
                                                            <input class="form-check-input me-3" type="radio" 
                                                                   name="answers[<?= $q['id'] ?>]" 
                                                                   value="<?= htmlspecialchars($opt) ?>" 
                                                                   id="q<?= $q['id'] ?>_<?= $optIndex ?>"
                                                                   onchange="markAnswered(<?= $index ?>)">
                                                            <label class="form-check-label w-100 fw-semibold cursor-pointer fs-5" for="q<?= $q['id'] ?>_<?= $optIndex ?>">
                                                                <?= htmlspecialchars($opt) ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php 
                                                endforeach; 
                                            endif;
                                            ?>
                                        </div>
                                    <?php elseif ($q['type'] === 'true_false'): ?>
                                        <div class="row g-4">
                                            <div class="col-12 col-md-6">
                                                <div class="option-item border rounded-4 p-5 transition-all bg-white shadow-sm text-center" onclick="selectOption(this)">
                                                    <div class="form-check d-inline-block m-0">
                                                        <input class="form-check-input" type="radio" 
                                                               name="answers[<?= $q['id'] ?>]" 
                                                               value="True" 
                                                               id="q<?= $q['id'] ?>_true"
                                                               onchange="markAnswered(<?= $index ?>)">
                                                        <label class="form-check-label fw-bold cursor-pointer fs-4" for="q<?= $q['id'] ?>_true">TRUE</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="option-item border rounded-4 p-5 transition-all bg-white shadow-sm text-center" onclick="selectOption(this)">
                                                    <div class="form-check d-inline-block m-0">
                                                        <input class="form-check-input" type="radio" 
                                                               name="answers[<?= $q['id'] ?>]" 
                                                               value="False" 
                                                               id="q<?= $q['id'] ?>_false"
                                                               onchange="markAnswered(<?= $index ?>)">
                                                        <label class="form-check-label fw-bold cursor-pointer fs-4" for="q<?= $q['id'] ?>_false">FALSE</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-white p-4 rounded-4 border shadow-sm">
                                            <textarea class="form-control border-0 shadow-none fs-5 p-2" 
                                                      name="answers[<?= $q['id'] ?>]" 
                                                      rows="6" 
                                                      placeholder="Enter your detailed response here..."
                                                      oninput="markAnswered(<?= $index ?>)"></textarea>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Fixed Bottom Navigation -->
    <div class="quiz-footer fixed-bottom bg-white border-top py-3" style="z-index: 1030;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-6">
                    <button type="button" id="prevBtn" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-muted border" onclick="prevQuestion()" disabled>
                        <i class="bi bi-arrow-left me-2"></i> Previous
                    </button>
                </div>
                <div class="col-6 text-end">
                    <button type="button" id="nextBtn" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm" onclick="nextQuestion()">
                        Next <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    <button type="button" id="submitBtn" class="btn btn-success rounded-pill px-5 py-2 fw-bold shadow-sm d-none" onclick="confirmSubmit()">
                        Finish Quiz <i class="bi bi-check2-all ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f8fafc; }
    .quiz-header, .quiz-footer { height: 75px; display: flex; align-items: center; }
    .option-item { 
        cursor: pointer; 
        border: 2px solid transparent !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .option-item:hover { border-color: #e2e8f0 !important; transform: translateY(-2px); }
    .option-item:has(.form-check-input:checked) {
        border-color: var(--primary-color) !important;
        background-color: rgba(79, 70, 229, 0.03) !important;
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.1) !important;
    }
    .option-item:has(.form-check-input:checked) .form-check-label {
        color: var(--primary-color);
        font-weight: 700 !important;
    }
    
    .question-page { animation: slideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
</style>

<script>
let currentQuestionIndex = 0;
const totalQuestions = <?= count($quizQuestions) ?>;
const answeredQuestions = new Set();

function updateUI() {
    const progress = (answeredQuestions.size / totalQuestions) * 100;
    document.getElementById('quizProgressBar').style.width = progress + '%';
    
    // Toggle buttons
    document.getElementById('prevBtn').disabled = currentQuestionIndex === 0;
    
    if (currentQuestionIndex === totalQuestions - 1) {
        document.getElementById('nextBtn').classList.add('d-none');
        document.getElementById('submitBtn').classList.remove('d-none');
    } else {
        document.getElementById('nextBtn').classList.remove('d-none');
        document.getElementById('submitBtn').classList.add('d-none');
    }
}

function showQuestion(index) {
    document.querySelectorAll('.question-page').forEach(p => p.style.display = 'none');
    document.getElementById('q-page-' + index).style.display = 'block';
    currentQuestionIndex = index;
    updateUI();
}

function nextQuestion() {
    if (currentQuestionIndex < totalQuestions - 1) showQuestion(currentQuestionIndex + 1);
}

function prevQuestion() {
    if (currentQuestionIndex > 0) showQuestion(currentQuestionIndex - 1);
}

function markAnswered(index) {
    answeredQuestions.add(index);
    updateUI();
}

function selectOption(element) {
    const input = element.querySelector('.form-check-input');
    input.checked = true;
    input.dispatchEvent(new Event('change'));
}

// Initial call
updateUI();

<?php
$durationSeconds = $quiz['duration_minutes'] * 60;
$timeUntilEnd = $endTime - $now;
$actualTimeRemaining = min($durationSeconds, $timeUntilEnd);
?>
// Countdown timer adjusted for both duration and quiz end time
let timeInSeconds = <?= $actualTimeRemaining ?>;
const timerDisplay = document.getElementById('timer');

const countdown = setInterval(() => {
    timeInSeconds--;
    
    let minutes = Math.floor(timeInSeconds / 60);
    let seconds = timeInSeconds % 60;
    
    seconds = seconds < 10 ? '0' + seconds : seconds;
    
    timerDisplay.textContent = minutes + ':' + seconds;
    
    if (timeInSeconds <= 0) {
        clearInterval(countdown);
        Swal.fire({
            title: 'Time is Up!',
            text: 'Your quiz is being submitted automatically.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        }).then(() => {
            const form = document.getElementById('quizForm');
            const hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", "auto_submit");
            hiddenField.setAttribute("value", "1");
            form.appendChild(hiddenField);
            form.submit();
        });
    }
}, 1000);

function confirmSubmit() {
    Swal.fire({
        title: 'Submit Quiz?',
        text: 'Are you sure you want to submit? You cannot change your answers later.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Submit!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('quizForm').submit();
        }
    });
}

function confirmCancel() {
    Swal.fire({
        title: 'Cancel Quiz?',
        text: 'Are you sure you want to exit? Your progress will NOT be saved.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Exit'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'dashboard.php';
        }
    });
}

// Anti-cheat: Detect tab switching or window blur
let tabSwitches = 0;
document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
        tabSwitches++;
        if (tabSwitches === 1) {
            Swal.fire({
                title: 'Warning!',
                text: 'You have switched tabs or left the quiz window. This is strictly prohibited. If you do this one more time, your quiz will be automatically submitted.',
                icon: 'warning',
                confirmButtonColor: '#d33',
                confirmButtonText: 'I Understand'
            });
        } else if (tabSwitches >= 2) {
            Swal.fire({
                title: 'Violation Detected',
                text: 'You left the quiz window again. Your quiz is now being automatically submitted.',
                icon: 'error',
                allowOutsideClick: false,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                document.getElementById('quizForm').submit();
            });
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
