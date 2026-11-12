-- ============================================================================
-- NSBM STUDY CIRCLE - COMPLETE DATABASE SCHEMA & SEED DATA
-- Database Name: nsbm_student_circle
-- Covers: Public Website, Student Portal Dashboard, and Admin Console
-- ============================================================================

CREATE DATABASE IF NOT EXISTS nsbm_student_circle
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE nsbm_student_circle;

-- ============================================================================
-- 1. USERS TABLE (Students & Administrators)
-- ============================================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    admin_type ENUM('Super administrator', 'Content moderator', 'Community moderator') NULL,
    student_id VARCHAR(30) NULL UNIQUE,
    faculty ENUM('Computing', 'Business', 'Engineering', 'Science') NOT NULL DEFAULT 'Computing',
    degree_program VARCHAR(180) DEFAULT 'BSc (Hons) Computer Science · Year 3',
    academic_year VARCHAR(20) DEFAULT 'Year 3',
    status ENUM('Active', 'Pending', 'Suspended') NOT NULL DEFAULT 'Active',
    bio TEXT NULL,
    academic_interests VARCHAR(255) DEFAULT 'Algorithms, Web Development, Databases',
    profile_visibility ENUM('NSBM Study Circle members', 'Public', 'Private') DEFAULT 'NSBM Study Circle members',
    profile_image VARCHAR(255) NULL,
    last_active_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_faculty (faculty),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. ACADEMIC AREAS TABLE (Faculties / Departments)
