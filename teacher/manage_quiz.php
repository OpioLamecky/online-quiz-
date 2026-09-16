<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('teacher');

$quizId = $_GET['id'] ?? null;
if (!$quizId) {
    die("Quiz ID not provided.");
}

// Verify quiz belongs to teacher and is in draft
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND teacher_id = ?");
$stmt->execute([$quizId, $_SESSION['user_id']]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("Quiz not found or unauthorized.");
}

// Add existing question to quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_existing') {
    $question_id = $_POST['question_id'];
    $marks = $_POST['marks'];
    
    // Determine max order index
    $orderStmt = $pdo->prepare("SELECT COALESCE(MAX(order_index), 0) + 1 FROM quiz_questions WHERE quiz_id = ?");
    $orderStmt->execute([$quizId]);
    $nextOrder = $orderStmt->fetchColumn();
    
    $insertStmt = $pdo->prepare("INSERT IGNORE INTO quiz_questions (quiz_id, question_id, order_index, marks) VALUES (?, ?, ?, ?)");
    $insertStmt->execute([$quizId, $question_id, $nextOrder, $marks]);
    
    header("Location: manage_quiz.php?id=$quizId&success=added");
    exit();
}

// Remove question from quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $question_id = $_POST['question_id'];
    $delStmt = $pdo->prepare("DELETE FROM quiz_questions WHERE quiz_id = ? AND question_id = ?");
    $delStmt->execute([$quizId, $question_id]);
    
    header("Location: manage_quiz.php?id=$quizId&success=removed");
    exit();
}

// Create new question AND add to quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_and_add') {
    $subject = $_POST['subject'];
    $topic = $_POST['topic'];
    $difficulty = $_POST['difficulty'];
    $type = $_POST['type'];
    $question_text = $_POST['question_text'];
    $correct_answer = $_POST['correct_answer'];
    $explanation = $_POST['explanation'] ?? '';
    $marks = $_POST['marks'];
    
    $options_json = ($type === 'multiple_choice') ? json_encode([$_POST['opt1'], $_POST['opt2'], $_POST['opt3'], $_POST['opt4']]) : NULL;
    
    $insertQ = $pdo->prepare("INSERT INTO questions (teacher_id, subject, topic, difficulty, question_text, type, options_json, correct_answer, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertQ->execute([$_SESSION['user_id'], $subject, $topic, $difficulty, $question_text, $type, $options_json, $correct_answer, $explanation]);
    
    $newQuestionId = $pdo->lastInsertId();
    
    // Determine max order index
    $orderStmt = $pdo->prepare("SELECT COALESCE(MAX(order_index), 0) + 1 FROM quiz_questions WHERE quiz_id = ?");
    $orderStmt->execute([$quizId]);
    $nextOrder = $orderStmt->fetchColumn();
    
    $insertLink = $pdo->prepare("INSERT INTO quiz_questions (quiz_id, question_id, order_index, marks) VALUES (?, ?, ?, ?)");
    $insertLink->execute([$quizId, $newQuestionId, $nextOrder, $marks]);
    
    header("Location: manage_quiz.php?id=$quizId&success=created");
    exit();
}

// Get current questions in quiz
$qStmt = $pdo->prepare("SELECT q.*, qq.marks, qq.order_index FROM questions q JOIN quiz_questions qq ON q.id = qq.question_id WHERE qq.quiz_id = ? ORDER BY qq.order_index ASC");
$qStmt->execute([$quizId]);
$quizQuestions = $qStmt->fetchAll();

// Get questions from bank NOT in quiz
$bankStmt = $pdo->prepare("SELECT * FROM questions WHERE teacher_id = ? AND id NOT IN (SELECT question_id FROM quiz_questions WHERE quiz_id = ?)");
$bankStmt->execute([$_SESSION['user_id'], $quizId]);
$bankQuestions = $bankStmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="mb-4">
    <a href="quizzes.php" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Back to Quizzes</a>
    <h2>Manage Quiz Questions</h2>
    <p class="text-muted">Quiz: <strong><?= htmlspecialchars($quiz['title']) ?></strong></p>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">Action completed successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row">
    <!-- Current Quiz Questions -->
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Questions in this Quiz</h5>
                <span class="badge bg-primary rounded-pill"><?= count($quizQuestions) ?></span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Question</th>
                            <th>Marks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quizQuestions as $index => $qq): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars(substr($qq['question_text'], 0, 50)) ?>...</td>
                                <td><?= $qq['marks'] ?></td>
                                <td>
                                    <?php if ($quiz['status'] === 'draft'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="question_id" value="<?= $qq['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($quizQuestions)): ?>
                            <tr><td colspan="4" class="text-center py-3">No questions added yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Tools -->
    <div class="col-md-5">
        <?php if ($quiz['status'] === 'draft'): ?>
        
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Add from Question Bank</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_existing">
                    <div class="mb-3">
                        <select name="question_id" class="form-select" required>
                            <option value="">Select a question...</option>
                            <?php foreach ($bankQuestions as $bq): ?>
                                <option value="<?= $bq['id'] ?>"><?= htmlspecialchars(substr($bq['question_text'], 0, 40)) ?>... (<?= $bq['type'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Marks for this question</label>
                        <input type="number" name="marks" class="form-control" value="5" min="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Add to Quiz</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Create New Question</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="create_and_add">
                    
                    <input type="hidden" name="subject" value="<?= htmlspecialchars($quiz['subject']) ?>">
                    
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="small">Topic</label>
                            <input type="text" name="topic" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="small">Difficulty</label>
                            <select name="difficulty" class="form-select form-select-sm" required>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="small">Type</label>
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True or False</option>
                                <option value="short_answer">Short Answer</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="small">Marks</label>
                            <input type="number" name="marks" class="form-control form-control-sm" value="5" min="1" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="small">Question Text</label>
                        <textarea name="question_text" class="form-control form-control-sm" rows="2" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="small">Correct Answer</label>
                        <input type="text" name="correct_answer" class="form-control form-control-sm" placeholder="e.g. True, False, Option 1" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100">Create & Add to Quiz</button>
                </form>
            </div>
        </div>
        
        <?php else: ?>
            <div class="alert alert-warning">This quiz is no longer in draft status. Questions cannot be modified.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
