-- Supabase / PostgreSQL schema for KoKCS Online Assessment Portal
-- Compatible with the current PHP app structure and role-based logic.

BEGIN;

DROP TABLE IF EXISTS attempt_answers CASCADE;
DROP TABLE IF EXISTS quiz_attempts CASCADE;
DROP TABLE IF EXISTS quiz_questions CASCADE;
DROP TABLE IF EXISTS quizzes CASCADE;
DROP TABLE IF EXISTS questions CASCADE;
DROP TABLE IF EXISTS users CASCADE;

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('admin', 'supervisor', 'teacher', 'student')),
    class_level VARCHAR(50),
    subject VARCHAR(100),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE questions (
    id SERIAL PRIMARY KEY,
    teacher_id INTEGER NOT NULL,
    subject VARCHAR(100) NOT NULL,
    topic VARCHAR(255) NOT NULL,
    difficulty VARCHAR(20) NOT NULL CHECK (difficulty IN ('easy', 'medium', 'hard')),
    question_text TEXT NOT NULL,
    type VARCHAR(30) NOT NULL CHECK (type IN ('multiple_choice', 'true_false', 'short_answer')),
    options_json JSONB,
    correct_answer TEXT NOT NULL,
    explanation TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT fk_questions_teacher
        FOREIGN KEY (teacher_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE quizzes (
    id SERIAL PRIMARY KEY,
    teacher_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    class_level VARCHAR(50) NOT NULL,
    duration_minutes INTEGER NOT NULL,
    start_time TIMESTAMPTZ NOT NULL,
    end_time TIMESTAMPTZ NOT NULL,
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'pending', 'approved', 'live', 'closed')),
    randomize BOOLEAN DEFAULT FALSE,
    max_attempts INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT fk_quizzes_teacher
        FOREIGN KEY (teacher_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE quiz_questions (
    quiz_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    order_index INTEGER NOT NULL,
    marks INTEGER NOT NULL DEFAULT 1,
    PRIMARY KEY (quiz_id, question_id),
    CONSTRAINT fk_quiz_questions_quiz
        FOREIGN KEY (quiz_id)
        REFERENCES quizzes(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_quiz_questions_question
        FOREIGN KEY (question_id)
        REFERENCES questions(id)
        ON DELETE CASCADE
);

CREATE TABLE quiz_attempts (
    id SERIAL PRIMARY KEY,
    quiz_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    started_at TIMESTAMPTZ NOT NULL,
    submitted_at TIMESTAMPTZ,
    score INTEGER DEFAULT 0,
    total_marks INTEGER DEFAULT 0,
    status VARCHAR(20) DEFAULT 'in_progress' CHECK (status IN ('in_progress', 'submitted', 'graded')),
    CONSTRAINT fk_quiz_attempts_quiz
        FOREIGN KEY (quiz_id)
        REFERENCES quizzes(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_quiz_attempts_student
        FOREIGN KEY (student_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE attempt_answers (
    id SERIAL PRIMARY KEY,
    attempt_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    student_answer TEXT,
    is_correct BOOLEAN,
    marks_awarded INTEGER DEFAULT 0,
    CONSTRAINT fk_attempt_answers_attempt
        FOREIGN KEY (attempt_id)
        REFERENCES quiz_attempts(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_attempt_answers_question
        FOREIGN KEY (question_id)
        REFERENCES questions(id)
        ON DELETE CASCADE
);

-- Seed demo users
-- Admin password: admin@kok
-- Hash generated with bcrypt for password_hash verification in the PHP app.
INSERT INTO users (name, email, password_hash, role, class_level, subject) VALUES
('Admin User', 'admin@kokcs.edu.ug', '$2y$10$oCrM.CMFArVx2KfoFUXv1Om7hKah8QbYA4JKnOv4sJBoeDc.BXaiO', 'admin', NULL, NULL),
('Pretty Supervisor', 'pretty@kokcs.edu.ug', '$2y$10$txIFfHo3cMduy/3i2Ikbie291kvCRQ/Pfsk4DpisVbZp08hsRV.CK', 'supervisor', 'S4', 'Mathematics'),
('Lameck Teacher', 'lameck@kokcs.edu.ug', '$2y$10$RKD8fJSmeoajpiBwH/oy9.jXs6wbr9rdXTyBEpYXkR/BD2nt.8joO', 'teacher', NULL, 'Mathematics'),
('Eriya Student', 'eriya@kokcs.edu.ug', '$2y$10$Vl.jtwN3xXm/uverjoYyw.eIc/pxT6g/0Za.KpX2hHTkeORcJQxk6', 'student', 'S4', 'Mathematics');

-- Seed sample questions
INSERT INTO questions (teacher_id, subject, topic, difficulty, question_text, type, options_json, correct_answer, explanation)
VALUES
(3, 'Mathematics', 'Algebra', 'easy', 'What is 2 + 2?', 'multiple_choice', '["1", "2", "3", "4"]', '4', 'Basic addition.'),
(3, 'Mathematics', 'Geometry', 'medium', 'A triangle has 4 sides.', 'true_false', NULL, 'False', 'A triangle always has 3 sides.'),
(3, 'Mathematics', 'Calculus', 'hard', 'What is the derivative of x^2?', 'short_answer', NULL, '2x', 'Power rule of differentiation.');

-- Seed sample quizzes
INSERT INTO quizzes (teacher_id, title, subject, class_level, duration_minutes, start_time, end_time, status, randomize, max_attempts)
VALUES
(3, 'Math Midterm Exam', 'Mathematics', 'S4', 60, NOW() - INTERVAL '1 day', NOW() + INTERVAL '1 day', 'live', TRUE, 1),
(3, 'Math Quiz 1', 'Mathematics', 'S4', 30, NOW() - INTERVAL '7 day', NOW() - INTERVAL '6 day', 'closed', FALSE, 1);

-- Link questions to quizzes
INSERT INTO quiz_questions (quiz_id, question_id, order_index, marks)
VALUES
(1, 1, 1, 5),
(1, 2, 2, 5),
(1, 3, 3, 10),
(2, 1, 1, 5);

COMMIT;
