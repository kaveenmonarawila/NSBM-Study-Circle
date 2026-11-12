# Database changes

Every schema or data change made **after the first deployment** goes into a new,
numbered SQL file in this folder. Never edit a file that has already been run.

```
001_default_admin.sql
002_add_xyz.sql        <- next change
003_...
```

## Rules

1. **One change = one new file**, named `NNN_short_description.sql` (three digits, increasing).
2. Plain SQL only, so it imports on InfinityFree. **Do not use** `CREATE DATABASE`, `USE`,
   `CREATE USER`, `GRANT`, `DEFINER=`, triggers, stored procedures or events.
3. Prefer statements that are safe to run twice:
   `CREATE TABLE IF NOT EXISTS`, `INSERT IGNORE`,
   `ALTER TABLE ... ADD COLUMN IF NOT EXISTS ...` (MariaDB, which both XAMPP and InfinityFree use).
4. Keep the file UTF-8 encoded.

## How to apply a change (by hand, on both databases)

1. **XAMPP:** open http://localhost/phpmyadmin, select `nsbm_student_circle`,
   go to the **Import** tab (or paste into the **SQL** tab), and run the file.
   Test the site locally.
2. **InfinityFree:** Control Panel -> **MySQL Databases** -> **phpMyAdmin** next to
   your `if0_XXXXXXXX_...` database -> **Import** the same file.
3. Also fold the change into `backend/database/nsbm_student_circle.sql` and
   `deploy-infinityfree/nsbm_infinityfree.sql`, so a fresh install gets the full schema.
4. Commit the new file together with the code that needs it.

InfinityFree keeps **no backups**: before a risky change, use phpMyAdmin
**Export** (Quick, SQL) on the live database and keep the file somewhere safe.

This folder is never uploaded to the web server (it is excluded by
`.github/workflows/deploy.yml` and `deploy-infinityfree/build.ps1`).
