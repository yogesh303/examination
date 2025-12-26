<?php
require_once 'config.php';
require_once 'class-exam-system.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$examSystem = new ExamSystem($_SESSION['user_id']);
$isAdmin = $examSystem->isAdmin();

// Only admin can access exam management
if (!$isAdmin) {
    header('Location: dashboard.php');
    exit;
}

$currentUser = [
    'name' => $_SESSION['user_name'],
    'email' => $_SESSION['user_email'],
    'role' => 'admin'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management</title>
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

        /* Header */
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

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn-back {
            padding: 10px 20px;
            background: #f0f0f0;
            color: #333;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: #e0e0e0;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Page Title */
        .page-title {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-family: 'Syne', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #0a0a0a;
            margin-bottom: 8px;
        }

        .page-title p {
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
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Card */
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

        /* Form */
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00d4aa;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        /* Options Container */
        .options-container {
            margin-top: 20px;
        }

        .option-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding: 12px;
            background: #f8f8f8;
            border-radius: 8px;
        }

        .option-number {
            font-weight: 600;
            color: #666;
            min-width: 30px;
        }

        .option-correct {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #00d4aa;
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

        /* Multi-select */
        .multiselect {
            position: relative;
        }

        .multiselect-header {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .multiselect-header:hover {
            border-color: #00d4aa;
        }

        .multiselect-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            margin-top: 4px;
            max-height: 250px;
            overflow-y: auto;
            display: none;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .multiselect-dropdown.show {
            display: block;
        }

        .multiselect-option {
            padding: 10px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .multiselect-option:hover {
            background: #f8f8f8;
        }

        .multiselect-option input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #00d4aa;
        }

        .selected-items {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .selected-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: #e0f7f4;
            color: #00d4aa;
            border-radius: 6px;
            font-size: 13px;
        }

        .selected-item .remove {
            cursor: pointer;
            font-weight: bold;
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
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
            animation: slideDown 0.3s ease;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #e0f7f4;
            color: #00d4aa;
            border: 2px solid #00d4aa;
        }

        .alert-error {
            background: #ffebee;
            color: #d32f2f;
            border: 2px solid #d32f2f;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Table */
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

        .badge.radio {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge.checkbox {
            background: #f3e5f5;
            color: #7b1fa2;
        }

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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">Exam Management</div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Page Title -->
        <div class="page-title">
            <h1>Manage Exams & Questions</h1>
            <p>Create subjects, add questions, and manage exams</p>
        </div>

        <!-- Alert -->
        <div id="alert" class="alert"></div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchTab('add-subject')">Add Subject</button>
            <button class="tab" onclick="switchTab('add-question')">Add Question</button>
            <button class="tab" onclick="switchTab('manage-exams')">Manage Exams</button>
        </div>

        <!-- Add Subject Tab -->
        <div id="add-subject-tab" class="tab-content active">
            <div class="card">
                <div class="card-title">Add New Subject</div>
                <form id="add-subject-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject Name *</label>
                            <input type="text" id="subject-name" required placeholder="e.g., Mathematics">
                        </div>
                        <div class="form-group">
                            <label>Subject Code *</label>
                            <input type="text" id="subject-code" required placeholder="e.g., MATH101">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="subject-description" placeholder="Brief description of the subject..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                </form>
            </div>

            <div class="card">
                <div class="card-title">All Subjects</div>
                <table>
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody id="subjects-tbody">
                        <tr><td colspan="4"><div class="loading"><div class="spinner"></div></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Question Tab -->
        <div id="add-question-tab" class="tab-content">
            <div class="card">
                <div class="card-title">Add New Question</div>
                <form id="add-question-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject *</label>
                            <select id="question-subject" required>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Question Type *</label>
                            <select id="question-type" required onchange="updateQuestionType()">
                                <option value="radio">Single Choice (Radio)</option>
                                <option value="checkbox">Multiple Choice (Checkbox)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Marks *</label>
                            <input type="number" id="question-marks" value="1" min="1" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Question Text *</label>
                        <textarea id="question-text" required placeholder="Enter your question here..."></textarea>
                    </div>

                    <div class="options-container">
                        <label>Answer Options (Exactly 4 required) *</label>
                        <div class="option-row">
                            <span class="option-number">1.</span>
                            <input type="radio" name="correct-answer" class="option-correct" value="0">
                            <input type="text" class="option-text" placeholder="Option 1" required>
                        </div>
                        <div class="option-row">
                            <span class="option-number">2.</span>
                            <input type="radio" name="correct-answer" class="option-correct" value="1">
                            <input type="text" class="option-text" placeholder="Option 2" required>
                        </div>
                        <div class="option-row">
                            <span class="option-number">3.</span>
                            <input type="radio" name="correct-answer" class="option-correct" value="2">
                            <input type="text" class="option-text" placeholder="Option 3" required>
                        </div>
                        <div class="option-row">
                            <span class="option-number">4.</span>
                            <input type="radio" name="correct-answer" class="option-correct" value="3">
                            <input type="text" class="option-text" placeholder="Option 4" required>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-primary">Save Question</button>
                        <button type="button" class="btn btn-secondary" onclick="resetQuestionForm()">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Manage Exams Tab -->
        <div id="manage-exams-tab" class="tab-content">
            <div class="card">
                <div class="card-title">Create New Exam</div>
                <form id="create-exam-form">
                    <div class="form-group">
                        <label>Exam Name *</label>
                        <input type="text" id="exam-name" required placeholder="e.g., Math Final Exam">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Duration (minutes) *</label>
                            <input type="number" id="exam-duration" value="60" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Passing Marks *</label>
                            <input type="number" id="exam-passing-marks" value="40" min="1" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Select Subjects & Questions *</label>
                        <div class="multiselect" id="subject-multiselect">
                            <div class="multiselect-header" onclick="toggleMultiselect()">
                                <span id="multiselect-placeholder">Select subjects...</span>
                                <span>▼</span>
                            </div>
                            <div class="multiselect-dropdown" id="multiselect-dropdown"></div>
                        </div>
                        <div class="selected-items" id="selected-subjects"></div>
                    </div>

                    <div class="form-group">
                        <label>Instructions</label>
                        <textarea id="exam-instructions" placeholder="Enter exam instructions..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Exam</button>
                </form>
            </div>

            <div class="card">
                <div class="card-title">All Exams</div>
                <table>
                    <thead>
                        <tr>
                            <th>Exam Name</th>
                            <th>Subjects</th>
                            <th>Questions</th>
                            <th>Duration</th>
                            <th>Total Marks</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="exams-tbody">
                        <tr><td colspan="7"><div class="loading"><div class="spinner"></div></div></td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Assign Exam to Users -->
            <div class="card">
                <div class="card-title">Assign Exam to Users</div>
                <form id="assign-exam-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Select Exam *</label>
                            <select id="assign-exam-select" required>
                                <option value="">Select Exam</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Select Users *</label>
                            <div class="multiselect" id="users-multiselect">
                                <div class="multiselect-header" onclick="toggleUsersMultiselect()">
                                    <span id="users-multiselect-placeholder">Select users...</span>
                                    <span>▼</span>
                                </div>
                                <div class="multiselect-dropdown" id="users-multiselect-dropdown"></div>
                            </div>
                            <div class="selected-items" id="selected-users"></div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Exam</button>
                </form>
            </div>

            <!-- Exam Assignments Table -->
            <div class="card">
                <div class="card-title">Exam Assignments</div>
                <table>
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Exam Name</th>
                            <th>Assigned Date</th>
                            <th>Status</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody id="assignments-tbody">
                        <tr><td colspan="6"><div class="loading"><div class="spinner"></div></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let selectedSubjects = {};
        let allSubjects = [];
        let questionsBySubject = {};
        let selectedUsers = {};
        let allUsers = [];

        // Switch tabs
        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
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

        // Utility function
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        // ==================== SUBJECTS ====================
        async function loadSubjects() {
            try {
                const response = await fetch('exam-system-api.php?action=get_subjects');
                const data = await response.json();
                
                if (data.success) {
                    allSubjects = data.subjects;
                    
                    // Update subject dropdown
                    const select = document.getElementById('question-subject');
                    select.innerHTML = '<option value="">Select Subject</option>' +
                        data.subjects.map(s => `<option value="${s.id}">${escapeHtml(s.subject_name)}</option>`).join('');
                    
                    // Update subjects table
                    const tbody = document.getElementById('subjects-tbody');
                    if (data.subjects.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center">No subjects found. Add your first subject above!</td></tr>';
                    } else {
                        tbody.innerHTML = data.subjects.map(s => `
                            <tr>
                                <td>${escapeHtml(s.subject_name)}</td>
                                <td>${escapeHtml(s.subject_code)}</td>
                                <td>${escapeHtml(s.description || '-')}</td>
                                <td>${formatDate(s.created_at)}</td>
                            </tr>
                        `).join('');
                    }
                    
                    // Update multiselect dropdown
                    updateMultiselectDropdown();
                }
            } catch (error) {
                console.error('Error loading subjects:', error);
            }
        }

        // Add subject form
        document.getElementById('add-subject-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('subject-name').value;
            const code = document.getElementById('subject-code').value;
            const description = document.getElementById('subject-description').value;
            
            try {
                const response = await fetch('exam-system-api.php?action=add_subject', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, code, description })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Subject added successfully!', 'success');
                    this.reset();
                    loadSubjects();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            }
        });

        // ==================== QUESTIONS ====================
        function updateQuestionType() {
            const questionType = document.getElementById('question-type').value;
            const checkboxes = document.querySelectorAll('.option-correct');
            
            if (questionType === 'radio') {
                checkboxes.forEach((checkbox, index) => {
                    checkbox.type = 'radio';
                    checkbox.name = 'correct-answer';
                    checkbox.value = index;
                });
            } else {
                checkboxes.forEach((checkbox, index) => {
                    checkbox.type = 'checkbox';
                    checkbox.name = '';
                    checkbox.value = index;
                });
            }
        }

        function resetQuestionForm() {
            document.getElementById('add-question-form').reset();
            updateQuestionType();
        }

        // Add question form
        document.getElementById('add-question-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const subjectId = document.getElementById('question-subject').value;
            const questionText = document.getElementById('question-text').value;
            const questionType = document.getElementById('question-type').value;
            const marks = document.getElementById('question-marks').value;
            
            // Collect options
            const optionTexts = document.querySelectorAll('.option-text');
            const optionCorrects = document.querySelectorAll('.option-correct');
            const options = [];
            let hasCorrect = false;
            
            for (let i = 0; i < 4; i++) {
                const text = optionTexts[i].value.trim();
                const isCorrect = optionCorrects[i].checked;
                
                if (!text) {
                    showAlert('All 4 options are required', 'error');
                    return;
                }
                
                options.push({
                    text: text,
                    is_correct: isCorrect
                });
                
                if (isCorrect) hasCorrect = true;
            }
            
            if (!hasCorrect) {
                showAlert('Please mark at least one correct answer', 'error');
                return;
            }
            
            try {
                const response = await fetch('exam-system-api.php?action=add_question', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        subject_id: subjectId,
                        question_text: questionText,
                        question_type: questionType,
                        marks: marks,
                        options: options
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

        // ==================== MULTISELECT ====================
        function updateMultiselectDropdown() {
            const dropdown = document.getElementById('multiselect-dropdown');
            dropdown.innerHTML = allSubjects.map(subject => `
                <div class="multiselect-option" onclick="toggleSubject(${subject.id}, '${escapeHtml(subject.subject_name)}')">
                    <input type="checkbox" id="subject-${subject.id}" ${selectedSubjects[subject.id] ? 'checked' : ''}>
                    <label for="subject-${subject.id}">${escapeHtml(subject.subject_name)}</label>
                </div>
            `).join('');
        }

        function toggleMultiselect() {
            document.getElementById('multiselect-dropdown').classList.toggle('show');
        }

        async function toggleSubject(subjectId, subjectName) {
            const checkbox = document.getElementById(`subject-${subjectId}`);
            
            if (checkbox.checked) {
                delete selectedSubjects[subjectId];
                checkbox.checked = false;
            } else {
                // Load questions for this subject
                if (!questionsBySubject[subjectId]) {
                    const response = await fetch(`exam-system-api.php?action=get_questions&subject_id=${subjectId}`);
                    const data = await response.json();
                    if (data.success) {
                        questionsBySubject[subjectId] = data.questions;
                    }
                }
                
                selectedSubjects[subjectId] = {
                    name: subjectName,
                    questions: questionsBySubject[subjectId] || []
                };
                checkbox.checked = true;
            }
            
            updateSelectedSubjects();
        }

        function updateSelectedSubjects() {
            const container = document.getElementById('selected-subjects');
            const placeholder = document.getElementById('multiselect-placeholder');
            
            const items = Object.entries(selectedSubjects);
            
            if (items.length === 0) {
                placeholder.textContent = 'Select subjects...';
                container.innerHTML = '';
            } else {
                placeholder.textContent = `${items.length} subject(s) selected`;
                container.innerHTML = items.map(([id, data]) => `
                    <div class="selected-item">
                        ${escapeHtml(data.name)} (${data.questions.length} questions)
                        <span class="remove" onclick="removeSubject(${id})">×</span>
                    </div>
                `).join('');
            }
        }

        function removeSubject(subjectId) {
            delete selectedSubjects[subjectId];
            document.getElementById(`subject-${subjectId}`).checked = false;
            updateSelectedSubjects();
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.multiselect')) {
                document.getElementById('multiselect-dropdown').classList.remove('show');
            }
        });

        // ==================== EXAMS ====================
        document.getElementById('create-exam-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const examName = document.getElementById('exam-name').value;
            const duration = document.getElementById('exam-duration').value;
            const passingMarks = document.getElementById('exam-passing-marks').value;
            const instructions = document.getElementById('exam-instructions').value;
            
            if (Object.keys(selectedSubjects).length === 0) {
                showAlert('Please select at least one subject with questions', 'error');
                return;
            }
            
            // Collect all questions from selected subjects
            const allQuestions = [];
            Object.values(selectedSubjects).forEach(subject => {
                allQuestions.push(...subject.questions.map(q => q.id));
            });
            
            if (allQuestions.length === 0) {
                showAlert('Selected subjects have no questions. Please add questions first.', 'error');
                return;
            }
            
            try {
                // Create exam with first subject (we'll improve this to support multiple subjects)
                const firstSubjectId = Object.keys(selectedSubjects)[0];
                
                const response = await fetch('exam-system-api.php?action=create_exam', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        exam_name: examName,
                        subject_id: firstSubjectId,
                        duration: duration,
                        passing_marks: passingMarks,
                        instructions: instructions
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Assign questions to exam
                    const assignResponse = await fetch('exam-system-api.php?action=assign_questions', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            exam_id: data.id,
                            question_ids: allQuestions
                        })
                    });
                    
                    const assignData = await assignResponse.json();
                    
                    if (assignData.success) {
                        showAlert(`Exam created successfully with ${allQuestions.length} questions!`, 'success');
                        this.reset();
                        selectedSubjects = {};
                        updateSelectedSubjects();
                        loadExams();
                    }
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            }
        });

        async function loadExams() {
            try {
                const response = await fetch('exam-system-api.php?action=get_exams');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('exams-tbody');
                    
                    if (data.exams.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center">No exams found. Create your first exam above!</td></tr>';
                    } else {
                        tbody.innerHTML = data.exams.map(exam => `
                            <tr>
                                <td>${escapeHtml(exam.exam_name)}</td>
                                <td>${escapeHtml(exam.subject_name)}</td>
                                <td>${exam.question_count}</td>
                                <td>${exam.duration_minutes} min</td>
                                <td>${exam.total_marks || 0}</td>
                                <td>${formatDate(exam.created_at)}</td>
                                <td>
                                    <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;" onclick="viewExamDetails(${exam.id})">View</button>
                                </td>
                            </tr>
                        `).join('');
                    }
                    
                    // Update assign exam dropdown
                    const assignSelect = document.getElementById('assign-exam-select');
                    if (assignSelect) {
                        assignSelect.innerHTML = '<option value="">Select Exam</option>' +
                            data.exams.map(e => `<option value="${e.id}">${escapeHtml(e.exam_name)}</option>`).join('');
                    }
                }
            } catch (error) {
                console.error('Error loading exams:', error);
            }
        }

        function viewExamDetails(examId) {
            showAlert('View exam details feature coming soon', 'success');
        }

        // ==================== USERS ====================
        async function loadUsers() {
            try {
                const response = await fetch('dashboard-api.php?action=get_users');
                const data = await response.json();
                
                if (data.success) {
                    allUsers = data.users.filter(u => u.user_role === 'learner');
                    updateUsersMultiselectDropdown();
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        function updateUsersMultiselectDropdown() {
            const dropdown = document.getElementById('users-multiselect-dropdown');
            dropdown.innerHTML = allUsers.map(user => `
                <div class="multiselect-option" onclick="toggleUser(${user.id}, '${escapeHtml(user.full_name)}', '${escapeHtml(user.email)}')">
                    <input type="checkbox" id="user-${user.id}" ${selectedUsers[user.id] ? 'checked' : ''}>
                    <label for="user-${user.id}">${escapeHtml(user.full_name)} (${escapeHtml(user.email)})</label>
                </div>
            `).join('');
        }

        function toggleUsersMultiselect() {
            document.getElementById('users-multiselect-dropdown').classList.toggle('show');
        }

        function toggleUser(userId, userName, userEmail) {
            const checkbox = document.getElementById(`user-${userId}`);
            
            if (checkbox.checked) {
                delete selectedUsers[userId];
                checkbox.checked = false;
            } else {
                selectedUsers[userId] = {
                    name: userName,
                    email: userEmail
                };
                checkbox.checked = true;
            }
            
            updateSelectedUsers();
        }

        function updateSelectedUsers() {
            const container = document.getElementById('selected-users');
            const placeholder = document.getElementById('users-multiselect-placeholder');
            
            const items = Object.entries(selectedUsers);
            
            if (items.length === 0) {
                placeholder.textContent = 'Select users...';
                container.innerHTML = '';
            } else {
                placeholder.textContent = `${items.length} user(s) selected`;
                container.innerHTML = items.map(([id, data]) => `
                    <div class="selected-item">
                        ${escapeHtml(data.name)}
                        <span class="remove" onclick="removeUser(${id})">×</span>
                    </div>
                `).join('');
            }
        }

        function removeUser(userId) {
            delete selectedUsers[userId];
            document.getElementById(`user-${userId}`).checked = false;
            updateSelectedUsers();
        }

        // ==================== ASSIGNMENTS ====================
        document.getElementById('assign-exam-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const examId = document.getElementById('assign-exam-select').value;
            const userIds = Object.keys(selectedUsers).map(id => parseInt(id));
            
            if (userIds.length === 0) {
                showAlert('Please select at least one user', 'error');
                return;
            }
            
            try {
                const response = await fetch('exam-system-api.php?action=assign_exam', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        exam_id: examId,
                        user_ids: userIds
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(`Exam assigned to ${userIds.length} user(s) successfully!`, 'success');
                    this.reset();
                    selectedUsers = {};
                    updateSelectedUsers();
                    loadAssignments();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            }
        });

        async function loadAssignments() {
            try {
                const response = await fetch('exam-system-api.php?action=get_assignments');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('assignments-tbody');
                    
                    if (data.assignments.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center">No assignments found</td></tr>';
                    } else {
                        tbody.innerHTML = data.assignments.map(assignment => {
                            let statusBadge = '';
                            let scoreDisplay = '-';
                            
                            if (assignment.status === 'assigned') {
                                statusBadge = '<span class="badge" style="background: #fff3cd; color: #856404;">Assigned</span>';
                            } else if (assignment.status === 'in_progress') {
                                statusBadge = '<span class="badge" style="background: #cfe2ff; color: #084298;">In Progress</span>';
                            } else if (assignment.status === 'completed') {
                                statusBadge = '<span class="badge" style="background: #d1e7dd; color: #0f5132;">Completed</span>';
                                if (assignment.marks_obtained !== null) {
                                    scoreDisplay = `${assignment.marks_obtained}/${assignment.total_marks} (${assignment.percentage}%)`;
                                }
                            }
                            
                            return `
                                <tr>
                                    <td>${escapeHtml(assignment.user_name)}</td>
                                    <td>${escapeHtml(assignment.user_email)}</td>
                                    <td>${escapeHtml(assignment.exam_name)}</td>
                                    <td>${formatDate(assignment.assigned_at)}</td>
                                    <td>${statusBadge}</td>
                                    <td>${scoreDisplay}</td>
                                </tr>
                            `;
                        }).join('');
                    }
                }
            } catch (error) {
                console.error('Error loading assignments:', error);
            }
        }

        // Initialize
        // Initialize
        window.addEventListener('load', function() {
            loadSubjects();
            loadExams();
            loadUsers();
            loadAssignments();
            updateQuestionType();
        });
    </script>
</body>
</html>