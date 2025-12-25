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
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');
$rememberMe = $input['rememberMe'] ?? false;

$errors = [];

// Validate email
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// Validate password
if (empty($password)) {
    $errors[] = 'Password is required';
}

// Return validation errors if any
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Get database connection
$conn = getDBConnection();

// Get user IP address
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Check for too many failed login attempts (rate limiting)
$stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE email = ? AND success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row['attempts'] >= 9) {
    echo json_encode(['success' => false, 'message' => 'Too many failed login attempts. Please try again in 15 minutes.']);
    $conn->close();
    exit;
}

// Find user by email
$stmt = $conn->prepare("SELECT id, full_name, email, password, is_active FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User not found - log failed attempt
    $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $email, $ipAddress);
    $stmt->execute();
    $stmt->close();
    
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if account is active
if (!$user['is_active']) {
    echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact support.']);
    $conn->close();
    exit;
}

// Verify password
if (!password_verify($password, $user['password'])) {
    // Log failed attempt
    $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $email, $ipAddress);
    $stmt->execute();
    $stmt->close();
    
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

// Login successful - log successful attempt
$stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 1)");
$stmt->bind_param("ss", $email, $ipAddress);
$stmt->execute();
$stmt->close();

// Update last login time
$stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$stmt->close();

// Set session variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['logged_in'] = true;

// Handle "Remember Me" functionality
if ($rememberMe) {
    // Create a secure token
    $token = bin2hex(random_bytes(32));
    
    // Store token in session for this example
    // In production, you'd want to store this in a separate table
    $_SESSION['remember_token'] = $token;
    
    // Set cookie for 30 days
    setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
}

$conn->close();

echo json_encode([
    'success' => true,
    'message' => 'Login successful!',
    'user' => [
        'id' => $user['id'],
        'name' => $user['full_name'],
        'email' => $user['email']
    ]
]);
?>