-- ============================================================================
CREATE TABLE IF NOT EXISTS academic_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    level VARCHAR(20) DEFAULT 'Year 1',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. SUBJECTS / MODULES TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    area_id INT NOT NULL,
    academic_year VARCHAR(20) DEFAULT 'Year 1',
    description TEXT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (area_id) REFERENCES academic_areas(id) ON DELETE CASCADE,
    INDEX idx_subjects_area (area_id),
    INDEX idx_subjects_year (academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. CATEGORIES TABLE (Discussion & Content Categories)
-- ============================================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. RESOURCES / STUDY MATERIALS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    about_text TEXT NULL,
    whats_included TEXT NULL,
    preview_badge VARCHAR(100) NULL,
    preview_heading VARCHAR(255) NULL,
    preview_subheading VARCHAR(255) NULL,
    subject_id INT NULL,
    module_code VARCHAR(30) NULL,
    faculty ENUM('Computing', 'Business', 'Engineering', 'Science') NOT NULL DEFAULT 'Computing',
    academic_year VARCHAR(20) DEFAULT 'Year 1',
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(20) DEFAULT 'PDF',
    file_size_mb DECIMAL(5,2) DEFAULT 0.00,
    page_count INT DEFAULT 1,
    language VARCHAR(50) DEFAULT 'English',
    uploaded_by INT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected', 'Archived') DEFAULT 'Approved',
    downloads INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 5.0,
    ratings_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_resources_faculty (faculty),
    INDEX idx_resources_status (status),
    INDEX idx_resources_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. RESOURCE DOWNLOADS HISTORY TABLE (Powers Student "My Downloads")
-- ============================================================================
CREATE TABLE IF NOT EXISTS resource_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_downloads_user (user_id),
    INDEX idx_downloads_resource (resource_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. SAVED / BOOKMARKED RESOURCES TABLE (Powers Student "Saved Resources")
-- ============================================================================
CREATE TABLE IF NOT EXISTS saved_resources (
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, resource_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. RESOURCE RATINGS & REVIEWS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS resource_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_resource_review (user_id, resource_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. DISCUSSIONS TABLE (Academic Questions & Forum Threads)
-- ============================================================================
CREATE TABLE IF NOT EXISTS discussions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    faculty ENUM('Computing', 'Business', 'Engineering', 'Science') DEFAULT 'Computing',
    category VARCHAR(100) DEFAULT 'Computing',
    module_code VARCHAR(30) NULL,
    tags VARCHAR(255) DEFAULT 'algorithms, sorting',
    author_id INT NOT NULL,
    upvotes INT DEFAULT 0,
    answers_count INT DEFAULT 0,
    views_count INT DEFAULT 45,
    is_solved TINYINT(1) DEFAULT 0,
    status ENUM('Open', 'Closed', 'Resolved') DEFAULT 'Open',
    is_hidden TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_discussions_faculty (faculty),
    INDEX idx_discussions_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. DISCUSSION ANSWERS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS discussion_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discussion_id INT NOT NULL,
    author_id INT NOT NULL,
    author_role VARCHAR(100) DEFAULT 'Student member',
    body TEXT NOT NULL,
    upvotes INT DEFAULT 0,
    is_solution TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_answers_discussion (discussion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. DISCUSSION REPLIES TABLE (Nested Replies to Answers)
-- ============================================================================
CREATE TABLE IF NOT EXISTS discussion_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    answer_id INT NOT NULL,
    user_id INT NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (answer_id) REFERENCES discussion_answers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_answer_replies (answer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. DISCUSSION VOTES TABLE (Helpful Votes on Questions & Answers)
-- ============================================================================
CREATE TABLE IF NOT EXISTS discussion_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    discussion_id INT NULL,
    answer_id INT NULL,
    vote_type ENUM('helpful') DEFAULT 'helpful',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE,
    FOREIGN KEY (answer_id) REFERENCES discussion_answers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_discussion_vote (user_id, discussion_id),
    UNIQUE KEY unique_user_answer_vote (user_id, answer_id),
    INDEX idx_discussion_votes (discussion_id),
    INDEX idx_answer_votes (answer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. STUDY GROUPS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS study_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(10) DEFAULT 'SG',
    category VARCHAR(50) DEFAULT 'Computing',
    is_featured TINYINT(1) DEFAULT 0,
    schedule_info VARCHAR(100) DEFAULT 'Meets Wednesdays',
    activity_status VARCHAR(50) DEFAULT 'Active this week',
    description TEXT NULL,
    about_text TEXT NULL,
    what_we_study VARCHAR(255) NULL,
    created_date_str VARCHAR(50) DEFAULT 'Created February 2025',
    badge_color VARCHAR(30) DEFAULT 'badge-ds',
    creator_id INT NOT NULL,
    member_count INT DEFAULT 1,
    max_members INT DEFAULT 48,
    status ENUM('Active', 'Review', 'Flagged') DEFAULT 'Active',
    reports_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_groups_category (category),
    INDEX idx_groups_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 14. STUDY GROUP MEMBERS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS study_group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_user (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 15. STUDY GROUP ACTIVITIES TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS study_group_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_initials VARCHAR(10) NOT NULL,
    action_text VARCHAR(255) NOT NULL,
    relative_time VARCHAR(100) NOT NULL,
    link_url VARCHAR(255) DEFAULT '#',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 16. STUDY GROUP COORDINATORS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS study_group_coordinators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    initials VARCHAR(10) NOT NULL,
    role_title VARCHAR(100) DEFAULT 'Coordinator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 17. STUDY SESSIONS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS study_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    module_code VARCHAR(30) DEFAULT 'CS2102',
    faculty ENUM('Computing', 'Business', 'Engineering', 'Science') DEFAULT 'Computing',
    host_id INT NOT NULL,
    session_date DATETIME NOT NULL,
    duration_mins INT DEFAULT 90,
    format_type VARCHAR(50) DEFAULT 'Online',
    meeting_link VARCHAR(255) DEFAULT 'https://teams.microsoft.com/l/meetup-join/nsbm-study-circle',
    location_details VARCHAR(255) NOT NULL,
    agenda TEXT NULL,
    capacity INT DEFAULT 24,
    enrolled_count INT DEFAULT 0,
    status ENUM('Scheduled', 'Review', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sessions_date (session_date),
    INDEX idx_sessions_faculty (faculty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 18. STUDY SESSION ATTENDEES TABLE (Seat Reservations)
-- ============================================================================
CREATE TABLE IF NOT EXISTS study_session_attendees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    user_id INT NOT NULL,
    reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_session_user (session_id, user_id),
    FOREIGN KEY (session_id) REFERENCES study_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 19. NOTIFICATIONS TABLE (Powers Student Portal Notifications)
-- ============================================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    category ENUM('upload', 'discussion', 'group', 'session', 'system') DEFAULT 'system',
    link_url VARCHAR(255) DEFAULT 'index.php',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 20. ANNOUNCEMENTS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    faculty ENUM('All', 'Computing', 'Business', 'Engineering', 'Science') DEFAULT 'All',
    created_by INT NOT NULL,
    is_pinned TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 21. EVENTS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    event_date DATETIME NOT NULL,
    venue VARCHAR(180) NOT NULL,
    faculty ENUM('All', 'Computing', 'Business', 'Engineering', 'Science') DEFAULT 'All',
    banner_image VARCHAR(255) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 22. CONTACT MESSAGES TABLE (Powers Contact & Help Form)
-- ============================================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    topic VARCHAR(100) DEFAULT 'General inquiry',
    subject VARCHAR(200) NULL,
    message TEXT NOT NULL,
    status ENUM('New', 'In Progress', 'Resolved') DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 23. REPORTED CONTENT TABLE (Powers Admin Moderation & Reports)
-- ============================================================================
CREATE TABLE IF NOT EXISTS reported_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_snippet TEXT NOT NULL,
    target_type ENUM('Discussion', 'Answer', 'Group', 'Resource') DEFAULT 'Discussion',
    target_id INT NOT NULL,
    reason ENUM('Academic integrity', 'Spam', 'Conduct', 'Duplicate') DEFAULT 'Academic integrity',
    priority ENUM('High priority', 'Medium', 'Low') DEFAULT 'Medium',
    status ENUM('Open', 'Resolved', 'Dismissed') DEFAULT 'Open',
    reported_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 24. PLATFORM SETTINGS TABLE (Powers Admin Console Settings)
-- ============================================================================
CREATE TABLE IF NOT EXISTS platform_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 25. LOGIN LOGS TABLE (Records every Login & Signup event in phpMyAdmin)
-- ============================================================================
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(120) NULL,
    email VARCHAR(160) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    action_type ENUM('Login', 'Signup', 'Logout') DEFAULT 'Login',
    ip_address VARCHAR(45) DEFAULT '127.0.0.1',
    user_agent VARCHAR(255) NULL,
    status ENUM('Success', 'Failed') DEFAULT 'Success',
    logged_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_login_logs_email (email),
    INDEX idx_login_logs_time (logged_in_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- SEED DATA
-- ============================================================================

-- 1. Seed Users (Administrators & Students)
INSERT INTO users (id, name, email, password, role, admin_type, student_id, faculty, degree_program, academic_year, status, bio, academic_interests) VALUES
(1, 'Admin User', 'admin@nsbm.lk', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'admin', 'Super administrator', 'ADM001', 'Computing', 'Platform Administration', 'Staff', 'Active', 'Super Administrator for NSBM Study Circle.', 'Academic Governance, Moderation, Curriculum Support'),
(2, 'Alex Silva', 'alex.silva@nsbm.ac.lk', '$2y$10$eE0mEwK5jBfN9/ZgS2u49.Y6sR1tVbK9QdYlF5gT6wU2oP3iA4k5e', 'student', NULL, '10023456', 'Computing', 'BSc (Hons) Computer Science · Year 3', 'Year 3', 'Active', 'Third-year Computer Science student passionate about algorithms, distributed systems, and collaborative peer learning.', 'Algorithms, Data Structures, Cloud Architecture, SQL'),
(3, 'Nethmi Perera', 'nethmi.perera@nsbm.ac.lk', '$2y$10$eE0mEwK5jBfN9/ZgS2u49.Y6sR1tVbK9QdYlF5gT6wU2oP3iA4k5e', 'student', NULL, '10023457', 'Computing', 'BSc (Hons) Software Engineering · Year 2', 'Year 2', 'Active', 'Peer mentor and study circle coordinator for Data Structures and Database Systems.', 'Data Structures, Java, Full-Stack Development'),
(4, 'Kavindu Silva', 'kavindu.silva@nsbm.ac.lk', '$2y$10$eE0mEwK5jBfN9/ZgS2u49.Y6sR1tVbK9QdYlF5gT6wU2oP3iA4k5e', 'student', NULL, '10023458', 'Business', 'BSc (Hons) Business Management · Year 2', 'Year 2', 'Active', 'Focused on corporate finance, accounting principles, and business case analysis.', 'Financial Accounting, WACC, Business Strategy'),
(5, 'Ravindu Mendis', 'ravindu.m@nsbm.ac.lk', '$2y$10$eE0mEwK5jBfN9/ZgS2u49.Y6sR1tVbK9QdYlF5gT6wU2oP3iA4k5e', 'student', NULL, '10023459', 'Engineering', 'BSc (Hons) Mechatronics Engineering · Year 2', 'Year 2', 'Active', 'Engineering study circle lead specializing in mechanics, circuits, and control systems.', 'Engineering Mechanics, Circuit Analysis, Robotics'),
(6, 'Dinara Jayasinghe', 'dinara.j@nsbm.ac.lk', '$2y$10$eE0mEwK5jBfN9/ZgS2u49.Y6sR1tVbK9QdYlF5gT6wU2oP3iA4k5e', 'student', NULL, '10023460', 'Science', 'BSc (Hons) Biomedical Science · Year 2', 'Year 2', 'Active', 'Science Study Circle coordinator sharing lab report templates and biostatistics guides.', 'Biomedical Science, Laboratory Techniques, Biostatistics'),
(7, 'Sahan Dissanayake', 'sahan.d@nsbm.ac.lk', '$2y$10$eE0mEwK5jBfN9/ZgS2u49.Y6sR1tVbK9QdYlF5gT6wU2oP3iA4k5e', 'student', NULL, '10023461', 'Computing', 'BSc (Hons) Computer Science · Year 3', 'Year 3', 'Active', 'Algorithm enthusiast and competitive programmer.', 'Graph Theory, Dynamic Programming, System Design'),
(8, 'Shenali Perera', 'shenali.p@nsbm.ac.lk', '$2y$10$eE0mEwK5jBfN9/ZgS2u49.Y6sR1tVbK9QdYlF5gT6wU2oP3iA4k5e', 'student', NULL, '10023462', 'Business', 'BSc (Hons) Accounting & Finance · Year 3', 'Year 3', 'Active', 'Business Circle lead hosting exam revision clinics.', 'Corporate Finance, Auditing, Financial Reporting'),
(9, 'Admin Secondary', 'admin@nsbm.ac.lk', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'admin', 'Content moderator', 'ADM002', 'Computing', 'Content Moderation', 'Staff', 'Active', 'Academic content reviewer and moderator.', 'Quality Assurance, Peer Review')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    role = VALUES(role),
    faculty = VALUES(faculty),
    degree_program = VALUES(degree_program),
    status = VALUES(status);

-- 1b. Default Super Administrator (login: admin.nsbmstudycircle@gmail.com / admin@nsbmsc)
--     CHANGE THIS PASSWORD after deploying. INSERT IGNORE never overwrites an existing account.
INSERT IGNORE INTO users (name, email, password, role, admin_type, faculty, degree_program, academic_year, status) VALUES
('System Administrator', 'admin.nsbmstudycircle@gmail.com', '$2y$10$yvaehJfr4R6it5SOa1spCO7V/R0WpzRn7JYxXalolemG25QWRGuGi', 'admin', 'Super administrator', 'Computing', 'Platform Administration', 'Staff', 'Active');

-- 2. Seed Academic Areas
INSERT INTO academic_areas (id, code, name, description, level, status) VALUES
(1, 'COM', 'Computing', 'Faculty of Computing - Software Engineering, Computer Science, AI & Data Science', 'Year 1 - 4', 'Active'),
(2, 'BUS', 'Business', 'Faculty of Business - Business Management, Accounting, Finance & Marketing', 'Year 1 - 4', 'Active'),
(3, 'ENG', 'Engineering', 'Faculty of Engineering - Mechatronics, Electrical, Civil & Mechanical Engineering', 'Year 1 - 4', 'Active'),
(4, 'SCI', 'Science', 'Faculty of Science - Biomedical Science, Applied Chemistry & Biostatistics', 'Year 1 - 4', 'Active')
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- 3. Seed Subjects / Modules
INSERT INTO subjects (code, name, area_id, academic_year, description, status) VALUES
('COM101', 'Intro to Programming', 1, 'Year 1', 'Core programming logic in Python and C', 'Active'),
('COM102', 'Computer Systems', 1, 'Year 1', 'Hardware, architecture and operating systems', 'Active'),
('COM103', 'Algorithms & Logic', 1, 'Year 1', 'Algorithmic thinking and problem solving', 'Active'),
('COM104', 'Web Development', 1, 'Year 1', 'HTML, CSS, JavaScript, and responsive design', 'Active'),
('COM105', 'Discrete Mathematics', 1, 'Year 1', 'Sets, graphs, combinatorics, and proof methods', 'Active'),
('COM106', 'Database Principles', 1, 'Year 1', 'Relational database theory and SQL', 'Active'),
('CS2102', 'Data Structures & Algorithms', 1, 'Year 2', 'Trees, graphs, heaps, sorting, and asymptotic complexity', 'Active'),
('CS2104', 'Database Management Systems', 1, 'Year 2', 'ER mapping, normalization (1NF-BCNF), indexing, and ACID', 'Active'),
('CS2105', 'Database Systems', 1, 'Year 2', 'Normalization, indexing, and transactions', 'Active'),
('CS2208', 'Human Computer Interaction', 1, 'Year 2', 'Usability heuristics, WCAG accessibility, and prototyping', 'Active'),
('CS3105', 'Enterprise Architecture', 1, 'Year 3', 'Microservices, cloud patterns, and distributed systems', 'Active'),
('BUS101', 'Principles of Management', 2, 'Year 1', 'Managerial functions, strategy, and leadership', 'Active'),
('BUS102', 'Financial Accounting', 2, 'Year 1', 'Double-entry bookkeeping and financial statements', 'Active'),
('BUS103', 'Marketing Fundamentals', 2, 'Year 1', 'Market research, consumer behaviour, and 4Ps', 'Active'),
('BUS104', 'Business Economics', 2, 'Year 1', 'Micro and macroeconomics principles', 'Active'),
('BUS105', 'Business Statistics', 2, 'Year 1', 'Probability, hypothesis testing, and regression', 'Active'),
('BUS106', 'Organizational Behaviour', 2, 'Year 1', 'Team dynamics, culture, and motivation', 'Active'),
('BM2204', 'Corporate Finance', 2, 'Year 2', 'WACC, capital budgeting (NPV/IRR), and capital structure', 'Active'),
('ENG101', 'Engineering Mathematics I', 3, 'Year 1', 'Calculus, linear algebra, and complex numbers', 'Active'),
('ENG102', 'Engineering Physics', 3, 'Year 1', 'Mechanics, optics, and thermodynamics', 'Active'),
('ENG103', 'Mechanics of Materials', 3, 'Year 1', 'Stress, strain, torsion, and beam bending', 'Active'),
('ENG104', 'CAD & Drafting Basics', 3, 'Year 1', 'Orthographic projection and 3D modeling', 'Active'),
('ENG105', 'Materials Science', 3, 'Year 1', 'Metals, polymers, ceramics, and material properties', 'Active'),
('ENG106', 'Electrical Circuit Theory', 3, 'Year 1', 'Ohm law, Kirchhoff laws, and AC/DC circuits', 'Active'),
('EN1103', 'Electric Circuits & Signals', 3, 'Year 1', 'Thevenin, Norton, nodal/mesh analysis, and AC response', 'Active'),
('SCI101', 'General Chemistry I', 4, 'Year 1', 'Atomic structure, bonding, and stoichiometry', 'Active'),
('SCI102', 'Cell Biology & Genetics', 4, 'Year 1', 'Cell structure, mitosis, meiosis, and heredity', 'Active'),
('SCI103', 'Applied Statistics', 4, 'Year 1', 'Biostatistics and experimental data analysis', 'Active'),
('SCI104', 'Laboratory Techniques', 4, 'Year 1', 'Spectrophotometry, titration, and microscope work', 'Active'),
('SCI105', 'Physics for Sciences', 4, 'Year 1', 'Kinematics, wave motion, and fluids', 'Active'),
('SCI106', 'Scientific Communication', 4, 'Year 1', 'Scientific writing, citations, and presentations', 'Active')
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- 4. Seed Categories
INSERT INTO categories (name, description) VALUES
('Computing', 'Software engineering, algorithms, networks and systems architecture'),
('Business', 'Management, marketing strategy, economics and organizational behaviour'),
('Engineering', 'Mechanics, electrical circuits, thermodynamics and materials science'),
('Science', 'Physical sciences, chemistry, biomedical and research methodology'),
('Accounting', 'Financial accounting, cash flow, cost management and auditing'),
('Academic Skills', 'Literature reviews, citations, essay structure and exam techniques'),
('Databases', 'SQL querying, relational design, normalization and indexing')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- 5. Seed Resources (Approved + Pending for Admin Review Queue)
INSERT INTO resources (
    id, title, description, faculty, module_code, academic_year, file_path, file_type,
    file_size_mb, page_count, language, downloads, rating, ratings_count, uploaded_by,
    status, about_text, whats_included, preview_badge, preview_heading, preview_subheading
) VALUES
(1, 'Data Structures — Complete Revision Guide', 'Clear summaries, worked examples, and exam-focused notes covering the full module.', 'Computing', 'CS2102', 'Year 2', 'uploads/resources/data_structures_revision_guide.pdf', 'PDF', 5.80, 42, 'English', 1284, 4.9, 38, 3, 'Approved',
 'A structured revision guide covering arrays, linked lists, stacks, queues, trees, graphs, searching, sorting, and algorithm analysis. Each chapter includes concise explanations followed by worked examples and quick-check questions.',
 '["Ten topic summaries aligned to the module outline","Visual diagrams for trees and graph traversal","Twenty-four worked algorithm questions","Final two-page exam checklist"]',
 'DATA STRUCTURES', 'Exam Revision Guide', 'Core concepts, visual explanations, and worked examples for confident revision.'),

(2, 'Financial Accounting: Core Concepts', 'A concise lecture companion with key formulas, journal entries, and practice questions.', 'Business', 'BUS102', 'Year 1', 'uploads/resources/financial_accounting_core_concepts.pdf', 'PDF', 3.40, 28, 'English', 864, 4.8, 24, 4, 'Approved',
 'Covers double-entry bookkeeping, trial balance preparation, profit and loss calculations, balance sheet structures, and cash flow reconciling with end-of-chapter practice examples.',
 '["Step-by-step balance sheet and income statement worksheets","Key ratio formulas cheat sheet","Ten worked cash flow adjustments"]',
 'FINANCIAL ACCOUNTING', 'Core Concepts & Principles', 'A complete primer on transaction recording and financial statement compilation.'),

(3, 'Business Statistics Formula Book', 'A clean reference for probability, regression, and hypothesis testing.', 'Business', 'BUS105', 'Year 1', 'uploads/resources/business_statistics_formula_book.pdf', 'PDF', 1.80, 14, 'English', 518, 4.6, 15, 4, 'Approved',
 'Quick lookup companion containing all critical formulas for standard deviation, normal distributions, confidence intervals, ANOVA tables, and linear regression analysis.',
 '["All first-year statistics formulas organized by topic","Standard normal z-table and t-table reference appendices","Hypothesis test decision tree"]',
 'BUSINESS STATISTICS', 'Formula Reference Book', 'Probability distributions, test statistics, and regression equations.'),

(4, 'Engineering Mechanics — First-Year Guide', 'Core mechanics principles, force diagrams, worked examples, and revision questions.', 'Engineering', 'ENG103', 'Year 1', 'uploads/resources/engineering_mechanics_guide.pdf', 'PDF', 6.20, 50, 'English', 706, 4.7, 19, 5, 'Approved',
 'Statics and dynamics fundamentals covering equilibrium of particles, free-body diagrams, truss analysis, moments of inertia, and kinematics of rigid bodies.',
 '["Detailed method of joints and sections truss examples","Friction and virtual work review sheets","Fifteen fully solved exam problems"]',
 'ENGINEERING MECHANICS', 'Statics & Dynamics Guide', 'Comprehensive force resolution and equilibrium problem walkthroughs.'),

(5, 'Scientific Communication Toolkit', 'Practical frameworks for laboratory records, scientific reports, citations, and presentations.', 'Science', 'SCI106', 'Year 1', 'uploads/resources/lab_report_template.docx', 'DOCX', 1.20, 8, 'English', 621, 4.7, 11, 6, 'Approved',
 'Standard template formatting for introduction, methodology, materials, data tables, error calculations, results discussion, and APA 7th style citations.',
 '["Pre-formatted Microsoft Word style template (.docx)","Plagiarism and citation guidelines checklist","Sample graphs and tabular layout styling"]',
 'SCIENTIFIC COMMUNICATION', 'Lab Report Template', 'Academic report formatting, table guidelines, and citation standards.'),

(6, 'Database Systems — SQL & Normalization Companion', 'Relational database concepts, SQL query exercises, and step-by-step 1NF to BCNF normalization.', 'Computing', 'CS2105', 'Year 2', 'uploads/resources/sql_normalization_companion.pdf', 'PDF', 4.10, 36, 'English', 942, 4.8, 31, 2, 'Approved',
 'A hands-on reference guide covering relational schema design, functional dependencies, 1NF, 2NF, 3NF, BCNF decomposition, and practical complex JOIN and aggregation queries.',
 '["Full normalization walkthrough with 8 realistic scenarios","Fifty practical SQL practice queries with solutions","Transaction ACID properties & indexing tips"]',
 'DATABASE SYSTEMS', 'SQL & Normalization Guide', 'Relational schemas, functional dependencies, and advanced SQL querying.'),

(7, 'Laboratory Skills & Safety Handbook', 'Safety protocols, chemical hazard classifications, and apparatus handling procedures for science labs.', 'Science', 'SCI104', 'Year 1', 'uploads/resources/lab_skills_safety_handbook.pdf', 'PDF', 2.70, 22, 'English', 312, 4.6, 9, 6, 'Approved',
 'Essential handbook for undergraduate students outlining safety protocols, WHMIS/GHS symbols, proper disposal of reagents, emergency procedures, and equipment sterilization.',
 '["Emergency first-aid and eyewash procedures","GHS chemical hazard symbols quick chart","Apparatus calibration guidelines"]',
 'SCIENCE FACULTY', 'Laboratory Skills Handbook', 'Safety protocols, measurement accuracy, and laboratory etiquette.'),

(8, 'UI/UX Heuristics & Wireframing Checklist', 'Practical usability review checklist based on Nielsen 10 heuristics and WCAG 2.1 accessibility standards.', 'Computing', 'CS2208', 'Year 2', 'uploads/resources/ui_ux_heuristics_checklist.pdf', 'PDF', 3.10, 18, 'English', 0, 5.0, 0, 2, 'Pending',
 'Submitted for peer review: covers usability testing scripts, heuristic evaluation severity scales, and Figma component organization.',
 '["10 Usability Heuristics evaluation sheet","WCAG contrast and keyboard navigation checklist"]',
 'HUMAN COMPUTER INTERACTION', 'UI/UX Heuristics Guide', 'Submitted for moderator approval.')
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    faculty = VALUES(faculty),
    module_code = VALUES(module_code),
    downloads = VALUES(downloads),
    rating = VALUES(rating),
    status = VALUES(status);

-- 6. Seed Student Download History (For Alex Silva, user_id = 2)
INSERT INTO resource_downloads (id, resource_id, user_id, downloaded_at) VALUES
(1, 2, 2, NOW()),
(2, 5, 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 1, 2, DATE_SUB(NOW(), INTERVAL 7 DAY))
ON DUPLICATE KEY UPDATE downloaded_at = VALUES(downloaded_at);

-- 7. Seed Saved / Bookmarked Resources (For Alex Silva, user_id = 2)
INSERT IGNORE INTO saved_resources (user_id, resource_id) VALUES
(2, 1),
(2, 2),
(2, 5);

-- 8. Seed Resource Reviews
INSERT INTO resource_reviews (resource_id, user_id, rating, comment) VALUES
(1, 4, 5, 'Excellent structure. The graph traversal diagrams finally made BFS and DFS click for me.'),
(1, 2, 5, 'Saved me so much time before mid-terms. The Big-O cheatsheet is great.'),
(2, 2, 5, 'Very clear ledger examples. Helped me tremendously for mid-terms.'),
(3, 3, 5, 'Compact and easy to revise during exam week.'),
(4, 2, 5, 'The free-body diagram breakdown is second to none.'),
(5, 3, 5, 'Saved me so much time formatting my first biology lab submission.'),
(6, 4, 5, 'The normalization decomposition steps are explained far better than our textbook.'),
(7, 5, 5, 'Must-read before the practical lab exams.')
ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment);

-- 9. Seed Discussions
INSERT INTO discussions (id, title, body, faculty, category, module_code, tags, author_id, upvotes, answers_count, views_count, is_solved, status) VALUES
(1, 'How do you choose between merge sort and quicksort in practice?', 'I understand that both have average O(n log n) time complexity, while quicksort can degrade to O(n²). But when building an actual application, how should memory constraints, data order, and stability influence the choice?', 'Computing', 'Computing', 'CS2102', 'algorithms, sorting, complexity', 2, 18, 3, 126, 1, 'Resolved'),
(2, 'Can someone explain the difference between cash flow and profit?', 'I am revising this topic and would appreciate a simple explanation with a practical example covering non-cash expenses, working capital, and revenue timing.', 'Business', 'Accounting', 'BUS102', 'accounting, cashflow, finance', 4, 15, 8, 98, 1, 'Resolved'),
(3, 'How do you document a reliable laboratory experiment?', 'I understand the theory, but I am unsure which constraints matter most in a real implementation when recording raw data, error propagation, and reagent calibration.', 'Science', 'Science', 'SCI104', 'laboratory, methodology, research', 6, 11, 6, 74, 0, 'Open'),
(4, 'Why does normalization sometimes increase query complexity?', 'I am revising this topic and would appreciate a simple explanation with a practical example of when 3NF/BCNF joins hurt read latency compared to intentional denormalization.', 'Computing', 'Databases', 'CS2105', 'databases, sql, normalization', 3, 9, 4, 62, 0, 'Open'),
(5, 'Best way to structure a literature review for a research proposal?', 'I understand the theory, but I am unsure which constraints matter most in a real implementation when synthesizing thematic versus chronological literature analysis.', 'Computing', 'Academic Skills', 'COM106', 'research, academic-writing, methodology', 5, 7, 3, 53, 0, 'Open'),
(6, 'How do you determine beam deflection under distributed loads?', 'Working through second-order differential equation methods and Macaulay method for simply supported beams with point and uniform loads.', 'Engineering', 'Engineering', 'ENG103', 'mechanics, structures, calculus', 5, 14, 5, 88, 1, 'Resolved')
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    body = VALUES(body),
    faculty = VALUES(faculty),
    category = VALUES(category),
    upvotes = VALUES(upvotes),
    answers_count = VALUES(answers_count);

-- 10. Seed Discussion Answers
INSERT INTO discussion_answers (id, discussion_id, author_id, author_role, body, upvotes, is_solution) VALUES
(1, 1, 7, 'Top contributor · 1h ago', 'In most application code, start with the language standard sort because it is heavily optimized. If you need stability or predictable worst-case behavior, merge sort is easier to reason about. Quicksort is attractive for arrays because it is cache-friendly and usually works in-place.', 12, 1),
(2, 1, 6, 'Student member · 2h ago', 'Memory is the clearest trade-off: classic merge sort needs additional O(n) storage, whereas quicksort can use O(log n) stack space on average. For linked lists, merge sort is often the natural choice because splitting and merging do not require array copies.', 9, 0),
(3, 1, 4, 'Student member · 3h ago', 'Also consider whether equal items must preserve their original order. That stability requirement often matters when sorting records by multiple fields.', 6, 0),
(4, 2, 8, 'Business Circle Lead · 2h ago', 'Profit is an accounting concept (Revenue minus Expenses on an accrual basis, including non-cash depreciation), whereas cash flow tracks actual money entering and leaving your bank account. A profitable company can still run out of cash if customers delay paying invoices!', 14, 1)
ON DUPLICATE KEY UPDATE
    body = VALUES(body),
    upvotes = VALUES(upvotes),
    is_solution = VALUES(is_solution);

-- 11. Seed Discussion Replies
INSERT INTO discussion_replies (id, answer_id, user_id, body) VALUES
(1, 1, 2, 'That makes a lot of sense! So for sorting objects in memory where stability is critical (like names ordered by age), merge sort or TimSort is always preferred.'),
(2, 1, 3, 'Exactly right, Alex. Java and Python both default to TimSort (a hybrid of merge sort and insertion sort) for this exact stability property.')
ON DUPLICATE KEY UPDATE body = VALUES(body);

-- 12. Seed Discussion Votes
INSERT IGNORE INTO discussion_votes (user_id, discussion_id, answer_id) VALUES
(3, 1, NULL),
(4, 1, NULL),
(5, 1, NULL),
(2, NULL, 1),
(4, NULL, 1),
(5, NULL, 2);

-- 13. Seed Study Groups (7 Groups)
INSERT INTO study_groups (id, name, code, category, is_featured, schedule_info, activity_status, description, about_text, what_we_study, created_date_str, badge_color, creator_id, member_count, max_members, status) VALUES
(1, 'Data Structures Circle', 'DS', 'Computing', 1, 'Meets Wednesdays', 'Active this week', 'Weekly problem solving, peer explanations, and focused preparation for algorithms and data structures.', 'We are a focused community for students working through data structures and algorithms. Members meet weekly to compare approaches, practice past-paper questions, and support one another without sharing prohibited assessment answers.', 'Arrays & linked lists, Trees, Graphs, Sorting, Complexity', 'Created February 2025', 'badge-ds', 2, 48, 60, 'Active'),
(2, 'Business Minds', 'BM', 'Business', 0, 'Meets Mondays', 'Active this week', 'Case studies, presentations, and exam preparation for management students.', 'Dedicated peer group for business analytics, organizational behavior, marketing strategies, and financial management presentations.', 'Case Analysis, Marketing, Financial Models, Presentation Skills', 'Created January 2025', 'badge-bm', 4, 35, 50, 'Active'),
(3, 'Engineering Innovators', 'EI', 'Engineering', 0, 'Meets Thursdays', 'Active this week', 'Work through first-year mechanics, mathematics, drawing, and physics together.', 'Engineering fundamentals community breaking down applied calculus, statics, dynamics, electrical circuit analysis, and CAD modelling.', 'Mechanics, Engineering Mathematics, Circuit Analysis, Physics', 'Created January 2025', 'badge-ei', 5, 32, 45, 'Active'),
(4, 'Science Study Circle', 'SC', 'Science', 0, 'Meets Tuesdays', 'Active this week', 'Collaborate on laboratory work, scientific reports, and core science subjects.', 'Supporting students in experimental design, lab data evaluation, statistical analysis, and comprehensive report preparation.', 'Lab Analysis, Report Writing, Organic Chemistry, Experimental Physics', 'Created February 2025', 'badge-sc', 6, 27, 40, 'Active'),
(5, 'AI & Machine Learning Hub', 'AI', 'Computing', 0, 'Meets Fridays', 'Active this week', 'Paper discussions, practical experiments, and project support.', 'Exploring deep learning architectures, Python ML tooling, computer vision, natural language processing, and research paper replication.', 'PyTorch, Computer Vision, Transformers, Model Fine-tuning, Math for ML', 'Created December 2024', 'badge-ai', 2, 62, 80, 'Active'),
(6, 'Finance Network', 'FN', 'Business', 0, 'Meets Saturdays', 'Active this week', 'Collaborative revision for finance and investment modules.', 'Hands-on revision sessions covering portfolio management, corporate finance, equity evaluation, and risk modeling.', 'Corporate Finance, Investment Valuation, Risk Analysis, Derivatives', 'Created February 2025', 'badge-fn', 4, 31, 45, 'Active'),
(7, 'Research Studio', 'RS', 'Science', 0, 'Meets Sundays', 'Active this week', 'Accountability and peer feedback for final-year research.', 'Structured peer critique, methodology review, literature mapping, and publication preparation for undergraduate thesis candidates.', 'Methodology, Literature Review, Academic Writing, Data Visualization', 'Created March 2025', 'badge-rs', 6, 24, 35, 'Active')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    code = VALUES(code),
    category = VALUES(category),
    description = VALUES(description),
    about_text = VALUES(about_text),
    what_we_study = VALUES(what_we_study),
    member_count = VALUES(member_count);

-- 14. Seed Study Group Members
INSERT IGNORE INTO study_group_members (group_id, user_id) VALUES
(1, 2), (1, 3), (1, 7),
(2, 2), (2, 4), (2, 8),
(3, 5),
(4, 2), (4, 6),
(5, 2), (5, 3), (5, 7);

-- 15. Seed Study Group Coordinators
DELETE FROM study_group_coordinators WHERE group_id IN (1, 2, 3, 4);
INSERT INTO study_group_coordinators (group_id, user_id, name, initials, role_title) VALUES
(1, 3, 'Nethmi Perera', 'NP', 'Coordinator'),
(1, 7, 'Sahan Dissanayake', 'SD', 'Coordinator'),
(2, 4, 'Kavindu Silva', 'KS', 'Coordinator'),
(2, 8, 'Shenali Perera', 'SP', 'Coordinator'),
(3, 5, 'Ravindu Mendis', 'RM', 'Coordinator'),
(4, 6, 'Dinara Jayasinghe', 'DJ', 'Coordinator');

-- 16. Seed Study Group Activities
DELETE FROM study_group_activities WHERE group_id = 1;
INSERT INTO study_group_activities (group_id, user_id, user_name, user_initials, action_text, relative_time, link_url) VALUES
(1, 3, 'Nethmi Perera', 'NP', 'shared a revision checklist', '35 minutes ago', 'discussions.php'),
(1, 4, 'Kavindu Silva', 'KS', 'asked about AVL rotations', '2 hours ago', 'discussions.php'),
(1, NULL, 'Amaya Fernando', 'AF', 'scheduled a graph traversal session', 'Yesterday', 'sessions.php');

-- 17. Seed Study Sessions
INSERT INTO study_sessions (id, title, description, module_code, faculty, host_id, session_date, duration_mins, format_type, location_details, capacity, enrolled_count, status) VALUES
(1, 'Algorithms: Final Exam Problem Solving', 'A focused ninety-minute problem solving session working through algorithm analysis, asymptotic complexity, tree balance rotations, and graph traversal problems from past semester papers.', 'CS2102', 'Computing', 7, '2027-05-24 18:30:00', 90, 'Online', 'Online · Microsoft Teams', 24, 18, 'Scheduled'),
(2, 'Marketing Strategy Case Review', 'Interactive pitch practice, consumer segmentation analysis, and SWOT matrix breakdown for first and second year business modules.', 'BUS103', 'Business', 4, '2027-05-26 16:00:00', 90, 'Online', 'Online · Zoom Session', 20, 14, 'Scheduled'),
(3, 'Laboratory Report Review', 'Peer-review session on biostatistics presentation, experimental error analysis, and APA citation standards.', 'SCI106', 'Science', 6, '2027-05-28 17:30:00', 90, 'In-person', 'Science Laboratory 2', 16, 12, 'Scheduled'),
(4, 'Graph Traversal Practice', 'Comprehensive practice on BFS, DFS, Dijkstra, and cycle detection algorithms.', 'CS2102', 'Computing', 2, '2027-05-29 18:30:00', 90, 'Online', 'Saturday · 6:30 PM · Online', 30, 22, 'Scheduled')
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    session_date = VALUES(session_date),
    enrolled_count = VALUES(enrolled_count);

-- 18. Seed Study Session Attendees
INSERT IGNORE INTO study_session_attendees (session_id, user_id) VALUES
(1, 2), (1, 3), (2, 2), (3, 2);

-- 19. Seed Student Notifications (For Alex Silva, user_id = 2)
INSERT INTO notifications (id, user_id, title, message, category, link_url, is_read) VALUES
(1, 2, 'Upload approved', 'Your Database Systems — SQL & Normalization Companion is now public in the library.', 'upload', 'uploads.php', 0),
(2, 2, 'New discussion reply', 'Sahan Dissanayake replied to your question about merge sort vs quicksort.', 'discussion', 'discussions.php', 0),
(3, 2, 'Study group update', 'Nethmi Perera shared a new revision checklist in Data Structures Circle.', 'group', 'groups.php', 0),
(4, 2, 'Upcoming study session reminder', 'Algorithms: Final Exam Problem Solving starts at 6:30 PM on Microsoft Teams.', 'session', 'schedule.php', 0)
ON DUPLICATE KEY UPDATE title = VALUES(title), message = VALUES(message);

-- 20. Seed Reported Content (For Admin Moderation Console)
INSERT INTO reported_content (id, content_snippet, target_type, target_id, reason, priority, status, reported_by) VALUES
(1, 'Uploaded solution sheet contains live graded coursework questions from current semester.', 'Resource', 8, 'Academic integrity', 'High priority', 'Open', 3),
(2, 'Repeated promotional links posted inside exam revision thread.', 'Answer', 3, 'Spam', 'Medium', 'Open', 4),
(3, 'Duplicate question thread already answered in Computing category.', 'Discussion', 4, 'Duplicate', 'Low', 'Resolved', 2)
ON DUPLICATE KEY UPDATE content_snippet = VALUES(content_snippet), status = VALUES(status);

-- 21. Seed Platform Settings
INSERT INTO platform_settings (setting_key, setting_value) VALUES
('platform_name', 'NSBM Study Circle'),
('platform_tagline', 'Learn Together. Grow Together.'),
('support_email', 'support@nsbm.ac.lk'),
('require_resource_approval', '1'),
('max_upload_size_mb', '25'),
('allow_student_session_creation', '1')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- 22. Seed Announcements
INSERT INTO announcements (id, title, content, faculty, created_by, is_pinned) VALUES
(1, 'Mid-Semester Exam Revision Week Schedule', 'Join faculty-led and peer-hosted revision clinics across Computing, Business, Engineering, and Science starting next Monday.', 'All', 1, 1),
(2, 'New Data Structures & DBMS Past Paper Pack Uploaded', 'Updated model answers and complexity cheatsheets are now available in the Computing Resource Library.', 'Computing', 1, 0)
ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content);

-- 23. Seed Events
INSERT INTO events (id, title, description, event_date, venue, faculty, created_by) VALUES
(1, 'NSBM Annual Peer Learning & Research Symposium', 'Showcase of student-led research studio papers, study group projects, and top academic contributors.', '2027-06-15 09:30:00', 'NSBM Main Auditorium', 'All', 1),
(2, 'Hackathon & Algorithm Problem-Solving Bootcamp', 'Intensive 3-hour collaborative coding and algorithm optimization clinic.', '2027-06-20 14:00:00', 'Faculty of Computing Lab 4', 'Computing', 1)
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description);

-- 24. Seed Contact Messages
INSERT INTO contact_messages (id, name, email, topic, subject, message, status) VALUES
(1, 'Tharindu Jayasuriya', 'tharindu.j@nsbm.ac.lk', 'Resource contribution', 'Uploading multi-part lecture slides', 'Can we upload a ZIP archive containing both PDF notes and sample SQL scripts for CS2104?', 'Resolved'),
(2, 'Malsha Wickramasinghe', 'malsha.w@nsbm.ac.lk', 'Study groups', 'Starting a new Year 1 Mathematics Circle', 'I would like to register a new weekly peer group for Engineering Mathematics I.', 'New')
ON DUPLICATE KEY UPDATE subject = VALUES(subject), status = VALUES(status);
