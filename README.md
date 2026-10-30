# 🎓 NSBM StudyCircle

**A Student Community Platform for Notes, Knowledge Sharing, and Collaborative Learning.**

NSBM StudyCircle is a web-based student collaboration platform designed to help university students share study materials, exchange knowledge, ask academic questions, and collaborate with fellow students. The platform provides a centralized space where students can access learning resources, participate in discussions, form study groups, and organize study sessions.

## 📌 Project Overview

University students often face difficulties finding study materials, sharing lecture notes, and collaborating with other students in one place. NSBM StudyCircle aims to address these challenges by providing an organized and user-friendly platform for academic collaboration.

### 🎯 Project Objectives

* Provide a centralized platform for sharing study materials and lecture notes.
* Allow students to ask academic questions and share knowledge.
* Support collaborative learning through study groups.
* Enable students to schedule and organize study sessions.
* Make learning resources easier to discover through search and filtering.
* Provide administrators with tools to manage users and platform content.

## ✨ Key Features

* **🔐 User Authentication** – Secure registration and login.
* **📚 Study Material Sharing** – Upload and access academic resources.
* **💬 Academic Q&A** – Ask questions and share answers.
* **👥 Study Groups** – Create or join groups for collaborative learning.
* **📅 Study Session Scheduling** – Organize group study sessions.
* **🔎 Search and Filtering** – Find relevant study materials efficiently.
* **🛠️ Admin Dashboard** – Manage users and platform content.
* **📱 Responsive Design** – Support desktop, tablet, and mobile screens.

*Note: Features will be implemented according to the final project requirements and development progress.*

## 🛠️ Technologies Used

| Technology  | Purpose                              |
| ----------- | ------------------------------------ |
| HTML5       | Structure of web pages               |
| CSS3        | Styling and layout                   |
| JavaScript  | Client-side interactivity            |
| PHP         | Server-side development              |
| MySQL       | Database management                  |
| Bootstrap 5 | Responsive user interface components |

## 👤 User Roles

### 1. Student

* Register and log in to the platform.
* Browse and search study materials.
* Share academic resources.
* Ask and answer academic questions.
* Create or join study groups.
* View and organize study sessions.

### 2. Administrator

* Manage student accounts.
* Monitor uploaded study materials.
* Manage platform content.
* Maintain the quality and proper use of the platform.

## 🗂️ Project Structure

```text
NSBM-StudyCircle/
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── config/
│   └── database.php
│
├── includes/
│
├── admin/
│
├── student/
│
├── uploads/
│
├── index.php
├── login.php
├── register.php
├── README.md
└── .gitignore
```

*The directory structure may change as development progresses.*

## ⚙️ Installation and Setup

Follow these steps to run the project locally.

### Prerequisites

* XAMPP or another compatible PHP development environment.
* PHP.
* MySQL.
* A modern web browser.
* Git (optional, for cloning the repository).

### Step 1: Clone The Repository

```bash
git clone https://github.com/YOUR-USERNAME/NSBM-StudyCircle.git
```

### Step 2: Move the Project

Place the project folder inside the XAMPP `htdocs` directory.

### Step 3: Configure the Database

1. Start Apache and MySQL using the XAMPP Control Panel.
2. Open phpMyAdmin in your browser.
3. Create a database for the project.
4. Import the project's SQL file if one is provided.
5. Update the database connection settings in the configuration file.

### Step 4: Run the Application

Open the following address in your browser:

```text
http://localhost/NSBM-StudyCircle/
```

*Setup instructions may be updated to match the final project structure and database configuration.*

## 🔒 Security Considerations

* Validate user inputs on both the client and server sides.
* Use password hashing for stored passwords.
* Use prepared statements for database queries.
* Apply role-based access control.
* Restrict access to administrative features.
* Validate uploaded files and apply appropriate file-size limits.

## 🚀 Future Improvements

* Notifications for new questions and study sessions.
* Student profiles and academic interests.
* Bookmarking useful study materials.
* Rating and feedback for shared resources.
* Advanced search by subject, module, or resource type.
* Integration of AI-powered study assistance.

## 🎓 Academic Project

This project is developed as part of the **Web and Mobile Application Development** module at **NSBM Green University**.

**Project Name:** NSBM StudyCircle
**Project Type:** Web Application
**Target Users:** University Students and Administrators
**Development Stack:** HTML, CSS, JavaScript, PHP, and MySQL

## 👨‍💻 Contributors

This project is developed collaboratively by our student team.

* Add team member names and GitHub profiles here.

## 📄 License

This project is developed for academic purposes. Licensing and reuse permissions can be specified by the project team.

---

**NSBM StudyCircle — Learn Together. Share Knowledge. Grow Together.**
