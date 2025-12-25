<?php
require_once 'config.php';

class Dashboard {
    private $conn;
    private $userId;
    private $userRole;
    
    public function __construct($userId) {
        $this->conn = getDBConnection();
        $this->userId = $userId;
        $this->userRole = $this->getUserRole();
    }
    
    /**
     * Get user role from database
     */
    private function getUserRole() {
        $stmt = $this->conn->prepare("SELECT user_role FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user['user_role'] ?? 'learner';
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin() {
        return $this->userRole === 'admin';
    }
    
    /**
     * Get current user information
     */
    public function getCurrentUser() {
        $stmt = $this->conn->prepare("SELECT id, full_name, email, user_role, created_at, last_login FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user;
    }
    
    /**
     * Get all users (admin only)
     */
    public function getAllUsers() {
        if (!$this->isAdmin()) {
            return [];
        }
        
        $stmt = $this->conn->prepare("SELECT id, full_name, email, user_role, created_at, last_login, is_active FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        
        return $users;
    }
    
    /**
     * Get single user by ID
     */
    public function getUserById($id) {
        // Admin can view any user, learner can only view themselves
        if (!$this->isAdmin() && $id != $this->userId) {
            return null;
        }
        
        $stmt = $this->conn->prepare("SELECT id, full_name, email, user_role, created_at, last_login, is_active FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user;
    }
    
    /**
     * Update user (admin only)
     */
    public function updateUser($id, $fullName, $email, $userRole) {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized access'];
        }
        
        // Check if email already exists for another user
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Email already exists'];
        }
        $stmt->close();
        
        // Update user
        $stmt = $this->conn->prepare("UPDATE users SET full_name = ?, email = ?, user_role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $fullName, $email, $userRole, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'User updated successfully'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Update failed'];
        }
    }
    
    /**
     * Delete user (admin only)
     */
    public function deleteUser($id) {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized access'];
        }
        
        // Prevent deleting own account
        if ($id == $this->userId) {
            return ['success' => false, 'message' => 'Cannot delete your own account'];
        }
        
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'User deleted successfully'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Delete failed'];
        }
    }
    
    /**
     * Toggle user active status (admin only)
     */
    public function toggleUserStatus($id) {
        if (!$this->isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized access'];
        }
        
        // Prevent disabling own account
        if ($id == $this->userId) {
            return ['success' => false, 'message' => 'Cannot disable your own account'];
        }
        
        $stmt = $this->conn->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'User status updated'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Status update failed'];
        }
    }
    
    /**
     * Get dashboard statistics
     */
    public function getStatistics() {
        $stats = [];
        
        if ($this->isAdmin()) {
            // Total users
            $result = $this->conn->query("SELECT COUNT(*) as count FROM users");
            $stats['total_users'] = $result->fetch_assoc()['count'];
            
            // Active users
            $result = $this->conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
            $stats['active_users'] = $result->fetch_assoc()['count'];
            
            // Admin users
            $result = $this->conn->query("SELECT COUNT(*) as count FROM users WHERE user_role = 'admin'");
            $stats['admin_users'] = $result->fetch_assoc()['count'];
            
            // Learner users
            $result = $this->conn->query("SELECT COUNT(*) as count FROM users WHERE user_role = 'learner'");
            $stats['learner_users'] = $result->fetch_assoc()['count'];
        } else {
            // For learner, just show their own info
            $user = $this->getCurrentUser();
            $stats['user_name'] = $user['full_name'];
            $stats['user_email'] = $user['email'];
            $stats['member_since'] = $user['created_at'];
        }
        
        return $stats;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
