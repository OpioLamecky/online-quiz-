CREATE DATABASE IF NOT EXISTS kokcs_oqs;
USE kokcs_oqs;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'supervisor', 'teacher', 'student') NOT NULL,
    class_level VARCHAR(50),
    subject VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    topic VARCHAR(255) NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
    question_text TEXT NOT NULL,
    type ENUM('multiple_choice', 'true_false', 'short_answer') NOT NULL,
    options_json JSON,
    correct_answer TEXT NOT NULL,
    explanation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    class_level VARCHAR(50) NOT NULL,
    duration_minutes INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('draft', 'pending', 'approved', 'live', 'closed') DEFAULT 'draft',
    randomize BOOLEAN DEFAULT FALSE,
    max_attempts INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE quiz_questions (
    quiz_id INT NOT NULL,
    question_id INT NOT NULL,
    order_index INT NOT NULL,
    marks INT NOT NULL DEFAULT 1,
    PRIMARY KEY (quiz_id, question_id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE TABLE quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    started_at DATETIME NOT NULL,
    submitted_at DATETIME,
    score INT DEFAULT 0,
    total_marks INT DEFAULT 0,
    status ENUM('in_progress', 'submitted', 'graded') DEFAULT 'in_progress',
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE attempt_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    student_answer TEXT,
    is_correct BOOLEAN,
    marks_awarded INT DEFAULT 0,
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- Insert Demo Users
-- Passwords are 'password123' hashed using bcrypt ($2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
INSERT INTO users (name, email, password_hash, role, class_level, subject) VALUES
('Admin User', 'admin@kokcs.edu.ug', '$2y$10$Uc/lmwsDf.g.tJBjTd3G1OqkgXYcxmeI1hexnWWEHn11/Do8vZSRK', 'admin', NULL, NULL),
('Supervisor User', 'supervisor@kokcs.edu.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor', NULL, 'Science'),
('Teacher User', 'teacher@kokcs.edu.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', NULL, 'Mathematics'),
('Student User', 'student@kokcs.edu.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'S4', NULL);

-- Insert Sample Questions
INSERT INTO questions (teacher_id, subject, topic, difficulty, question_text, type, options_json, correct_answer, explanation) VALUES
(3, 'Mathematics', 'Algebra', 'easy', 'What is 2 + 2?', 'multiple_choice', '["1", "2", "3", "4"]', '4', 'Basic addition.'),
(3, 'Mathematics', 'Geometry', 'medium', 'A triangle has 4 sides.', 'true_false', NULL, 'False', 'A triangle always has 3 sides.'),
(3, 'Mathematics', 'Calculus', 'hard', 'What is the derivative of x^2?', 'short_answer', NULL, '2x', 'Power rule of differentiation.');

-- Insert Sample Quizzes
INSERT INTO quizzes (teacher_id, title, subject, class_level, duration_minutes, start_time, end_time, status, randomize, max_attempts) VALUES
(3, 'Math Midterm Exam', 'Mathematics', 'S4', 60, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY), 'live', TRUE, 1),
(3, 'Math Quiz 1', 'Mathematics', 'S4', 30, DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY), 'closed', FALSE, 1);

-- Link Questions to Quizzes
INSERT INTO quiz_questions (quiz_id, question_id, order_index, marks) VALUES
(1, 1, 1, 5),
(1, 2, 2, 5),
(1, 3, 3, 10),
(2, 1, 1, 5);
