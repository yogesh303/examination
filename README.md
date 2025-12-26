# Online Examination System

Complete web-based examination system with user authentication, exam management, and automated grading.

## 📋 Table of Contents
- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [User Roles](#user-roles)
- [Admin Features](#admin-features)
- [Learner Features](#learner-features)
- [Database Structure](#database-structure)
- [Usage Guide](#usage-guide)
- [Security Features](#security-features)

---

## ✨ Features

### Core Features
- ✅ User Authentication (Login/Registration)
- ✅ Role-Based Access (Admin & Learner)
- ✅ Subject Management
- ✅ Question Bank with Multiple Choice
- ✅ Exam Creation & Management
- ✅ Exam Assignment to Users
- ✅ Timed Exam Taking
- ✅ Automatic Grading
- ✅ Detailed Results & Analytics
- ✅ Question Randomization
- ✅ Responsive Design

---

## 💻 System Requirements

- **Web Server:** Apache/Nginx
- **PHP:** 7.4 or higher
- **Database:** MySQL 5.7+ / MariaDB 10.2+
- **Browser:** Modern browser (Chrome, Firefox, Safari, Edge)

---

## 🚀 Installation

### Step 1: Database Setup
```bash
mysql -u root -p < complete-database-setup.sql
```

Creates:
- Database: `user_auth_db`
- All required tables
- Admin user account
- Sample data

### Step 2: Configure Database
Edit `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'user_auth_db');
```

### Step 3: Access System
```
http://localhost/your-project/index.php
```

---

## 👥 User Roles

### Admin
- **Email:** yogesh@gmail.com
- **Full system control**

### Learner
- **Self-registration**
- **Take exams, view results**

---

## 🔧 Admin Features

### 1. Users Management (`dashboard.php`)
**Statistics:**
- Total Users
- Active Users
- Admin Count
- Learner Count

**User Table:**
- ID, Name, Email, Role, Status, Created Date
- Edit user information
- Delete users
- Toggle active/inactive status

### 2. Manage Exams (`exams.php`)

#### Tab 1: Add Subject
**Create subjects:**
- Subject Name (e.g., Mathematics)
- Subject Code (e.g., MATH101)
- Description
- View all subjects table

#### Tab 2: Add Question
**Create questions:**
- Select subject
- Question type:
  - **Radio** - Single correct answer
  - **Checkbox** - Multiple correct answers
- Set marks
- Enter question text
- **4 answer options** (required)
- Mark correct answer(s)

#### Tab 3: Manage Exams
**Create Exam:**
- Exam Name
- Duration (minutes)
- Passing Marks
- Multi-select subjects
- Instructions

**All Exams Table:**
- Name, Subject, Questions, Duration, Marks
- View action

**Assign Exam to Users:**
- Select exam
- Multi-select users (learners)
- Shows selected count
- Assign button

**Assignments Table:**
- User Name & Email
- Exam Name
- Assigned Date
- Status:
  - 🟡 **Assigned** - Not started
  - 🔵 **In Progress** - Started
  - 🟢 **Completed** - Finished
- Score (if completed)

### 3. Results (`results.php`)
**Results Table:**
- Student Name & Email
- Exam Name & Subject
- Questions, Correct, Wrong
- Score & Percentage
- Pass/Fail Status
- Date & Time
- View Details button

**Detailed View (Modal):**
- Summary cards
- Question-wise review
- Correct/Wrong indicators
- Color-coded results

---

## 📚 Learner Features

### 1. Dashboard (`dashboard.php`)
**Profile information**

### 2. My Exams (`my_exams.php`)

#### Assigned Exams Table
- Exam Name, Subject
- Questions, Duration, Marks
- Status & Action Button:
  - "Start Exam" (not started)
  - "Continue" (in progress)
  - "✓ Done" (completed)

#### Exam Interface
**Start Exam:**
- Confirmation dialog
- Timer starts automatically

**During Exam:**
- Exam header with timer
- Question display
- Answer options (radio/checkbox)
- Navigation (Previous/Next)
- Submit button (last question)
- Progress tracker
- Exit button

**Features:**
- ✅ Questions randomized
- ✅ Options randomized
- ✅ Auto-submit on timeout
- ✅ Confirmation for unanswered
- ✅ Progress saving

**Result Display:**
- Percentage score
- Pass/Fail status
- Total/Correct/Wrong
- Final Score

### 3. Results (`results.php`)
**View own results:**
- Exam details
- Performance metrics
- Pass/Fail status
- View Details button

**Detailed View:**
- Summary statistics
- Question-wise review

---

## 🗄️ Database Structure

### Main Tables
```
users               - User accounts
subjects            - Exam subjects
questions           - Question bank
question_options    - Answer options (4 per question)
exams               - Exam definitions
exam_questions      - Questions in exams
exam_assignments    - User assignments
exam_attempts       - Results storage
user_answers        - Individual answers
```

---

## 📖 Usage Guide

### Admin Workflow

#### 1. Setup Subjects
1. Go to "Manage Exams"
2. Click "Add Subject" tab
3. Fill form and submit

#### 2. Add Questions
1. Click "Add Question" tab
2. Select subject
3. Choose type
4. Enter question and 4 options
5. Mark correct answer(s)
6. Save

#### 3. Create Exam
1. Click "Manage Exams" tab
2. Fill exam details
3. Select subjects (multi-select)
4. Create exam

#### 4. Assign to Users
1. Scroll to "Assign Exam to Users"
2. Select exam
3. Select users
4. Click "Assign Exam"

#### 5. Monitor Results
1. Click "Results"
2. View all results
3. Click "View Details" for detailed review

### Learner Workflow

#### 1. Register
1. Go to index.php
2. Click "Create Account"
3. Fill form
4. Submit

#### 2. Take Exam
1. Click "My Exams"
2. Click "Start Exam"
3. Answer questions
4. Navigate with Previous/Next
5. Submit on last question
6. View result

#### 3. View Results
1. Click "Results"
2. See all completed exams
3. Click "View Details" for review

---

## 🔒 Security Features

### Authentication
- ✅ Password hashing (bcrypt)
- ✅ Session management
- ✅ Rate limiting
- ✅ SQL injection protection
- ✅ XSS protection

### Access Control
- ✅ Role-based permissions
- ✅ Session validation
- ✅ API authorization
- ✅ User isolation

### Exam Security
- ✅ Question randomization
- ✅ Option randomization
- ✅ Timer enforcement
- ✅ Auto-submit
- ✅ One attempt per assignment

---

## 🎯 Complete Flow

```
Admin Setup
    ↓
Create Subjects
    ↓
Add Questions
    ↓
Create Exam
    ↓
Assign Questions to Exam
    ↓
Assign Exam to Users
    ↓
Learner Receives Assignment
    ↓
Learner Takes Exam
    ↓
Auto-Grading
    ↓
Results Available
```

### Status Flow
```
Assigned → In Progress → Completed
```

---

## 📊 Feature Matrix

| Feature | Admin | Learner |
|---------|:-----:|:-------:|
| User Management | ✅ | ❌ |
| Subject Management | ✅ | ❌ |
| Question Management | ✅ | ❌ |
| Exam Creation | ✅ | ❌ |
| Exam Assignment | ✅ | ❌ |
| Take Exams | ❌ | ✅ |
| View All Results | ✅ | ❌ |
| View Own Results | ✅ | ✅ |

---

## 📁 Files

```
index.php                    - Login/Registration
config.php                   - Database config
dashboard.php                - Main dashboard
exams.php                    - Exam management
my_exams.php                 - Exam taking
results.php                  - Results viewing
class-dashboard.php          - Dashboard class
class-exam-system.php        - Exam class
exam-system-api.php          - Exam API
dashboard-api.php            - Dashboard API
complete-database-setup.sql  - Database setup
```

---

## 💡 Best Practices

### For Admins
1. Create subjects first
2. Add multiple questions per subject
3. Use clear question text
4. Set appropriate passing marks
5. Test exams before assigning
6. Monitor results regularly

### For Learners
1. Check exam details before starting
2. Read questions carefully
3. Keep track of time
4. Submit before timeout
5. Review results

---

## 🐛 Troubleshooting

### Cannot Login
- Check credentials
- Verify account active
- Wait if rate limited

### Questions Not Saving
- Select subject
- Fill all 4 options
- Mark correct answer

### Cannot Assign Exam
- Ensure exam has questions
- Select users
- Check users active

### Timer Not Working
- Enable JavaScript
- Refresh page
- Clear cache

---

## 📝 Sample Data

Includes:
- **5 Subjects:** Math, Science, English, CS, History
- **6 Questions:** With 4 options each
- **1 Exam:** Mathematics Final
- **1 Admin:** yogesh@gmail.com

---

## 🚀 Future Features

- [ ] Question categories
- [ ] Bulk import
- [ ] Certificates
- [ ] Email notifications
- [ ] Analytics dashboard
- [ ] Mobile app

---

## 📌 Version

**Version:** 1.0.0
**Updated:** December 2024

---

**Built with ❤️ for education**
