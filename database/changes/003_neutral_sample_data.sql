-- ============================================================================
-- 003 - Neutral branding in sample data + only one active administrator
-- ----------------------------------------------------------------------------
-- Run ONLY on a database that was imported BEFORE this change, and AFTER 002
-- (database/infinityfree_import.sql already contains it - do not run it there).
--
-- What it does:
--   1. users.profile_visibility: the value 'NSBM Study Circle members' becomes
--      the brand-neutral 'Members only' (the page shows it as "<site name> members").
--   2. The 7 SAMPLE student accounts move from @nsbm.ac.lk to @example.com.
--      Only these exact sample addresses are changed - real users who signed
--      up with their own address are never touched.
--   3. The sample admins admin@nsbm.lk and admin@nsbm.ac.lk are made inactive:
--      demoted to student, Suspended, unusable password, @example.com address.
--      (They are NOT deleted: deleting would cascade and remove the sample
--      announcements, events and other content linked to them.)
--      admin.nsbmstudycircle@gmail.com stays the only administrator.
--   4. Sample platform settings and contact messages use neutral values.
-- Safe to run twice.
-- ============================================================================

-- 1. profile_visibility: add the new value, move rows over, drop the old value
ALTER TABLE users
    MODIFY profile_visibility ENUM('NSBM Study Circle members', 'Members only', 'Public', 'Private') DEFAULT 'Members only';
UPDATE users SET profile_visibility = 'Members only' WHERE profile_visibility = 'NSBM Study Circle members';
ALTER TABLE users
    MODIFY profile_visibility ENUM('Members only', 'Public', 'Private') DEFAULT 'Members only';

-- 2. Sample student accounts -> @example.com (exact sample addresses only)
UPDATE users
SET email = REPLACE(email, '@nsbm.ac.lk', '@example.com')
WHERE email IN (
    'alex.silva@nsbm.ac.lk', 'nethmi.perera@nsbm.ac.lk', 'kavindu.silva@nsbm.ac.lk',
    'ravindu.m@nsbm.ac.lk', 'dinara.j@nsbm.ac.lk', 'sahan.d@nsbm.ac.lk', 'shenali.p@nsbm.ac.lk'
);

-- 3. Deactivate the two sample admins
UPDATE users
SET name = 'Former Sample Admin 1 (inactive)', email = 'former-admin1@example.com',
    password = '!disabled-sample-account', role = 'student', admin_type = NULL,
    status = 'Suspended', bio = 'Inactive sample account. Cannot log in.'
WHERE email = 'admin@nsbm.lk';

UPDATE users
SET name = 'Former Sample Admin 2 (inactive)', email = 'former-admin2@example.com',
    password = '!disabled-sample-account', role = 'student', admin_type = NULL,
    status = 'Suspended', bio = 'Inactive sample account. Cannot log in.'
WHERE email = 'admin@nsbm.ac.lk';

-- 4. Sample settings and contact messages
UPDATE platform_settings SET setting_value = 'Study Circle'
WHERE setting_key = 'platform_name' AND setting_value = 'NSBM Study Circle';
UPDATE platform_settings SET setting_value = 'support@example.com'
WHERE setting_key = 'support_email' AND setting_value = 'support@nsbm.ac.lk';

UPDATE contact_messages
SET email = REPLACE(email, '@nsbm.ac.lk', '@example.com')
WHERE email IN ('tharindu.j@nsbm.ac.lk', 'malsha.w@nsbm.ac.lk');

-- Check: this should list exactly one row (admin.nsbmstudycircle@gmail.com)
SELECT id, name, email, role, status FROM users WHERE role = 'admin';
