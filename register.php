<?php
require_once 'config.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirmPassword = $input['confirmPassword'] ?? '';

$errors = [];

// Validate name
if (empty($name)) {
    $errors[] = 'Name is required';
} elseif (strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters long';
}

// Validate email
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// Validate password
if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'Password must contain at least one uppercase letter';
} elseif (!preg_match('/[a-z]/', $password)) {
    $errors[] = 'Password must contain at least one lowercase letter';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors[] = 'Password must contain at least one number';
}

// Validate password confirmation
if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match';
}

// Return validation errors if any
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Get database connection
$conn = getDBConnection();

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}
$stmt->close();

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Set default user role as learner - EXPLICITLY SET
$userRole = 'learner';

// DEBUG: Log what we're about to insert
error_log("DEBUG - Inserting user: name=$name, email=$email, userRole=$userRole");

// Insert new user with user_role
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, user_role) VALUES (?, ?, ?, ?)");

// Verify the statement was prepared correctly
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    $conn->close();
    exit;
}

// Bind parameters - 4 strings: name, email, password, user_role
$stmt->bind_param("ssss", $name, $email, $hashedPassword, $userRole);

// Execute and check for errors
if ($stmt->execute()) {
    $userId = $stmt->insert_id;
    
    // Verify the user_role was actually inserted
    $checkStmt = $conn->prepare("SELECT user_role FROM users WHERE id = ?");
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $userData = $checkResult->fetch_assoc();
    $checkStmt->close();
    
    $actualRole = $userData['user_role'];
    error_log("DEBUG - User created with role: $actualRole");
    
    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['logged_in'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful!',
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => $actualRole  // Include role in response
        ],
        'debug' => [
            'role_set' => $userRole,
            'role_in_db' => $actualRole
        ]
    ]);
} else {
    error_log("DEBUG - Insert failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>