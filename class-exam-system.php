<?php
require_once 'config.php';

class ExamSystem {
    private $conn;
    private $userId;
    private $userRole;
    
    public function __construct($userId) {
        $this->conn = getDBConnection();
        $this->userId = $userId;
        $this->userRole = $this->getUserRole();
    }
    
    private function getUserRole() {
        $stmt = $this->conn->prepare("SELECT user_role FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user['user_role'] ?? 'learner';
    }
    
    public function isAdmin() {
        return $this->userRole === 'admin';
    }
    
    // ==================== SUBJECTS ====================
    
    public function getAllSubjects() {
        $stmt = $this->conn->prepare("SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name");
        $stmt->execute();
        $result = $stmt->get_result();
        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }
        $stmt->close();
        return $subjects;
    }
    
    public function addSubject($name, $code, $description = '') {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $stmt = $this->conn->prepare("INSERT INTO subjects (subject_name, subject_code, description, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $code, $description, $this->userId);
        
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Subject added successfully', 'id' => $id];
        }
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to add subject: ' . $error];
    }
    
    // ==================== QUESTIONS ====================
    
    public function addQuestion($subjectId, $questionText, $questionType, $marks, $options) {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        // Validate: Must have exactly 4 options
        if (count($options) !== 4) {
            return ['success' => false, 'message' => 'Exactly 4 options are required'];
        }
        
        // Validate: At least one option must be correct
        $hasCorrect = false;
        foreach ($options as $option) {
            if ($option['is_correct']) {
                $hasCorrect = true;
                break;
            }
        }
        
        if (!$hasCorrect) {
            return ['success' => false, 'message' => 'At least one option must be marked as correct'];
        }
        
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Insert question
            $stmt = $this->conn->prepare("INSERT INTO questions (subject_id, question_text, question_type, marks, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issii", $subjectId, $questionText, $questionType, $marks, $this->userId);
            $stmt->execute();
            $questionId = $stmt->insert_id;
            $stmt->close();
            
            // Insert 4 options
            $stmt = $this->conn->prepare("INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES (?, ?, ?, ?)");
            
            for ($i = 0; $i < 4; $i++) {
                $isCorrect = $options[$i]['is_correct'] ? 1 : 0;
                $order = $i + 1;
                $stmt->bind_param("isii", $questionId, $options[$i]['text'], $isCorrect, $order);
                $stmt->execute();
            }
            $stmt->close();
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Question added successfully', 'id' => $questionId];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Failed to add question: ' . $e->getMessage()];
        }
    }
    
    public function getQuestionsBySubject($subjectId) {
        $stmt = $this->conn->prepare("
            SELECT q.*, s.subject_name,
                   (SELECT COUNT(*) FROM question_options WHERE question_id = q.id) as option_count
            FROM questions q
            JOIN subjects s ON q.subject_id = s.id
            WHERE q.subject_id = ? AND q.is_active = 1
            ORDER BY q.created_at DESC
        ");
        $stmt->bind_param("i", $subjectId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $stmt->close();
        return $questions;
    }
    
    public function getQuestionWithOptions($questionId) {
        // Get question
        $stmt = $this->conn->prepare("SELECT q.*, s.subject_name FROM questions q JOIN subjects s ON q.subject_id = s.id WHERE q.id = ?");
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $question = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$question) {
            return null;
        }
        
        // Get options
        $stmt = $this->conn->prepare("SELECT * FROM question_options WHERE question_id = ? ORDER BY option_order");
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
        $stmt->close();
        
        $question['options'] = $options;
        return $question;
    }
    
    public function deleteQuestion($questionId) {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $stmt = $this->conn->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->bind_param("i", $questionId);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Question deleted successfully'];
        }
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to delete question'];
    }
    
    // ==================== EXAMS ====================
    
    public function createExam($examName, $subjectId, $duration, $passingMarks, $instructions = '') {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $stmt = $this->conn->prepare("INSERT INTO exams (exam_name, subject_id, duration_minutes, passing_marks, instructions, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiisi", $examName, $subjectId, $duration, $passingMarks, $instructions, $this->userId);
        
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Exam created successfully', 'id' => $id];
        }
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to create exam'];
    }
    
    public function assignQuestionsToExam($examId, $questionIds) {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        // Delete existing assignments
        $stmt = $this->conn->prepare("DELETE FROM exam_questions WHERE exam_id = ?");
        $stmt->bind_param("i", $examId);
        $stmt->execute();
        $stmt->close();
        
        // Insert new assignments
        $stmt = $this->conn->prepare("INSERT INTO exam_questions (exam_id, question_id, question_order) VALUES (?, ?, ?)");
        
        foreach ($questionIds as $index => $questionId) {
            $order = $index + 1;
            $stmt->bind_param("iii", $examId, $questionId, $order);
            $stmt->execute();
        }
        $stmt->close();
        
        // Update total marks
        $stmt = $this->conn->prepare("
            UPDATE exams e
            SET total_marks = (
                SELECT SUM(q.marks)
                FROM exam_questions eq
                JOIN questions q ON eq.question_id = q.id
                WHERE eq.exam_id = ?
            )
            WHERE e.id = ?
        ");
        $stmt->bind_param("ii", $examId, $examId);
        $stmt->execute();
        $stmt->close();
        
        return ['success' => true, 'message' => 'Questions assigned successfully'];
    }
    
    public function getAllExams() {
        $stmt = $this->conn->prepare("
            SELECT e.*, s.subject_name,
                   (SELECT COUNT(*) FROM exam_questions WHERE exam_id = e.id) as question_count
            FROM exams e
            JOIN subjects s ON e.subject_id = s.id
            WHERE e.is_active = 1
            ORDER BY e.created_at DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $exams = [];
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }
        $stmt->close();
        return $exams;
    }
    
    // ==================== RESULTS ====================
    
    public function getAllResults() {
        if ($this->isAdmin()) {
            // Admin sees all results
            $stmt = $this->conn->prepare("
                SELECT * FROM exam_attempts
                WHERE status = 'completed'
                ORDER BY created_at DESC
            ");
        } else {
            // Learner sees only their results
            $stmt = $this->conn->prepare("
                SELECT * FROM exam_attempts
                WHERE user_id = ? AND status = 'completed'
                ORDER BY created_at DESC
            ");
            $stmt->bind_param("i", $this->userId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
        return $results;
    }
    
    public function getResultDetails($attemptId) {
        // Check access
        $stmt = $this->conn->prepare("SELECT user_id FROM exam_attempts WHERE id = ?");
        $stmt->bind_param("i", $attemptId);
        $stmt->execute();
        $attempt = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$attempt || (!$this->isAdmin() && $attempt['user_id'] != $this->userId)) {
            return null;
        }
        
        // Get attempt details
        $stmt = $this->conn->prepare("SELECT * FROM exam_attempts WHERE id = ?");
        $stmt->bind_param("i", $attemptId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Get user answers
        $stmt = $this->conn->prepare("
            SELECT ua.*, q.question_text, q.question_type
            FROM user_answers ua
            JOIN questions q ON ua.question_id = q.id
            WHERE ua.attempt_id = ?
        ");
        $stmt->bind_param("i", $attemptId);
        $stmt->execute();
        $answersResult = $stmt->get_result();
        
        $answers = [];
        while ($row = $answersResult->fetch_assoc()) {
            $answers[] = $row;
        }
        $stmt->close();
        
        $result['answers'] = $answers;
        return $result;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
