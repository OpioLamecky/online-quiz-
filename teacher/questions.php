<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('teacher');

// Handle form submission to create a question (simplified)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $subject = $_POST['subject'];
    $topic = $_POST['topic'];
    $difficulty = $_POST['difficulty'];
    $type = $_POST['type'];
    $question_text = $_POST['question_text'];
    $correct_answer = $_POST['correct_answer'];
    $explanation = $_POST['explanation'] ?? '';
    
    // For multiple choice, we would handle options_json here
    $options_json = ($type === 'multiple_choice') ? json_encode([$_POST['opt1'], $_POST['opt2'], $_POST['opt3'], $_POST['opt4']]) : NULL;
    
    $stmt = $pdo->prepare("INSERT INTO questions (teacher_id, subject, topic, difficulty, question_text, type, options_json, correct_answer, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $subject, $topic, $difficulty, $question_text, $type, $options_json, $correct_answer, $explanation]);
    
    header('Location: questions.php?success=1');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['question_id'];
    $subject = $_POST['subject'];
    $topic = $_POST['topic'];
    $difficulty = $_POST['difficulty'];
    $type = $_POST['type'];
    $question_text = $_POST['question_text'];
    $correct_answer = $_POST['correct_answer'];
    $explanation = $_POST['explanation'] ?? '';
    
    $options_json = ($type === 'multiple_choice') ? json_encode([$_POST['opt1'], $_POST['opt2'], $_POST['opt3'], $_POST['opt4']]) : NULL;
    
    $stmt = $pdo->prepare("UPDATE questions SET subject=?, topic=?, difficulty=?, question_text=?, type=?, options_json=?, correct_answer=?, explanation=? WHERE id=? AND teacher_id=?");
    $stmt->execute([$subject, $topic, $difficulty, $question_text, $type, $options_json, $correct_answer, $explanation, $id, $_SESSION['user_id']]);
    
    header('Location: questions.php?edited=1');
    exit();
}

// Fetch quizzes
$quizStmt = $pdo->prepare("SELECT id, title FROM quizzes WHERE teacher_id = ? ORDER BY created_at DESC");
$quizStmt->execute([$_SESSION['user_id']]);
$quizzes = $quizStmt->fetchAll();

// Fetch all questions
$qStmt = $pdo->prepare("SELECT * FROM questions WHERE teacher_id = ? ORDER BY created_at DESC");
$qStmt->execute([$_SESSION['user_id']]);
$allQuestions = $qStmt->fetchAll();

