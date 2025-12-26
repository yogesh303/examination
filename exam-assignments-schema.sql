CREATE TABLE IF NOT EXISTS exam_assignments (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id INT(11) UNSIGNED NOT NULL,
    user_id INT(11) UNSIGNED NOT NULL,
    assigned_by INT(11) UNSIGNED NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date TIMESTAMP NULL,
    status ENUM('assigned', 'in_progress', 'completed') DEFAULT 'assigned',
    attempt_id INT(11) UNSIGNED NULL,

    INDEX idx_exam (exam_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),

    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE SET NULL,

    UNIQUE KEY unique_user_exam (user_id, exam_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
