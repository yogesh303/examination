<?php
require_once 'config.php';
require_once 'class-dashboard.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$dashboard = new Dashboard($_SESSION['user_id']);
$currentUser = $dashboard->getCurrentUser();
$isAdmin = $dashboard->isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($currentUser['full_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
            min-height: 100vh;
        }

        /* Header Styles */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #0a0a0a 0%, #ff3366 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: #0a0a0a;
            font-size: 15px;
        }

        .user-role {
            font-size: 13px;
            color: #666;
            text-transform: capitalize;
        }

        .user-role.admin {
            color: #ff3366;
            font-weight: 500;
        }

        .logout-btn {
            padding: 10px 24px;
            background: linear-gradient(135deg, #0a0a0a 0%, #2a2a2a 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Welcome Section */
        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .welcome-title {
            font-family: 'Syne', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #0a0a0a;
            margin-bottom: 8px;
        }

        .welcome-subtitle {
            color: #666;
            font-size: 15px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 8px;
            background: white;
            padding: 8px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .tab {
            padding: 12px 24px;
            background: transparent;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .tab:hover {
            background: #f8f8f8;
            color: #0a0a0a;
        }

        .tab.active {
            background: linear-gradient(135deg, #0a0a0a 0%, #2a2a2a 100%);
            color: white;
        }

        /* Tab Content */
        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 20px;
            font-weight: 600;
            color: #0a0a0a;
            margin-bottom: 20px;
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8f8f8 0%, #ffffff 100%);
            padding: 25px;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            border-color: #00d4aa;
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 212, 170, 0.1);
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #0a0a0a;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f8f8;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #0a0a0a;
            font-size: 14px;
            border-bottom: 2px solid #e0e0e0;
        }

        td {
            padding: 15px;
            color: #666;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:hover {
            background: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge.admin {
            background: #ffe0e6;
            color: #ff3366;
        }

        .badge.learner {
            background: #e0f7f4;
            color: #00d4aa;
        }

        .badge.active {
            background: #e0f7f4;
            color: #00d4aa;
        }

        .badge.inactive {
            background: #f0f0f0;
            color: #999;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-edit {
            background: #e3f2fd;
            color: #1976d2;
        }

        .btn-edit:hover {
            background: #1976d2;
            color: white;
        }

        .btn-delete {
            background: #ffebee;
            color: #d32f2f;
        }

        .btn-delete:hover {
            background: #d32f2f;
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .empty-state-text {
            font-size: 16px;
        }

        /* Loading Spinner */
        .loading {
            text-align: center;
            padding: 40px;
        }

        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f0f0f0;
            border-top-color: #00d4aa;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-title {
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 600;
            color: #0a0a0a;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #0a0a0a;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00d4aa;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0a0a0a 0%, #2a2a2a 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #666;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        /* Alert */
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #e0f7f4;
            color: #00d4aa;
            border: 1px solid #00d4aa;
        }

        .alert-error {
            background: #ffebee;
            color: #d32f2f;
            border: 1px solid #d32f2f;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .user-info {
                flex-direction: column;
            }

            .user-details {
                text-align: center;
            }

            .tabs {
                overflow-x: auto;
            }

            .table-container {
                overflow-x: auto;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        /* Sub-Tabs */
        .sub-tabs {
            display: flex;
            gap: 8px;
            background: #f8f8f8;
            padding: 6px;
            border-radius: 10px;
        }

        .sub-tab {
            padding: 10px 20px;
            background: transparent;
            border: none;
            border-radius: 6px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sub-tab:hover {
            background: white;
            color: #0a0a0a;
        }

        .sub-tab.active {
            background: white;
            color: #0a0a0a;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .sub-tab-content {
            display: none;
        }

        .sub-tab-content.active {
            display: block;
        }

        /* Form Styles */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            resize: vertical;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            outline: none;
            border-color: #00d4aa;
        }

        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        select:focus {
            outline: none;
            border-color: #00d4aa;
        }

        /* Options Container */
        .option-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding: 10px;
            background: #f8f8f8;
            border-radius: 8px;
        }

        .option-correct {
            width: 20px;
            height: 20px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .option-text {
            flex: 1;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }

        .option-text:focus {
            outline: none;
            border-color: #00d4aa;
        }

        .btn-remove-option {
            padding: 6px 10px;
            background: #ffebee;
            color: #d32f2f;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.2s ease;
        }

        .btn-remove-option:hover {
            background: #d32f2f;
            color: white;
        }

        /* Questions Checklist */
        .questions-checklist {
            max-height: 400px;
            overflow-y: auto;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
        }

        .question-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px;
            background: #f8f8f8;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .question-item:hover {
            background: #e3f2fd;
        }

        .question-item input[type="checkbox"] {
            margin-top: 4px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .question-item-content {
            flex: 1;
        }

        .question-item-text {
            font-size: 14px;
            color: #333;
            margin-bottom: 4px;
        }

        .question-item-meta {
            font-size: 12px;
            color: #666;
        }

        .question-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            margin-right: 8px;
        }

        .question-badge.radio {
            background: #e3f2fd;
            color: #1976d2;
        }

        .question-badge.checkbox {
            background: #f3e5f5;
            color: #7b1fa2;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">Dashboard</div>
            <div class="user-info">
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($currentUser['full_name']); ?></div>
                    <div class="user-role <?php echo $currentUser['user_role']; ?>">
                        <?php echo ucfirst($currentUser['user_role']); ?>
                    </div>
                </div>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-title">
                Welcome<?php echo $isAdmin ? ', Admin' : ''; ?>! 👋
            </div>
            <div class="welcome-subtitle">
                <?php echo $isAdmin ? 'Manage your users and system' : 'View your profile and activities'; ?>
            </div>
        </div>

        <!-- Alert -->
        <div id="alert" class="alert"></div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchTab('users')">Users</button>
            <button class="tab" onclick="window.location.href='exams.php'">Exams</button>
            <button class="tab" onclick="switchTab('results')">Results</button>
        </div>

        <!-- Tab Contents -->
        
        <!-- Users Tab -->
        <div id="users-tab" class="tab-content active">
            <?php if ($isAdmin): ?>
                <!-- Admin View: Statistics -->
                <div class="stats-grid" id="statistics">
                    <div class="loading">
                        <div class="spinner"></div>
                    </div>
                </div>

                <!-- Admin View: All Users Table -->
                <div class="card">
                    <div class="card-title">All Users</div>
                    <div class="table-container">
                        <table id="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-tbody">
                                <tr>
                                    <td colspan="7">
                                        <div class="loading">
                                            <div class="spinner"></div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <!-- Learner View: Own Profile -->
                <div class="card">
                    <div class="card-title">My Profile</div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">Name</div>
                            <div class="stat-value" style="font-size: 20px;"><?php echo htmlspecialchars($currentUser['full_name']); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Email</div>
                            <div class="stat-value" style="font-size: 16px;"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Role</div>
                            <div class="stat-value" style="font-size: 20px;"><?php echo ucfirst($currentUser['user_role']); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Member Since</div>
                            <div class="stat-value" style="font-size: 16px;"><?php echo date('M Y', strtotime($currentUser['created_at'])); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Results Tab -->
        <div id="results-tab" class="tab-content">
            <div class="card">
                <div class="card-title">Exam Results</div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <?php if ($isAdmin): ?>
                                <th>Student Name</th>
                                <?php endif; ?>
                                <th>Exam Name</th>
                                <th>Subject</th>
                                <th>Total Questions</th>
                                <th>Correct</th>
                                <th>Wrong</th>
                                <th>Score</th>
                                <th>Percentage</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="results-tbody">
                            <tr><td colspan="<?php echo $isAdmin ? '10' : '9'; ?>"><div class="loading"><div class="spinner"></div></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <div class="modal-title">Edit User</div>
            <form id="edit-form">
                <input type="hidden" id="edit-user-id">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="edit-full-name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="edit-email" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="edit-role" required>
                        <option value="learner">Learner</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;

        // Switch tabs
        function switchTab(tabName) {
            // Remove active class from all tabs and contents
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to selected tab
            event.target.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        }

        // Show alert
        function showAlert(message, type = 'success') {
            const alert = document.getElementById('alert');
            alert.className = `alert alert-${type} show`;
            alert.textContent = message;
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }

        // Logout
        async function logout() {
            if (confirm('Are you sure you want to logout?')) {
                try {
                    const response = await fetch('logout.php');
                    const data = await response.json();
                    
                    if (data.success) {
                        window.location.href = 'index.php';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.location.href = 'index.php';
                }
            }
        }

        // Load statistics (admin only)
        async function loadStatistics() {
            if (!isAdmin) return;
            
            try {
                const response = await fetch('dashboard-api.php?action=get_statistics');
                const data = await response.json();
                
                if (data.success) {
                    const stats = data.statistics;
                    document.getElementById('statistics').innerHTML = `
                        <div class="stat-card">
                            <div class="stat-label">Total Users</div>
                            <div class="stat-value">${stats.total_users}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Active Users</div>
                            <div class="stat-value">${stats.active_users}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Admins</div>
                            <div class="stat-value">${stats.admin_users}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Learners</div>
                            <div class="stat-value">${stats.learner_users}</div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        // Load users table (admin only)
        async function loadUsers() {
            if (!isAdmin) return;
            
            try {
                const response = await fetch('dashboard-api.php?action=get_users');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('users-tbody');
                    
                    if (data.users.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-state-text">No users found</div></div></td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = data.users.map(user => `
                        <tr>
                            <td>${user.id}</td>
                            <td>${escapeHtml(user.full_name)}</td>
                            <td>${escapeHtml(user.email)}</td>
                            <td><span class="badge ${user.user_role}">${user.user_role}</span></td>
                            <td><span class="badge ${user.is_active ? 'active' : 'inactive'}">${user.is_active ? 'Active' : 'Inactive'}</span></td>
                            <td>${formatDate(user.created_at)}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-edit" onclick="editUser(${user.id})">Edit</button>
                                    <button class="btn btn-delete" onclick="deleteUser(${user.id}, '${escapeHtml(user.full_name)}')">Delete</button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        // Edit user
        async function editUser(userId) {
            try {
                const response = await fetch(`dashboard-api.php?action=get_user&id=${userId}`);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('edit-user-id').value = data.user.id;
                    document.getElementById('edit-full-name').value = data.user.full_name;
                    document.getElementById('edit-email').value = data.user.email;
                    document.getElementById('edit-role').value = data.user.user_role;
                    document.getElementById('edit-modal').classList.add('show');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Close edit modal
        function closeEditModal() {
            document.getElementById('edit-modal').classList.remove('show');
        }

        // Handle edit form submit
        document.getElementById('edit-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('edit-user-id').value;
            const fullName = document.getElementById('edit-full-name').value;
            const email = document.getElementById('edit-email').value;
            const role = document.getElementById('edit-role').value;
            
            try {
                const response = await fetch('dashboard-api.php?action=update_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: userId,
                        full_name: fullName,
                        email: email,
                        user_role: role
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    closeEditModal();
                    loadUsers();
                    loadStatistics();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            }
        });

        // Delete user
        async function deleteUser(userId, userName) {
            if (!confirm(`Are you sure you want to delete ${userName}?`)) return;
            
            try {
                const response = await fetch('dashboard-api.php?action=delete_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    loadUsers();
                    loadStatistics();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            }
        }

        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        // Close modal on outside click
        document.getElementById('edit-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Initialize
        window.addEventListener('load', function() {
            if (isAdmin) {
                loadStatistics();
                loadUsers();
                loadSubjects();
                loadExams();
            }
            loadResults();
        });

        // ==================== RESULTS ====================
        async function loadResults() {
            try {
                const response = await fetch('exam-api.php?action=get_results');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('results-tbody');
                    const colSpan = isAdmin ? 10 : 9;
                    
                    if (data.results.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="${colSpan}"><div class="empty-state-text">No results found</div></td></tr>`;
                        return;
                    }
                    
                    tbody.innerHTML = data.results.map(result => `
                        <tr>
                            ${isAdmin ? `<td>${escapeHtml(result.user_name)}</td>` : ''}
                            <td>${escapeHtml(result.exam_name)}</td>
                            <td>${escapeHtml(result.subject_name)}</td>
                            <td>${result.total_questions}</td>
                            <td style="color: #00d4aa; font-weight: 600;">${result.correct_answers}</td>
                            <td style="color: #ff3366; font-weight: 600;">${result.wrong_answers}</td>
                            <td>${result.marks_obtained}/${result.total_marks}</td>
                            <td>
                                <span class="badge ${result.percentage >= 40 ? 'active' : 'inactive'}">
                                    ${result.percentage}%
                                </span>
                            </td>
                            <td>${formatDate(result.start_time)}</td>
                            <td>
                                <button class="btn btn-edit" onclick="viewResultDetail(${result.attempt_id})">View Details</button>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading results:', error);
            }
        }

        function viewResultDetail(attemptId) {
            // TODO: Show detailed result in modal
            showAlert('View details feature coming soon', 'success');
        }

        // ==================== SUB-TAB MANAGEMENT ====================
        function switchSubTab(tabName) {
            document.querySelectorAll('.sub-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.sub-tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tabName + '-subtab').classList.add('active');
        }

        // ==================== SUBJECTS ====================
        async function loadSubjects() {
            try {
                const response = await fetch('exam-api.php?action=get_subjects');
                const data = await response.json();
                
                if (data.success) {
                    const selects = ['question-subject', 'assign-subject', 'exam-subject'];
                    selects.forEach(selectId => {
                        const select = document.getElementById(selectId);
                        if (select) {
                            select.innerHTML = '<option value="">Select Subject</option>' +
                                data.subjects.map(s => `<option value="${s.id}">${s.subject_name}</option>`).join('');
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading subjects:', error);
            }
        }

        // ==================== QUESTION MANAGEMENT ====================
        let optionCount = 4;

        function updateQuestionType() {
            const questionType = document.getElementById('question-type').value;
            const checkboxes = document.querySelectorAll('.option-correct');
            
            if (questionType === 'radio') {
                checkboxes.forEach(checkbox => {
                    checkbox.type = 'radio';
                    checkbox.name = 'correct-answer';
                });
            } else {
                checkboxes.forEach(checkbox => {
                    checkbox.type = 'checkbox';
                    checkbox.name = '';
                });
            }
        }

        function addMoreOptions() {
            const container = document.getElementById('options-container');
            const newOption = document.createElement('div');
            newOption.className = 'option-row';
            newOption.innerHTML = `
                <input type="checkbox" class="option-correct" data-index="${optionCount}">
                <input type="text" class="option-text" placeholder="Option ${optionCount + 1}" required>
                <button type="button" class="btn-remove-option" onclick="removeOption(${optionCount})">✕</button>
            `;
            container.appendChild(newOption);
            optionCount++;
            updateQuestionType();
        }

        function removeOption(index) {
            const rows = document.querySelectorAll('.option-row');
            if (rows.length > 2) {
                rows[index].remove();
            } else {
                showAlert('At least 2 options are required', 'error');
            }
        }

        function resetQuestionForm() {
            document.getElementById('add-question-form').reset();
            const container = document.getElementById('options-container');
            container.innerHTML = '';
            optionCount = 0;
            for (let i = 0; i < 4; i++) {
                addMoreOptions();
            }
        }

        // Handle add question form submission
        document.getElementById('add-question-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const subjectId = document.getElementById('question-subject').value;
            const questionText = document.getElementById('question-text').value;
            const questionType = document.getElementById('question-type').value;
            const marks = document.getElementById('question-marks').value;
            
            // Collect options
            const optionRows = document.querySelectorAll('.option-row');
            const options = [];
            let hasCorrect = false;
            
            optionRows.forEach(row => {
                const text = row.querySelector('.option-text').value.trim();
                const isCorrect = row.querySelector('.option-correct').checked;
                
                if (text) {
                    options.push({
                        text: text,
                        is_correct: isCorrect
                    });
                    if (isCorrect) hasCorrect = true;
                }
            });
            
            if (!hasCorrect) {
                showAlert('Please mark at least one correct answer', 'error');
                return;
            }
            
            if (options.length < 2) {
                showAlert('Please provide at least 2 options', 'error');
                return;
            }
            
            try {
                const response = await fetch('exam-api.php?action=add_question', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        subject_id: subjectId,
                        question_text: questionText,
                        question_type: questionType,
                        options: options,
                        marks: marks
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Question added successfully!', 'success');
                    resetQuestionForm();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            }
        });

        // ==================== ASSIGN QUESTIONS ====================
        async function loadExamQuestions() {
            const examId = document.getElementById('assign-exam').value;
            if (!examId) return;
            
            // Show assigned questions card
            document.getElementById('assigned-questions-card').style.display = 'block';
            // Load currently assigned questions here if needed
        }

        async function loadSubjectQuestions() {
            const subjectId = document.getElementById('assign-subject').value;
            if (!subjectId) {
                document.getElementById('questions-list-container').style.display = 'none';
                return;
            }
            
            document.getElementById('questions-list-container').style.display = 'block';
            const container = document.getElementById('available-questions');
            container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
            
            try {
                const response = await fetch(`exam-api.php?action=get_questions_by_subject&subject_id=${subjectId}`);
                const data = await response.json();
                
                if (data.success) {
                    if (data.questions.length === 0) {
                        container.innerHTML = '<div class="empty-state-text">No questions found for this subject</div>';
                        return;
                    }
                    
                    container.innerHTML = data.questions.map(q => `
                        <div class="question-item">
                            <input type="checkbox" value="${q.id}" class="question-checkbox">
                            <div class="question-item-content">
                                <div class="question-item-text">${escapeHtml(q.question_text)}</div>
                                <div class="question-item-meta">
                                    <span class="question-badge ${q.question_type}">${q.question_type}</span>
                                    <span>${q.marks} mark(s)</span>
                                    <span>•</span>
                                    <span>${q.option_count} options</span>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error:', error);
                container.innerHTML = '<div class="empty-state-text">Error loading questions</div>';
            }
        }

        // Handle assign questions form
        document.getElementById('assign-questions-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const examId = document.getElementById('assign-exam').value;
            const checkboxes = document.querySelectorAll('.question-checkbox:checked');
            
            if (checkboxes.length === 0) {
                showAlert('Please select at least one question', 'error');
                return;
            }
            
            const questionIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
            
            try {
                const response = await fetch('exam-api.php?action=assign_questions', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        exam_id: examId,
                        question_ids: questionIds
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(`${questionIds.length} questions assigned successfully!`, 'success');
                    loadExams();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            }
        });

        // ==================== EXAM MANAGEMENT ====================
        async function loadExams() {
            try {
                const response = await fetch('exam-api.php?action=get_exams');
                const data = await response.json();
                
                if (data.success) {
                    // Populate exam select dropdown
                    const examSelect = document.getElementById('assign-exam');
                    if (examSelect) {
                        examSelect.innerHTML = '<option value="">Select Exam</option>' +
                            data.exams.map(e => `<option value="${e.id}">${e.exam_name} (${e.subject_name})</option>`).join('');
                    }
                    
                    // Populate exams table
                    const tbody = document.getElementById('exams-tbody');
                    if (tbody) {
                        if (data.exams.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state-text">No exams found</div></td></tr>';
                            return;
                        }
                        
                        tbody.innerHTML = data.exams.map(exam => `
                            <tr>
                                <td>${escapeHtml(exam.exam_name)}</td>
                                <td>${escapeHtml(exam.subject_name)}</td>
                                <td>${exam.question_count}</td>
                                <td>${exam.duration_minutes} min</td>
                                <td>${exam.total_marks || 0}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-edit" onclick="viewExam(${exam.id})">View</button>
                                    </div>
                                </td>
                            </tr>
                        `).join('');
                    }
                }
            } catch (error) {
                console.error('Error loading exams:', error);
            }
        }

        function viewExam(examId) {
            // TODO: Implement view exam details
            showAlert('View exam feature coming soon', 'success');
        }

        // Handle create exam form
        document.getElementById('create-exam-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const examName = document.getElementById('exam-name').value;
            const subjectId = document.getElementById('exam-subject').value;
            const duration = document.getElementById('exam-duration').value;
            const passingMarks = document.getElementById('exam-passing-marks').value;
            const instructions = document.getElementById('exam-instructions').value;
            
            try {
                const response = await fetch('exam-api.php?action=create_exam', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        exam_name: examName,
                        subject_id: subjectId,
                        duration: duration,
                        passing_marks: passingMarks,
                        instructions: instructions
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Exam created successfully!', 'success');
                    this.reset();
                    loadExams();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            }
        });
    </script>
</body>
</html>