// Fetch relationships
$linkStmt = $pdo->prepare("
    SELECT qq.quiz_id, qq.question_id 
    FROM quiz_questions qq
    JOIN quizzes q ON qq.quiz_id = q.id
    WHERE q.teacher_id = ?
");
$linkStmt->execute([$_SESSION['user_id']]);
$links = $linkStmt->fetchAll();

// Group questions
$groupedQuestions = [];
$assignedQids = [];
$questionMap = [];

foreach($allQuestions as $q) {
    $questionMap[$q['id']] = $q;
}

foreach($links as $link) {
    $qid = $link['quiz_id'];
    $questId = $link['question_id'];
    if (!isset($groupedQuestions[$qid])) {
        $groupedQuestions[$qid] = [];
    }
    if (isset($questionMap[$questId])) {
        $groupedQuestions[$qid][] = $questionMap[$questId];
        $assignedQids[$questId] = true;
    }
}

$unassignedQuestions = [];
foreach($allQuestions as $q) {
    if (!isset($assignedQids[$q['id']])) {
        $unassignedQuestions[] = $q;
    }
}

require_once '../includes/header.php';
?>
<div class="row align-items-center mb-5">
    <div class="col-lg-8">
        <h2 class="display-6 fw-bold text-primary mb-2">
            <span class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-2">
                <i class="bi bi-collection-fill"></i>
            </span>
            Question Bank
        </h2>
        <p class="text-muted lead mb-0">Manage and organize your questions by quiz groups.</p>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <button class="btn btn-primary fw-bold px-4 py-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Question
        </button>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center p-3" role="alert">
        <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
        <div>Question added successfully!</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['edited'])): ?>
    <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center p-3" role="alert">
        <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
        <div>Question updated successfully!</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="accordion accordion-custom" id="questionsAccordion">
    
    <!-- Unassigned Questions First -->
    <div class="accordion-item border-0 mb-4 rounded-4 shadow-sm overflow-hidden">
        <h2 class="accordion-header">
            <button class="accordion-button fw-bold py-4 px-4 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUnassigned">
                <div class="d-flex align-items-center w-100">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2 me-3">
                        <i class="bi bi-inbox-fill"></i>
                    </div>
                    <span>Unassigned Questions</span>
                    <span class="badge bg-light text-dark border rounded-pill ms-auto px-3"><?= count($unassignedQuestions) ?></span>
                </div>
            </button>
        </h2>
        <div id="collapseUnassigned" class="accordion-collapse collapse show" data-bs-parent="#questionsAccordion">
            <div class="accordion-body p-0 border-top">
                <?php if (count($unassignedQuestions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Question Details</th>
                                    <th>Topic</th>
                                    <th>Type</th>
                                    <th>Difficulty</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($unassignedQuestions as $q): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars(substr($q['question_text'], 0, 80)) ?>...</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-muted border-0 rounded-pill px-3"><?= htmlspecialchars($q['topic']) ?></span>
                                        </td>
                                        <td>
                                            <span class="small fw-medium"><?= ucfirst(str_replace('_', ' ', $q['type'])) ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $diffClass = $q['difficulty'] === 'hard' ? 'danger' : ($q['difficulty'] === 'medium' ? 'warning' : 'success');
                                            ?>
                                            <span class="badge bg-<?= $diffClass ?> bg-opacity-10 text-<?= $diffClass ?> border-0 rounded-pill px-3"><?= ucfirst($q['difficulty']) ?></span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-light rounded-circle p-2" onclick="openEditModal(<?= $q['id'] ?>, '<?= htmlspecialchars(addslashes($q['subject'])) ?>', '<?= htmlspecialchars(addslashes($q['topic'])) ?>', '<?= $q['type'] ?>', '<?= $q['difficulty'] ?>', '<?= htmlspecialchars(addslashes($q['question_text'])) ?>', '<?= htmlspecialchars(addslashes($q['correct_answer'])) ?>', '<?= htmlspecialchars(addslashes($q['explanation'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($q['options_json'] ?? '')) ?>')">
                                                <i class="bi bi-pencil-square text-primary"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-check-all display-6 d-block mb-3 text-success"></i>
                        All your questions have been assigned to quizzes.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Assigned Quizzes -->
    <?php foreach($quizzes as $quiz): 
        $qQuestions = $groupedQuestions[$quiz['id']] ?? [];
    ?>
    <div class="accordion-item border-0 mb-4 rounded-4 shadow-sm overflow-hidden">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold py-4 px-4 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQuiz<?= $quiz['id'] ?>">
                <div class="d-flex align-items-center w-100">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <span><?= htmlspecialchars($quiz['title']) ?></span>
                    <span class="badge bg-light text-dark border rounded-pill ms-auto px-3"><?= count($qQuestions) ?></span>
                </div>
            </button>
        </h2>
        <div id="collapseQuiz<?= $quiz['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#questionsAccordion">
            <div class="accordion-body p-0 border-top">
                <?php if (count($qQuestions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Question Details</th>
                                    <th>Topic</th>
                                    <th>Type</th>
                                    <th>Difficulty</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($qQuestions as $q): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars(substr($q['question_text'], 0, 80)) ?>...</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-muted border-0 rounded-pill px-3"><?= htmlspecialchars($q['topic']) ?></span>
                                        </td>
                                        <td>
                                            <span class="small fw-medium"><?= ucfirst(str_replace('_', ' ', $q['type'])) ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $diffClass = $q['difficulty'] === 'hard' ? 'danger' : ($q['difficulty'] === 'medium' ? 'warning' : 'success');
                                            ?>
                                            <span class="badge bg-<?= $diffClass ?> bg-opacity-10 text-<?= $diffClass ?> border-0 rounded-pill px-3"><?= ucfirst($q['difficulty']) ?></span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-light rounded-circle p-2" onclick="openEditModal(<?= $q['id'] ?>, '<?= htmlspecialchars(addslashes($q['subject'])) ?>', '<?= htmlspecialchars(addslashes($q['topic'])) ?>', '<?= $q['type'] ?>', '<?= $q['difficulty'] ?>', '<?= htmlspecialchars(addslashes($q['question_text'])) ?>', '<?= htmlspecialchars(addslashes($q['correct_answer'])) ?>', '<?= htmlspecialchars(addslashes($q['explanation'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($q['options_json'] ?? '')) ?>')">
                                                <i class="bi bi-pencil-square text-primary"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-journal-x display-6 d-block mb-3"></i>
                        No questions assigned to this quiz yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
    .accordion-button:not(.collapsed) {
        background-color: white !important;
        color: var(--primary-color) !important;
        box-shadow: none !important;
    }
    .accordion-button:focus {
        box-shadow: none !important;
    }
    .accordion-button::after {
        background-size: 1rem;
    }
</style>

<!-- Enhanced Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold text-primary">Add New Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Subject</label>
                            <input type="text" name="subject" class="form-control bg-light border-0" value="<?= htmlspecialchars($_SESSION['user_name'] == 'Teacher User' ? 'Mathematics' : '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Topic</label>
                            <input type="text" name="topic" class="form-control bg-light border-0" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Question Type</label>
                            <select name="type" class="form-select bg-light border-0" required>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True or False</option>
                                <option value="short_answer">Short Answer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Difficulty</label>
                            <select name="difficulty" class="form-select bg-light border-0" required>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted">Question Text</label>
                        <textarea name="question_text" class="form-control bg-light border-0" rows="4" placeholder="Enter the full question text here..." required></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small text-muted">Correct Answer</label>
                        <input type="text" name="correct_answer" class="form-control bg-light border-0" placeholder="Specify the exact correct answer" required>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            For Multiple Choice, ensure it matches one of the options. For True/False, use "True" or "False".
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill">Create Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>

<!-- Edit Question Modal -->
<div class="modal fade" id="editQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="question_id" id="edit_question_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Subject</label>
                            <input type="text" name="subject" id="edit_subject" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Topic</label>
                            <input type="text" name="topic" id="edit_topic" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Type</label>
                            <select name="type" id="edit_type" class="form-select" required>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True or False</option>
                                <option value="short_answer">Short Answer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Difficulty</label>
                            <select name="difficulty" id="edit_difficulty" class="form-select" required>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Question Text</label>
                        <textarea name="question_text" id="edit_question_text" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3" id="edit_options_container" style="display:none;">
                        <label>Options (for Multiple Choice)</label>
                        <input type="text" name="opt1" id="edit_opt1" class="form-control mb-1" placeholder="Option 1">
                        <input type="text" name="opt2" id="edit_opt2" class="form-control mb-1" placeholder="Option 2">
                        <input type="text" name="opt3" id="edit_opt3" class="form-control mb-1" placeholder="Option 3">
                        <input type="text" name="opt4" id="edit_opt4" class="form-control mb-1" placeholder="Option 4">
                    </div>
                    <div class="mb-3">
                        <label>Correct Answer</label>
                        <input type="text" name="correct_answer" id="edit_correct_answer" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Explanation (Optional)</label>
                        <textarea name="explanation" id="edit_explanation" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, subject, topic, type, difficulty, question_text, correct_answer, explanation, options_json) {
    document.getElementById('edit_question_id').value = id;
    document.getElementById('edit_subject').value = subject;
    document.getElementById('edit_topic').value = topic;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_difficulty').value = difficulty;
    document.getElementById('edit_question_text').value = question_text;
    document.getElementById('edit_correct_answer').value = correct_answer;
    document.getElementById('edit_explanation').value = explanation;
    
    let optContainer = document.getElementById('edit_options_container');
    if (type === 'multiple_choice' && options_json) {
        optContainer.style.display = 'block';
        try {
            let opts = JSON.parse(options_json);
            document.getElementById('edit_opt1').value = opts[0] || '';
            document.getElementById('edit_opt2').value = opts[1] || '';
            document.getElementById('edit_opt3').value = opts[2] || '';
            document.getElementById('edit_opt4').value = opts[3] || '';
        } catch(e) {}
    } else {
        optContainer.style.display = 'none';
    }
    
    let modal = new bootstrap.Modal(document.getElementById('editQuestionModal'));
    modal.show();
}

// Add simple logic to show options fields for Add Question modal
document.querySelector('select[name="type"]').addEventListener('change', function() {
    // Basic implementation - ideally you'd add the options fields to the Add Modal too.
});
</script>

<?php require_once '../includes/footer.php'; ?>
