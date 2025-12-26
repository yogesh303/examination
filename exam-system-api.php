<?php
require_once 'config.php';
require_once 'class-exam-system.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$examSystem = new ExamSystem($_SESSION['user_id']);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    // ==================== SUBJECTS ====================
    case 'get_subjects':
        $subjects = $examSystem->getAllSubjects();
        echo json_encode(['success' => true, 'subjects' => $subjects]);
        break;
        
    case 'add_subject':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $examSystem->addSubject(
            $input['name'],
            $input['code'],
            $input['description'] ?? ''
        );
        echo json_encode($result);
        break;
    
    // ==================== QUESTIONS ====================
    case 'add_question':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $examSystem->addQuestion(
            $input['subject_id'],
            $input['question_text'],
            $input['question_type'],
            $input['marks'] ?? 1,
            $input['options']
        );
        echo json_encode($result);
        break;
        
    case 'get_questions':
        $subjectId = $_GET['subject_id'] ?? 0;
        $questions = $examSystem->getQuestionsBySubject($subjectId);
        echo json_encode(['success' => true, 'questions' => $questions]);
        break;
        
    case 'get_question':
        $questionId = $_GET['id'] ?? 0;
        $question = $examSystem->getQuestionWithOptions($questionId);
        if ($question) {
            echo json_encode(['success' => true, 'question' => $question]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Question not found']);
        }
        break;
        
    case 'delete_question':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $examSystem->deleteQuestion($input['id']);
        echo json_encode($result);
        break;
    
    // ==================== EXAMS ====================
    case 'create_exam':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $examSystem->createExam(
            $input['exam_name'],
            $input['subject_id'],
            $input['duration'],
            $input['passing_marks'],
            $input['instructions'] ?? ''
        );
        echo json_encode($result);
        break;
        
    case 'assign_questions':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $examSystem->assignQuestionsToExam(
            $input['exam_id'],
            $input['question_ids']
        );
        echo json_encode($result);
        break;
        
    case 'get_exams':
        $exams = $examSystem->getAllExams();
        echo json_encode(['success' => true, 'exams' => $exams]);
        break;
    
    // ==================== RESULTS ====================
    case 'get_results':
        $results = $examSystem->getAllResults();
        echo json_encode(['success' => true, 'results' => $results]);
        break;
        
    case 'get_result_details':
        $attemptId = $_GET['id'] ?? 0;
        $result = $examSystem->getResultDetails($attemptId);
        if ($result) {
            echo json_encode(['success' => true, 'result' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Result not found']);
        }
        break;
    
    // ==================== ASSIGNMENTS ====================
    case 'assign_exam':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $examSystem->assignExamToUsers($input['exam_id'], $input['user_ids']);
        echo json_encode($result);
        break;
        
    case 'get_assignments':
        $assignments = $examSystem->getAllAssignments();
        echo json_encode(['success' => true, 'assignments' => $assignments]);
        break;
        
    case 'get_my_exams':
        $myExams = $examSystem->getMyAssignedExams();
        echo json_encode(['success' => true, 'exams' => $myExams]);
        break;
        
    case 'start_exam':
        $examId = $_POST['exam_id'] ?? 0;
        $result = $examSystem->startExam($examId);
        echo json_encode($result);
        break;
        
    case 'submit_exam':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $examSystem->submitExam($input['attempt_id'], $input['answers']);
        echo json_encode($result);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
