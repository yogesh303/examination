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
    <title>Exam Results</title>
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

        .badge.pass {
            background: #d1e7dd;
            color: #0f5132;
        }

        .badge.fail {
            background: #ffebee;
            color: #d32f2f;
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

        .btn-view {
            background: #00d4aa;
            color: white;
        }

        .btn-view:hover {
            background: #00bfa0;
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
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #0a0a0a 0%, #2a2a2a 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 700;
        }

        .close-modal {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            font-size: 24px;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 30px;
        }

        /* Result Summary */
        .result-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: #f8f8f8;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .summary-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 700;
            font-family: 'Syne', sans-serif;
            color: #0a0a0a;
        }

        .summary-value.correct {
            color: #00d4aa;
        }

        .summary-value.wrong {
            color: #ff3366;
        }

        .summary-value.percentage {
            color: #0a0a0a;
        }

        /* Question Review */
        .question-review {
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f8f8;
            border-radius: 12px;
            border-left: 4px solid #e0e0e0;
        }

        .question-review.correct {
            border-left-color: #00d4aa;
            background: #e0f7f4;
        }

        .question-review.wrong {
            border-left-color: #ff3366;
            background: #ffebee;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .question-number {
            font-weight: 600;
            color: #0a0a0a;
        }

        .question-status {
            font-size: 12px;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .question-status.correct {
            background: #00d4aa;
            color: white;
        }

        .question-status.wrong {
            background: #ff3366;
            color: white;
        }

        .question-text {
            font-size: 15px;
            color: #333;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .answer-info {
            font-size: 13px;
            color: #666;
        }

        .answer-info strong {
            color: #0a0a0a;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state-text {
            font-size: 16px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">Exam Results</div>
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Page Title -->
        <div class="page-title">
            <h1><?php echo $isAdmin ? 'All Exam Results' : 'My Exam Results'; ?></h1>
            <p><?php echo $isAdmin ? 'View all student exam results' : 'View your completed exam results'; ?></p>
        </div>

        <!-- Results Table -->
        <div class="card">
            <div class="card-title">Results</div>
            <table>
                <thead>
                    <tr>
                        <?php if ($isAdmin): ?>
                        <th>Student Name</th>
                        <th>Email</th>
                        <?php endif; ?>
                        <th>Exam Name</th>
                        <th>Subject</th>
                        <th>Questions</th>
                        <th>Correct</th>
                        <th>Wrong</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="results-tbody">
                    <tr><td colspan="<?php echo $isAdmin ? '12' : '10'; ?>"><div class="loading"><div class="spinner"></div></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Result Detail Modal -->
    <div id="result-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Exam Result Details</h2>
                <button class="close-modal" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <div id="modal-loading" class="loading">
                    <div class="spinner"></div>
                </div>
                <div id="modal-content" style="display: none;">
                    <!-- Will be populated dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script>
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Load results
        async function loadResults() {
            try {
                const response = await fetch('exam-system-api.php?action=get_results');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('results-tbody');
                    const colSpan = isAdmin ? 12 : 10;
                    
                    if (data.results.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="${colSpan}">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">📊</div>
                                        <div class="empty-state-text">No exam results found</div>
                                    </div>
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    tbody.innerHTML = data.results.map(result => {
                        const percentage = parseFloat(result.percentage);
                        const isPassed = percentage >= 40; // You can make this dynamic based on exam passing marks
                        const statusBadge = isPassed ? 
                            '<span class="badge pass">Passed</span>' : 
                            '<span class="badge fail">Failed</span>';
                        
                        return `
                            <tr>
                                ${isAdmin ? `
                                    <td style="font-weight: 500;">${escapeHtml(result.user_name)}</td>
                                    <td>${escapeHtml(result.user_email)}</td>
                                ` : ''}
                                <td style="font-weight: 500;">${escapeHtml(result.exam_name)}</td>
                                <td>${escapeHtml(result.subject_name)}</td>
                                <td>${result.total_questions}</td>
                                <td style="color: #00d4aa; font-weight: 600;">${result.correct_answers}</td>
                                <td style="color: #ff3366; font-weight: 600;">${result.wrong_answers}</td>
                                <td>${result.marks_obtained}/${result.total_marks}</td>
                                <td style="font-weight: 600;">${percentage}%</td>
                                <td>${statusBadge}</td>
                                <td>${formatDate(result.created_at)}</td>
                                <td>
                                    <button class="btn btn-view" onclick="viewResultDetail(${result.id})">View Details</button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            } catch (error) {
                console.error('Error loading results:', error);
            }
        }

        // View result detail
        async function viewResultDetail(attemptId) {
            document.getElementById('result-modal').classList.add('active');
            document.getElementById('modal-loading').style.display = 'block';
            document.getElementById('modal-content').style.display = 'none';
            
            try {
                const response = await fetch(`exam-system-api.php?action=get_result_details&id=${attemptId}`);
                const data = await response.json();
                
                if (data.success) {
                    const result = data.result;
                    const percentage = parseFloat(result.percentage);
                    const isPassed = percentage >= 40;
                    
                    document.getElementById('modal-content').innerHTML = `
                        <h3 style="margin-bottom: 20px; font-family: 'Syne', sans-serif;">
                            ${escapeHtml(result.exam_name)}
                        </h3>
                        
                        <div class="result-summary">
                            <div class="summary-card">
                                <div class="summary-label">Total Questions</div>
                                <div class="summary-value">${result.total_questions}</div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-label">Correct Answers</div>
                                <div class="summary-value correct">${result.correct_answers}</div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-label">Wrong Answers</div>
                                <div class="summary-value wrong">${result.wrong_answers}</div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-label">Score</div>
                                <div class="summary-value">${result.marks_obtained}/${result.total_marks}</div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-label">Percentage</div>
                                <div class="summary-value percentage">${percentage}%</div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-label">Status</div>
                                <div class="summary-value" style="font-size: 18px;">
                                    ${isPassed ? 
                                        '<span style="color: #00d4aa;">✓ Passed</span>' : 
                                        '<span style="color: #ff3366;">✗ Failed</span>'}
                                </div>
                            </div>
                        </div>
                        
                        <h4 style="margin: 30px 0 20px; font-family: 'Syne', sans-serif;">
                            Question-wise Review
                        </h4>
                        
                        ${result.answers.map((answer, index) => {
                            const isCorrect = answer.is_correct == 1;
                            return `
                                <div class="question-review ${isCorrect ? 'correct' : 'wrong'}">
                                    <div class="question-header">
                                        <span class="question-number">Question ${index + 1}</span>
                                        <span class="question-status ${isCorrect ? 'correct' : 'wrong'}">
                                            ${isCorrect ? '✓ Correct' : '✗ Wrong'}
                                        </span>
                                    </div>
                                    <div class="question-text">${escapeHtml(answer.question_text)}</div>
                                    <div class="answer-info">
                                        <strong>Marks:</strong> ${answer.marks_obtained} / 1
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    `;
                    
                    document.getElementById('modal-loading').style.display = 'none';
                    document.getElementById('modal-content').style.display = 'block';
                } else {
                    alert(data.message || 'Failed to load result details');
                    closeModal();
                }
            } catch (error) {
                console.error('Error loading result details:', error);
                alert('An error occurred while loading result details');
                closeModal();
            }
        }

        function closeModal() {
            document.getElementById('result-modal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('result-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Initialize
        window.addEventListener('load', function() {
            loadResults();
        });
    </script>
</body>
</html>
