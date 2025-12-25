-- Complete Exam System Database Schema
-- Drop existing tables if they exist
DROP TABLE IF EXISTS user_answers;
DROP TABLE IF EXISTS exam_attempts;
DROP TABLE IF EXISTS exam_questions;
DROP TABLE IF EXISTS question_options;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS exams;
DROP TABLE IF EXISTS subjects;

-- 1. Subjects Table
CREATE TABLE subjects (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(255) NOT NULL,
    subject_code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_by INT(11) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_subject_code (subject_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Questions Table
CREATE TABLE questions (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_id INT(11) UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('radio', 'checkbox') NOT NULL DEFAULT 'radio',
    marks INT DEFAULT 1,
    created_by INT(11) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_subject (subject_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Question Options Table (4 options for each question)
CREATE TABLE question_options (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT(11) UNSIGNED NOT NULL,
    option_text TEXT NOT NULL,
    is_correct TINYINT(1) DEFAULT 0,
    option_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_question (question_id),
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Exams Table
CREATE TABLE exams (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(255) NOT NULL,
    subject_id INT(11) UNSIGNED NOT NULL,
    duration_minutes INT DEFAULT 60,
    total_marks INT DEFAULT 0,
    passing_marks INT DEFAULT 0,
    instructions TEXT,
    created_by INT(11) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_subject (subject_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Exam Questions Assignment Table
CREATE TABLE exam_questions (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id INT(11) UNSIGNED NOT NULL,
    question_id INT(11) UNSIGNED NOT NULL,
    question_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_exam (exam_id),
    INDEX idx_question (question_id),
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_exam_question (exam_id, question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Exam Attempts/Results Table (Main Results Table)
CREATE TABLE exam_attempts (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    exam_id INT(11) UNSIGNED NOT NULL,
    exam_name VARCHAR(255) NOT NULL,
    subject_name VARCHAR(255) NOT NULL,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    total_questions INT DEFAULT 0,
    attempted_questions INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    wrong_answers INT DEFAULT 0,
    marks_obtained DECIMAL(10,2) DEFAULT 0.00,
    total_marks DECIMAL(10,2) DEFAULT 0.00,
    percentage DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('started', 'completed', 'abandoned') DEFAULT 'started',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_exam (exam_id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. User Answers Table (Individual Question Answers)
CREATE TABLE user_answers (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT(11) UNSIGNED NOT NULL,
    question_id INT(11) UNSIGNED NOT NULL,
    selected_option_id INT(11) UNSIGNED,
    selected_options JSON,
    is_correct TINYINT(1) DEFAULT 0,
    marks_obtained DECIMAL(10,2) DEFAULT 0.00,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attempt (attempt_id),
    INDEX idx_question (question_id),
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Subjects
INSERT INTO subjects (subject_name, subject_code, description) VALUES
('Mathematics', 'MATH101', 'Basic Mathematics and Algebra'),
('Science', 'SCI101', 'General Science'),
('English', 'ENG101', 'English Language and Literature'),
('Computer Science', 'CS101', 'Introduction to Programming'),
('History', 'HIST101', 'World History');

-- Insert Sample Questions with Options

-- Math Question 1 (Radio)
INSERT INTO questions (subject_id, question_text, question_type, marks) VALUES
(1, 'What is 5 + 3?', 'radio', 1);
SET @q1 = LAST_INSERT_ID();
INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES
(@q1, '6', 0, 1),
(@q1, '7', 0, 2),
(@q1, '8', 1, 3),
(@q1, '9', 0, 4);

-- Math Question 2 (Radio)
INSERT INTO questions (subject_id, question_text, question_type, marks) VALUES
(1, 'What is 10 × 5?', 'radio', 1);
SET @q2 = LAST_INSERT_ID();
INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES
(@q2, '40', 0, 1),
(@q2, '45', 0, 2),
(@q2, '50', 1, 3),
(@q2, '55', 0, 4);

-- Math Question 3 (Checkbox)
INSERT INTO questions (subject_id, question_text, question_type, marks) VALUES
(1, 'Which of the following are even numbers?', 'checkbox', 2);
SET @q3 = LAST_INSERT_ID();
INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES
(@q3, '2', 1, 1),
(@q3, '3', 0, 2),
(@q3, '4', 1, 3),
(@q3, '5', 0, 4);

-- Science Question 1 (Radio)
INSERT INTO questions (subject_id, question_text, question_type, marks) VALUES
(2, 'What is the chemical symbol for water?', 'radio', 1);
SET @q4 = LAST_INSERT_ID();
INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES
(@q4, 'H2O', 1, 1),
(@q4, 'CO2', 0, 2),
(@q4, 'O2', 0, 3),
(@q4, 'N2', 0, 4);

-- Science Question 2 (Radio)
INSERT INTO questions (subject_id, question_text, question_type, marks) VALUES
(2, 'How many planets are in our solar system?', 'radio', 1);
SET @q5 = LAST_INSERT_ID();
INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES
(@q5, '7', 0, 1),
(@q5, '8', 1, 2),
(@q5, '9', 0, 3),
(@q5, '10', 0, 4);

-- English Question 1 (Radio)
INSERT INTO questions (subject_id, question_text, question_type, marks) VALUES
(3, 'What is the plural of "child"?', 'radio', 1);
SET @q6 = LAST_INSERT_ID();
INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES
(@q6, 'childs', 0, 1),
(@q6, 'childes', 0, 2),
(@q6, 'children', 1, 3),
(@q6, 'childrens', 0, 4);

-- Sample Exam
INSERT INTO exams (exam_name, subject_id, duration_minutes, passing_marks, instructions) VALUES
('Mathematics Final Exam', 1, 30, 3, 'Answer all questions carefully. Each question carries marks as indicated.');

-- Sample data showing results format (commented out)
-- Uncomment ONLY if you have a user with ID 1 in your users table
-- This is just to show the structure - actual results are created when students take exams

-- INSERT INTO exam_attempts (user_id, user_name, exam_id, exam_name, subject_name, total_questions, attempted_questions, correct_answers, wrong_answers, marks_obtained, total_marks, percentage, status, end_time) VALUES
-- (1, 'Test Student', 1, 'Mathematics Final Exam', 'Mathematics', 5, 5, 4, 1, 4, 5, 80.00, 'completed', NOW());

-- NOTE: Real results will be automatically created when students take exams