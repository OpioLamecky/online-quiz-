# Kings of Kings College School (KoKCS) - Online Assessment Portal

## Project Overview & Purpose
This is a complete, fully functional web-based Online Assessment Portal for Kings of Kings College School (KoKCS). The system replaces the current paper-based, manual quiz administration process with a secure, automated digital platform.

It supports quiz creation, instant automated grading, centralized question storage, approval workflows, and performance tracking, accessible via web browser.

## Features
- **Role-Based Access Control (RBAC)**: Admin, Supervisor, Teacher, Student
- **Question Bank**: Centralized repository for multiple choice, true/false, and short answer questions
- **Quiz Management**: Create drafts, submit for approval, and publish live quizzes
- **Approval Workflow**: Supervisor reviews and approves teacher quizzes
- **Student Dashboard**: Take timed quizzes with live countdown
- **Automated Grading**: Instant results and feedback upon submission

## Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5.3
- **Backend**: PHP (PDO)
- **Database**: MySQL (MariaDB compatible)

## Setup Instructions

### 1. Requirements
- PHP 8.x
- MySQL 5.7+ or MariaDB 10.x+
- Web Server (Apache/Nginx, e.g., WAMP, XAMPP)

### 2. Database Configuration
1. Open phpMyAdmin or your MySQL CLI.
2. Create a database named `kokcs_oqs`.
3. Import the database schema and seed data by running the `database/schema.sql` script:
   ```bash
   mysql -u root -p kokcs_oqs < database/schema.sql
   ```
   *(Note: The seed script will automatically create demo users, questions, and quizzes).*

### 3. Application Configuration
1. Copy this project folder into your web server's document root (e.g., `c:\wamp64\www\OQS_project`).
2. If your database credentials differ from the default (`root` / empty password), update `includes/db.php`:
   ```php
   $host = 'localhost';
   $dbname = 'kokcs_oqs';
   $username = 'root'; // Update if necessary
   $password = '';     // Update if necessary
   ```

### 4. Running the Application
1. Start your Apache and MySQL servers via your control panel.
2. Access the application in your browser: `http://localhost/OQS_project/index.php`

### 5. Demo Credentials
The `schema.sql` file seeds the following users (Password for all is `password123`):
- **Admin**: admin@kokcs.edu.ug
- **Supervisor**: supervisor@kokcs.edu.ug
- **Teacher**: teacher@kokcs.edu.ug
- **Student**: student@kokcs.edu.ug

## Project Structure
- `/admin`: Administrator dashboard and user management
- `/assets`: CSS and JavaScript files
- `/database`: SQL schema and seed files
- `/includes`: Reusable components (db config, auth, header, footer)
- `/student`: Student dashboard, quiz taking, and results view
- `/supervisor`: Supervisor dashboard and quiz approval workflow
- `/teacher`: Teacher dashboard, question bank, and quiz creation
- `index.php`: Login portal
- `logout.php`: Session termination script

## Academic Context
**Institution**: Kings of Kings College School (KoKCS), Fort Portal, Uganda
**Target Audience**: O-Level & A-Level Students, Teachers, Supervisors
**Course**: BCS 2204 | Individual Programming Project | Mountains of the Moon University
**Prepared by**: 
- ARIHIHIKWIZA OSCAR (Reg: 2024/U/MMU/BCS/00064)
- OPIO LAMECK (Reg: 2024/U/MMU/BCS/00055)
**Instructor**: Mr. Baranga Peter
