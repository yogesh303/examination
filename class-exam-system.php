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
    
    // ==================== ASSIGNMENTS ====================
    
    public function assignExamToUsers($examId, $userIds) {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $stmt = $this->conn->prepare("INSERT IGNORE INTO exam_assignments (exam_id, user_id, assigned_by) VALUES (?, ?, ?)");
        
        $successCount = 0;
        foreach ($userIds as $userId) {
            $stmt->bind_param("iii", $examId, $userId, $this->userId);
            if ($stmt->execute()) {
                $successCount++;
            }
        }
        $stmt->close();
        
        return ['success' => true, 'message' => "Exam assigned to {$successCount} user(s)"];
    }
    
    public function getAllAssignments() {
        if (!$this->isAdmin()) {
            return [];
        }
        
        $stmt = $this->conn->prepare("
            SELECT 
                ea.*,
                u.full_name as user_name,
                u.email as user_email,
                e.exam_name,
                e.total_marks,
                eat.marks_obtained,
                eat.percentage
            FROM exam_assignments ea
            JOIN users u ON ea.user_id = u.id
            JOIN exams e ON ea.exam_id = e.id
            LEFT JOIN exam_attempts eat ON ea.attempt_id = eat.id
            ORDER BY ea.assigned_at DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $assignments = [];
        while ($row = $result->fetch_assoc()) {
            $assignments[] = $row;
        }
        $stmt->close();
        return $assignments;
    }
    
    public function getMyAssignedExams() {
        $stmt = $this->conn->prepare("
            SELECT 
                ea.*,
                e.exam_name,
                e.duration_minutes,
                e.total_marks,
                e.passing_marks,
                e.instructions,
                s.subject_name,
                (SELECT COUNT(*) FROM exam_questions WHERE exam_id = e.id) as question_count
            FROM exam_assignments ea
            JOIN exams e ON ea.exam_id = e.id
            JOIN subjects s ON e.subject_id = s.id
            WHERE ea.user_id = ?
            ORDER BY ea.assigned_at DESC
        ");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $exams = [];
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }
        $stmt->close();
        return $exams;
    }
    
    public function startExam($examId) {
        // Check if exam is assigned to user
        $stmt = $this->conn->prepare("SELECT id, status FROM exam_assignments WHERE exam_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $examId, $this->userId);
        $stmt->execute();
        $assignment = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$assignment) {
            return ['success' => false, 'message' => 'Exam not assigned to you'];
        }
        
        if ($assignment['status'] === 'completed') {
            return ['success' => false, 'message' => 'Exam already completed'];
        }
        
        // Get exam details with subject name
        $stmt = $this->conn->prepare("
            SELECT e.*, s.subject_name, u.full_name as user_name
            FROM exams e
            JOIN subjects s ON e.subject_id = s.id
            JOIN users u ON u.id = ?
            WHERE e.id = ?
        ");
        $stmt->bind_param("ii", $this->userId, $examId);
        $stmt->execute();
        $exam = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$exam) {
            return ['success' => false, 'message' => 'Exam not found'];
        }
        
        // Create exam attempt
        $stmt = $this->conn->prepare("
            INSERT INTO exam_attempts 
            (user_id, user_name, exam_id, exam_name, subject_name, total_marks, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'started')
        ");
        $stmt->bind_param("isissd", 
            $this->userId, 
            $exam['user_name'], 
            $examId, 
            $exam['exam_name'], 
            $exam['subject_name'],
            $exam['total_marks']
        );
        $stmt->execute();
        $attemptId = $stmt->insert_id;
        $stmt->close();
        
        // Update assignment status
        $stmt = $this->conn->prepare("UPDATE exam_assignments SET status = 'in_progress', attempt_id = ? WHERE exam_id = ? AND user_id = ?");
        $stmt->bind_param("iii", $attemptId, $examId, $this->userId);
        $stmt->execute();
        $stmt->close();
        
        // Get questions (randomized)
        $stmt = $this->conn->prepare("
            SELECT q.*, 
                   (SELECT JSON_ARRAYAGG(
                       JSON_OBJECT('id', id, 'text', option_text, 'order', option_order)
                   ) FROM question_options WHERE question_id = q.id ORDER BY RAND()) as options
            FROM exam_questions eq
            JOIN questions q ON eq.question_id = q.id
            WHERE eq.exam_id = ?
            ORDER BY RAND()
        ");
        $stmt->bind_param("i", $examId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $row['options'] = json_decode($row['options'], true);
            $questions[] = $row;
        }
        $stmt->close();
        
        return [
            'success' => true,
            'attempt_id' => $attemptId,
            'exam' => $exam,
            'questions' => $questions
        ];
    }
    
    public function submitExam($attemptId, $answers) {
        // Verify attempt belongs to user
        $stmt = $this->conn->prepare("SELECT user_id, exam_id FROM exam_attempts WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $attemptId, $this->userId);
        $stmt->execute();
        $attempt = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$attempt) {
            return ['success' => false, 'message' => 'Invalid attempt'];
        }
        
        $totalQuestions = 0;
        $correctAnswers = 0;
        $wrongAnswers = 0;
        $marksObtained = 0;
        
        // Process each answer
        foreach ($answers as $answer) {
            $questionId = $answer['question_id'];
            $selectedOptions = $answer['selected_options']; // array of option IDs
            
            // Get question details
            $stmt = $this->conn->prepare("SELECT question_type, marks FROM questions WHERE id = ?");
            $stmt->bind_param("i", $questionId);
            $stmt->execute();
            $question = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$question) continue;
            
            $totalQuestions++;
            
            // Get correct options
            $stmt = $this->conn->prepare("SELECT id FROM question_options WHERE question_id = ? AND is_correct = 1");
            $stmt->bind_param("i", $questionId);
            $stmt->execute();
            $result = $stmt->get_result();
            $correctOptionIds = [];
            while ($row = $result->fetch_assoc()) {
                $correctOptionIds[] = $row['id'];
            }
            $stmt->close();
            
            // Check if answer is correct
            $isCorrect = false;
            sort($selectedOptions);
            sort($correctOptionIds);
            
            if ($selectedOptions == $correctOptionIds) {
                $isCorrect = true;
                $correctAnswers++;
                $marksObtained += $question['marks'];
            } else {
                $wrongAnswers++;
            }
            
            // Store answer
            $selectedOptionsJson = json_encode($selectedOptions);
            $stmt = $this->conn->prepare("
                INSERT INTO user_answers (attempt_id, question_id, selected_options, is_correct, marks_obtained)
                VALUES (?, ?, ?, ?, ?)
            ");
            $marks = $isCorrect ? $question['marks'] : 0;
            $stmt->bind_param("iisid", $attemptId, $questionId, $selectedOptionsJson, $isCorrect, $marks);
            $stmt->execute();
            $stmt->close();
        }
        
        // Calculate percentage
        $stmt = $this->conn->prepare("SELECT total_marks FROM exam_attempts WHERE id = ?");
        $stmt->bind_param("i", $attemptId);
        $stmt->execute();
        $totalMarks = $stmt->get_result()->fetch_assoc()['total_marks'];
        $stmt->close();
        
        $percentage = $totalMarks > 0 ? ($marksObtained / $totalMarks) * 100 : 0;
        
        // Update attempt
        $stmt = $this->conn->prepare("
            UPDATE exam_attempts 
            SET end_time = NOW(),
                total_questions = ?,
                attempted_questions = ?,
                correct_answers = ?,
                wrong_answers = ?,
                marks_obtained = ?,
                percentage = ?,
                status = 'completed'
            WHERE id = ?
        ");
        $stmt->bind_param("iiiiddi", $totalQuestions, $totalQuestions, $correctAnswers, $wrongAnswers, $marksObtained, $percentage, $attemptId);
        $stmt->execute();
        $stmt->close();
        
        // Update assignment
        $stmt = $this->conn->prepare("UPDATE exam_assignments SET status = 'completed' WHERE attempt_id = ?");
        $stmt->bind_param("i", $attemptId);
        $stmt->execute();
        $stmt->close();
        
        return [
            'success' => true,
            'result' => [
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'wrong_answers' => $wrongAnswers,
                'marks_obtained' => $marksObtained,
                'total_marks' => $totalMarks,
                'percentage' => round($percentage, 2)
            ]
        ];
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>