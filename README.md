# NSBM Student Circle

A collaborative web platform where NSBM students share study materials, discuss academic questions, form study groups and schedule sessions, with an admin panel for moderation and reporting.

Built for the Web and Mobile Application Development final project, Faculty of Computing, NSBM Green University.

| | |
|---|---|
| Live site | https://studycirclelk.free.nf |
| Repository | https://github.com/kaveenmonarawila/NSBM-Study-Circle |

## Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Database](#database)
- [Getting Started](#getting-started)
- [Deployment](#deployment)

## Overview

Student Circle gives each faculty a single place to share lecture notes, get answers from peers and organise group study. The system has two separate interfaces:

- **Student portal** for uploading and downloading materials, joining discussions, and managing study groups and sessions.
- **Admin panel** for approving content, managing users and subjects, moderating the forum and viewing engagement reports.

## Features

### Student portal

| Area | Capabilities |
|---|---|
| Account | Register, log in, edit profile, notification and privacy settings |
| Study materials | Upload notes (PDF, DOCX), browse and filter by subject, download, save for later |
| Feedback | Rate and review shared resources |
| Discussions | Ask questions, post answers and replies, vote |
| Study groups | Create or join groups, view members and activity |
| Study sessions | Schedule group sessions, join and view upcoming sessions |
| Dashboard | Schedule, notifications, uploads, downloads and saved items |

### Admin panel

| Area | Capabilities |
|---|---|
| Users | Create, view, edit, deactivate and delete users |
| Approvals | Approve or reject uploaded resources |
| Academic data | Manage subjects, categories and academic areas |
| Moderation | Review discussions and reported content |
| Content | Manage announcements and events |
| Reports | Resource usage and engagement reports |
| Settings | Platform-wide settings |

All modules support full CRUD operations with server-side validation.

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Backend | PHP (MVC-style: controllers, models, helpers) |
| Database | MySQL / MariaDB, accessed through MySQLi and PDO |
| Local environment | XAMPP (Apache, MySQL) |
| Hosting | InfinityFree |

## Project Structure

```
.
├── index.php
├── backend/
│   ├── config/         Application and database configuration
│   ├── controllers/    Request handling (auth, admin, student, resources, discussions, groups, events)
│   ├── models/         Data layer (User, Resource, Discussion, StudyGroup, StudySession, Event, Announcement)
│   ├── helpers/        Session, upload and validation helpers
│   ├── database/       SQL schema and migration scripts
│   └── uploads/        User-uploaded files
├── frontend/
│   ├── admin/          Admin panel (pages, includes, assets)
│   ├── student/        Student portal (pages, includes, assets)
│   ├── assets/         Shared CSS, JS, images and Bootstrap
│   ├── includes/       Shared layout components
│   └── *.php           Public pages (home, about, contact, login, signup, resources, discussions, groups, sessions)
└── uploads/            Sample study materials
```

## Database

Database name: `nsbm_student_circle` (25 tables). The schema and sample data are in [`backend/database/nsbm_student_circle.sql`](backend/database/nsbm_student_circle.sql).

| Module | Tables |
|---|---|
| Users and access | `users`, `login_logs`, `platform_settings` |
| Academic structure | `academic_areas`, `subjects`, `categories` |
| Resources | `resources`, `resource_downloads`, `resource_reviews`, `saved_resources` |
| Discussions | `discussions`, `discussion_answers`, `discussion_replies`, `discussion_votes` |
| Study groups | `study_groups`, `study_group_members`, `study_group_coordinators`, `study_group_activities` |
| Study sessions | `study_sessions`, `study_session_attendees` |
| Communication | `announcements`, `events`, `notifications`, `contact_messages`, `reported_content` |

## Getting Started

### Prerequisites

- XAMPP (or any Apache, PHP and MySQL stack)
- PHP 7.4 or later
- A web browser

### Installation

1. Clone the repository into your web root.

   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/kaveenmonarawila/NSBM-Study-Circle.git nsbm-student-circle
   ```

2. Start **Apache** and **MySQL** from the XAMPP control panel.

3. Create the database. Either:
   - open `http://localhost/phpmyadmin`, create a database named `nsbm_student_circle` and import `backend/database/nsbm_student_circle.sql`, or
   - open the site once. If the database does not exist, `db_config.php` creates it and imports the SQL file automatically.

4. Review the configuration files.

   `backend/config/db_config.php` (defaults for XAMPP)

   ```php
   DB_HOST = '127.0.0.1'
   DB_PORT = 3306
   DB_USER = 'root'
   DB_PASS = ''
   DB_NAME = 'nsbm_student_circle'
   ```

   `backend/config/app_config.php`

   ```php
   SITE_URL = 'http://localhost/nsbm-student-circle'
   ```

5. Open `http://localhost/nsbm-student-circle`.

To verify the database connection, visit `http://localhost/nsbm-student-circle/backend/config/test_db_connection.php`. It returns the connection status, table list and row counts as JSON.

## Deployment

The live site runs on InfinityFree.

1. Create a MySQL database in the hosting control panel, open phpMyAdmin for it and import `database/infinityfree_import.sql`.
2. Copy `backend/config/db_credentials.example.php` to `backend/config/db_credentials.php`, fill in the host, port, database, user and password from the panel, and upload it once by FTP to `/htdocs/backend/config/`. This file is gitignored and is never deployed by GitHub Actions.
3. Add the `FTP_SERVER`, `FTP_USERNAME` and `FTP_PASSWORD` repository secrets. Every push to `main` then uploads the site to `/htdocs/` via `.github/workflows/deploy.yml`. If FTP from GitHub fails, run `deploy-infinityfree/build.ps1` and upload `deploy-infinityfree/htdocs/` with FileZilla.

`SITE_URL` is detected from the request automatically, so no URL needs to be configured. Hosting credentials should only exist on the server and should not be committed to the repository.

## Academic Context

Module: Web and Mobile Application Development  
Faculty of Computing, NSBM Green University
