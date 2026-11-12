<?php
// ============================================================================
// NSBM STUDY CIRCLE - ONLINE DATABASE CREDENTIALS (TEMPLATE)
// ----------------------------------------------------------------------------
// 1. Copy this file to `db_credentials.php` in the same folder.
// 2. Fill in the values from InfinityFree:
//      Control Panel -> MySQL Databases  (host, database name, username)
//      Client Area   -> Account -> "Show/Hide" password  (the MySQL password
//                       is the same as your hosting account / vPanel password)
// 3. Upload db_credentials.php ONCE with FileZilla to /htdocs/backend/config/
//
// db_credentials.php is listed in .gitignore and is never deployed by GitHub
// Actions, so your real password never ends up in git.
// On localhost / XAMPP this file is ignored and the local root@127.0.0.1
// settings in db_config.php are used.
// ============================================================================

return [
    'host' => 'sqlXXX.infinityfree.com',   // never 'localhost' on InfinityFree
    'port' => 3306,
    'name' => 'if0_XXXXXXXX_nsbm_student_circle',
    'user' => 'if0_XXXXXXXX',
    'pass' => 'YOUR_VPANEL_PASSWORD',
];
