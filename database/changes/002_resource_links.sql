-- ============================================================================
-- 002 - Study materials become Google Drive / OneDrive links + admin approval
-- ----------------------------------------------------------------------------
-- Run ONLY on a database that was imported BEFORE this change
-- (database/infinityfree_import.sql already contains it - do not run it there).
-- phpMyAdmin -> select your database -> SQL tab (or Import) -> run this file.
--
-- What it does:
--   1. resources.external_url  : new column for the share link (max 500 chars)
--   2. resources.file_path     : may now be NULL (new resources have no file)
--   3. resources.status        : new rows default to 'Pending' (admin must approve)
-- Existing resources and their files are NOT changed: old server files stay
-- downloadable read-only, and already-approved resources stay approved.
-- Safe to run twice (MariaDB "IF NOT EXISTS"; MODIFY just re-applies).
-- ============================================================================

ALTER TABLE resources
    ADD COLUMN IF NOT EXISTS external_url VARCHAR(500) NULL AFTER file_path;

ALTER TABLE resources
    MODIFY file_path VARCHAR(255) NULL;

ALTER TABLE resources
    MODIFY status ENUM('Pending', 'Approved', 'Rejected', 'Archived') DEFAULT 'Pending';
