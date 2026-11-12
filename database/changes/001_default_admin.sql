-- ============================================================================
-- 001 - Ensure the default Super Administrator exists in the `users` table
-- ----------------------------------------------------------------------------
-- Why: the admin login used to be hard-coded in auth_controller.php (and reset
-- the password on every login). It now uses the normal `users` table, so the
-- account must exist in the database.
--
-- Run once in phpMyAdmin on XAMPP (database nsbm_student_circle).
-- NOT needed on InfinityFree if you imported deploy-infinityfree/nsbm_infinityfree.sql
-- (it already contains this row) - running it again is harmless.
--
-- Default login: admin.nsbmstudycircle@gmail.com / admin@nsbmsc  -> CHANGE IT.
-- INSERT IGNORE never overwrites an existing account or a changed password.
-- ============================================================================

INSERT IGNORE INTO users (name, email, password, role, admin_type, faculty, degree_program, academic_year, status) VALUES
('System Administrator', 'admin.nsbmstudycircle@gmail.com', '$2y$10$yvaehJfr4R6it5SOa1spCO7V/R0WpzRn7JYxXalolemG25QWRGuGi', 'admin', 'Super administrator', 'Computing', 'Platform Administration', 'Staff', 'Active');
