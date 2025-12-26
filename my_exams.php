<?php
require_once 'config.php';
require_once 'class-exam-system.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$examSystem = new ExamSystem($_SESSION['user_id']);
$currentUser = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'email' => $_SESSION['user_email']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Exams</title>
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

        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge.assigned {
            background: #fff3cd;
            color: #856404;
        }

        .badge.in-progress {
            background: #cfe2ff;
            color: #084298;
        }

        .badge.completed {
            background: #d1e7dd;
            color: #0f5132;
        }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
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

        .btn-success {
            background: #00d4aa;
            color: white;
        }

        .btn-success:hover {
            background: #00bfa0;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #666;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        /* Exam Interface */
        .exam-container {
            display: none;
        }

        .exam-container.active {
            display: block;
        }

        .exam-header {
            background: linear-gradient(135deg, #0a0a0a 0%, #2a2a2a 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .exam-info h2 {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .exam-info p {
            opacity: 0.8;
            font-size: 14px;
        }

        .timer {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 25px;
            border-radius: 8px;
            text-align: center;
        }

        .timer-label {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .timer-value {
            font-size: 28px;
            font-weight: 700;
            font-family: 'Syne', sans-serif;
        }

        /* Question Card */
        .question-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .question-number {
            color: #00d4aa;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .question-text {
            font-size: 18px;
            font-weight: 500;
            color: #0a0a0a;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: #f8f8f8;
            border: 2px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option:hover {
            background: #e3f2fd;
            border-color: #00d4aa;
        }

        .option input[type="radio"],
        .option input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            cursor: pointer;
            accent-color: #00d4aa;
        }

        .option label {
            flex: 1;
            font-size: 15px;
            color: #333;
            cursor: pointer;
        }

        .option.selected {
            background: #e0f7f4;
            border-color: #00d4aa;
        }

        /* Navigation */
        .exam-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }

        .question-progress {
            font-size: 14px;
            color: #666;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
        }

        /* Result */
        .result-container {
            display: none;
            text-align: center;
        }

        .result-container.active {
            display: block;
        }

        .result-header {
            background: linear-gradient(135deg, #00d4aa 0%, #00bfa0 100%);
            color: white;
            padding: 40px;
            border-radius: 16px;
            margin-bottom: 30px;
        }

        .result-header h2 {
            font-family: 'Syne', sans-serif;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .result-score {
            font-size: 48px;
            font-weight: 700;
            font-family: 'Syne', sans-serif;
            margin: 20px 0;
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            font-family: 'Syne', sans-serif;
            color: #0a0a0a;
        }

        .stat-value.correct {
            color: #00d4aa;
        }

        .stat-value.wrong {
            color: #ff3366;
        }

        /* Loading */
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

        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">My Exams</div>
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Page Title -->
        <div class="page-title">
            <h1>My Assigned Exams</h1>
            <p>View and attend your assigned exams</p>
        </div>

        <!-- Exams List -->
        <div id="exams-list" class="card">
            <div class="card-title">Available Exams</div>
            <table>
                <thead>
                    <tr>
                        <th>Exam Name</th>
                        <th>Subject</th>
                        <th>Questions</th>
                        <th>Duration</th>
                        <th>Total Marks</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="exams-tbody">
                    <tr><td colspan="7"><div class="loading"><div class="spinner"></div></div></td></tr>
                </tbody>
            </table>
        </div>

        <!-- Exam Interface -->
        <div id="exam-interface" class="exam-container">
            <!-- Exam Header -->
            <div class="exam-header">
                <div class="exam-info">
                    <h2 id="exam-title">Exam Title</h2>
                    <p id="exam-details">Subject • Questions • Marks</p>
                </div>
                <div class="timer">
                    <div class="timer-label">Time Remaining</div>
                    <div class="timer-value" id="timer">00:00</div>
                </div>
            </div>

            <!-- Question Card -->
            <div class="question-card">
                <div class="question-number" id="question-number">Question 1 of 10</div>
                <div class="question-text" id="question-text">Loading question...</div>
                <div class="options" id="options-container">
                    <!-- Options will be loaded here -->
                </div>
            </div>

            <!-- Navigation -->
            <div class="exam-navigation">
                <div class="question-progress">
                    <span id="answered-count">0</span> of <span id="total-count">0</span> answered
                </div>
                <div class="nav-buttons">
                    <button class="btn btn-secondary" id="prev-btn" onclick="previousQuestion()">← Previous</button>
                    <button class="btn btn-primary" id="next-btn" onclick="nextQuestion()">Next →</button>
                    <button class="btn btn-success hidden" id="submit-btn" onclick="submitExam()">Submit Exam</button>
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button class="btn btn-secondary" onclick="exitExam()">Exit Exam</button>
            </div>
        </div>

        <!-- Result -->
        <div id="result-container" class="result-container">
            <div class="result-header">
                <h2>Exam Completed!</h2>
                <div class="result-score" id="result-percentage">0%</div>
                <p id="result-status">Pass/Fail</p>
            </div>

            <div class="result-stats">
                <div class="stat-box">
                    <div class="stat-label">Total Questions</div>
                    <div class="stat-value" id="result-total">0</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Correct Answers</div>
                    <div class="stat-value correct" id="result-correct">0</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Wrong Answers</div>
                    <div class="stat-value wrong" id="result-wrong">0</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Score</div>
                    <div class="stat-value" id="result-score">0/0</div>
                </div>
            </div>

            <button class="btn btn-primary" onclick="backToExamsList()">Back to Exams</button>
        </div>
    </div>

    <script>
        let currentExamData = null;
        let currentQuestionIndex = 0;
        let userAnswers = {};
        let timerInterval = null;
        let timeRemaining = 0;

        // Load user's exams
        async function loadExams() {
            try {
                const response = await fetch('exam-system-api.php?action=get_my_exams');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('exams-tbody');
                    
                    if (data.exams.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 40px;">No exams assigned yet. Check back later!</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = data.exams.map(exam => {
                        let statusBadge = '';
                        let actionButton = '';
                        
                        if (exam.status === 'assigned') {
                            statusBadge = '<span class="badge assigned">Not Started</span>';
                            actionButton = `<button class="btn btn-primary" onclick="startExam(${exam.exam_id})">Start Exam</button>`;
                        } else if (exam.status === 'in_progress') {
                            statusBadge = '<span class="badge in-progress">In Progress</span>';
                            actionButton = `<button class="btn btn-primary" onclick="continueExam(${exam.attempt_id})">Continue</button>`;
                        } else if (exam.status === 'completed') {
                            statusBadge = '<span class="badge completed">Completed</span>';
                            actionButton = '<span style="color: #0f5132; font-weight: 600;">✓ Done</span>';
                        }
                        
                        return `
                            <tr>
                                <td style="font-weight: 500;">${escapeHtml(exam.exam_name)}</td>
                                <td>${escapeHtml(exam.subject_name)}</td>
                                <td>${exam.question_count}</td>
                                <td>${exam.duration_minutes} min</td>
                                <td>${exam.total_marks}</td>
                                <td>${statusBadge}</td>
                                <td>${actionButton}</td>
                            </tr>
                        `;
                    }).join('');
                }
            } catch (error) {
                console.error('Error loading exams:', error);
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Start exam
        async function startExam(examId) {
            if (!confirm('Are you ready to start this exam? Timer will start immediately.')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('exam_id', examId);
                
                const response = await fetch('exam-system-api.php?action=start_exam', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentExamData = data;
                    userAnswers = {};
                    currentQuestionIndex = 0;
                    
                    // Set timer
                    timeRemaining = data.exam.duration_minutes * 60;
                    
                    // Show exam interface
                    document.getElementById('exams-list').classList.add('hidden');
                    document.getElementById('exam-interface').classList.add('active');
                    
                    // Set exam info
                    document.getElementById('exam-title').textContent = data.exam.exam_name;
                    document.getElementById('exam-details').textContent = `${data.exam.subject_name} • ${data.questions.length} Questions • ${data.exam.total_marks} Marks`;
                    document.getElementById('total-count').textContent = data.questions.length;
                    
                    // Start timer
                    startTimer();
                    
                    // Show first question
                    showQuestion(0);
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error starting exam:', error);
                alert('An error occurred while starting the exam');
            }
        }

        // Timer
        function startTimer() {
            updateTimerDisplay();
            
            timerInterval = setInterval(() => {
                timeRemaining--;
                updateTimerDisplay();
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    alert('Time is up! Your exam will be submitted automatically.');
                    submitExam();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            document.getElementById('timer').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Warning color when < 5 minutes
            if (timeRemaining < 300) {
                document.getElementById('timer').style.color = '#ff3366';
            }
        }

        // Show question
        function showQuestion(index) {
            const question = currentExamData.questions[index];
            currentQuestionIndex = index;
            
            // Update question number
            document.getElementById('question-number').textContent = 
                `Question ${index + 1} of ${currentExamData.questions.length}`;
            
            // Update question text
            document.getElementById('question-text').textContent = question.question_text;
            
            // Update options
            const container = document.getElementById('options-container');
            const inputType = question.question_type === 'radio' ? 'radio' : 'checkbox';
            
            container.innerHTML = question.options.map((option, i) => `
                <div class="option" onclick="selectOption(${index}, ${option.id}, '${inputType}')">
                    <input type="${inputType}" 
                           name="question-${question.id}" 
                           id="option-${option.id}" 
                           value="${option.id}"
                           ${isOptionSelected(index, option.id) ? 'checked' : ''}>
                    <label for="option-${option.id}">${escapeHtml(option.text)}</label>
                </div>
            `).join('');
            
            // Update navigation
            document.getElementById('prev-btn').disabled = index === 0;
            
            if (index === currentExamData.questions.length - 1) {
                document.getElementById('next-btn').classList.add('hidden');
                document.getElementById('submit-btn').classList.remove('hidden');
            } else {
                document.getElementById('next-btn').classList.remove('hidden');
                document.getElementById('submit-btn').classList.add('hidden');
            }
            
            // Update answered count
            updateAnsweredCount();
        }

        function selectOption(questionIndex, optionId, type) {
            const question = currentExamData.questions[questionIndex];
            
            if (type === 'radio') {
                userAnswers[question.id] = [optionId];
            } else {
                if (!userAnswers[question.id]) {
                    userAnswers[question.id] = [];
                }
                
                const index = userAnswers[question.id].indexOf(optionId);
                if (index > -1) {
                    userAnswers[question.id].splice(index, 1);
                } else {
                    userAnswers[question.id].push(optionId);
                }
            }
            
            // Update UI
            showQuestion(questionIndex);
        }

        function isOptionSelected(questionIndex, optionId) {
            const question = currentExamData.questions[questionIndex];
            return userAnswers[question.id] && userAnswers[question.id].includes(optionId);
        }

        function updateAnsweredCount() {
            const answered = Object.keys(userAnswers).filter(k => userAnswers[k].length > 0).length;
            document.getElementById('answered-count').textContent = answered;
        }

        function nextQuestion() {
            if (currentQuestionIndex < currentExamData.questions.length - 1) {
                showQuestion(currentQuestionIndex + 1);
            }
        }

        function previousQuestion() {
            if (currentQuestionIndex > 0) {
                showQuestion(currentQuestionIndex - 1);
            }
        }

        // Submit exam
        async function submitExam() {
            const unanswered = currentExamData.questions.length - Object.keys(userAnswers).filter(k => userAnswers[k].length > 0).length;
            
            if (unanswered > 0) {
                if (!confirm(`You have ${unanswered} unanswered question(s). Are you sure you want to submit?`)) {
                    return;
                }
            }
            
            // Stop timer
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            
            // Prepare answers
            const answers = currentExamData.questions.map(q => ({
                question_id: q.id,
                selected_options: userAnswers[q.id] || []
            }));
            
            try {
                const response = await fetch('exam-system-api.php?action=submit_exam', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        attempt_id: currentExamData.attempt_id,
                        answers: answers
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showResult(data.result);
                } else {
                    alert(data.message || 'Failed to submit exam');
                }
            } catch (error) {
                console.error('Error submitting exam:', error);
                alert('An error occurred while submitting the exam');
            }
        }

        function showResult(result) {
            // Hide exam interface
            document.getElementById('exam-interface').classList.remove('active');
            
            // Show result
            document.getElementById('result-container').classList.add('active');
            
            // Update result values
            document.getElementById('result-percentage').textContent = result.percentage + '%';
            document.getElementById('result-total').textContent = result.total_questions;
            document.getElementById('result-correct').textContent = result.correct_answers;
            document.getElementById('result-wrong').textContent = result.wrong_answers;
            document.getElementById('result-score').textContent = `${result.marks_obtained}/${result.total_marks}`;
            
            // Pass/Fail status
            const passingPercentage = 40; // You can make this dynamic
            if (result.percentage >= passingPercentage) {
                document.getElementById('result-status').textContent = '✓ Passed';
                document.getElementById('result-status').style.color = '#00d4aa';
            } else {
                document.getElementById('result-status').textContent = '✗ Failed';
                document.getElementById('result-status').style.color = '#ff3366';
            }
        }

        function exitExam() {
            if (confirm('Are you sure you want to exit? Your progress will be saved.')) {
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
                backToExamsList();
            }
        }

        function backToExamsList() {
            document.getElementById('exam-interface').classList.remove('active');
            document.getElementById('result-container').classList.remove('active');
            document.getElementById('exams-list').classList.remove('hidden');
            
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            
            loadExams();
        }

        // Initialize
        window.addEventListener('load', function() {
            loadExams();
        });
    </script>
</body>
</html>
