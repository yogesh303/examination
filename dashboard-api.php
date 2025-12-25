<?php
require_once 'config.php';
require_once 'class-dashboard.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$dashboard = new Dashboard($_SESSION['user_id']);

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_users':
        if (!$dashboard->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        $users = $dashboard->getAllUsers();
        echo json_encode(['success' => true, 'users' => $users]);
        break;
        
    case 'get_user':
        $userId = $_GET['id'] ?? 0;
        $user = $dashboard->getUserById($userId);
        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        break;
        
    case 'update_user':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $dashboard->updateUser(
            $input['id'],
            $input['full_name'],
            $input['email'],
            $input['user_role']
        );
        echo json_encode($result);
        break;
        
    case 'delete_user':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $dashboard->deleteUser($input['id']);
        echo json_encode($result);
        break;
        
    case 'toggle_status':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $dashboard->toggleUserStatus($input['id']);
        echo json_encode($result);
        break;
        
    case 'get_statistics':
        $stats = $dashboard->getStatistics();
        echo json_encode(['success' => true, 'statistics' => $stats]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
