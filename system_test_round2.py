# ==============================================================================
# NSBM STUDY CIRCLE - ROUND 2 COMPLETE SYSTEM AUTOMATED TEST SUITE
# Covers all 14 Real User Scenarios: Students, Admins, Security, RBAC & Privacy
# ==============================================================================

import urllib.request, urllib.parse, json, http.cookiejar, sys, time

BASE_URL = 'http://127.0.0.1:8080'

results = []

def record(test_num, title, expected, actual, passed):
    status = "PASS" if passed else "FAIL"
    results.append({
        "num": test_num,
        "title": title,
        "expected": expected,
        "actual": actual,
        "status": status
    })
    print(f"[{status}] Test {test_num}: {title}\n  Expected: {expected}\n  Actual:   {actual}\n")

# Custom redirect handler that captures HTTP 302 responses without following automatically
class NoRedirectHandler(urllib.request.HTTPRedirectHandler):
    def http_error_302(self, req, fp, code, msg, headers):
        return fp
    http_error_301 = http_error_302
    http_error_303 = http_error_302
    http_error_307 = http_error_302

def make_session():
    cj = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj), NoRedirectHandler)
    return opener, cj

# ==============================================================================
# TEST 1: Register Student A with new email & password
# ==============================================================================
student_a_email = f"student.a_{int(time.time())}@nsbm.ac.lk"
student_a_pass = "SecurePass123!"
student_a_name = "Nimal Perera"

opener_a, cj_a = make_session()
reg_data_a = urllib.parse.urlencode({
    'action': 'register',
    'name': student_a_name,
    'email': student_a_email,
    'password': student_a_pass,
    'faculty': 'Computing',
    'degree_program': 'BSc (Hons) Computer Science',
    'academic_year': 'Year 2'
}).encode('utf-8')

try:
    resp = opener_a.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/auth_controller.php?format=json", data=reg_data_a))
    res_json = json.loads(resp.read().decode('utf-8'))
    t1_pass = res_json.get('success') is True and res_json.get('user', {}).get('email') == student_a_email
    student_a_id = res_json.get('user', {}).get('id')
    record(1, "Register Student A with New Credentials", "Account registered in MySQL and returned success: true", f"Success={res_json.get('success')}, User ID={student_a_id}, Msg={res_json.get('message')}", t1_pass)
except Exception as e:
    record(1, "Register Student A with New Credentials", "Account registered in MySQL", f"Exception: {e}", False)

# ==============================================================================
# TEST 2: Log out and log in again using Student A's credentials
# ==============================================================================
try:
    # Logout
    logout_resp = opener_a.open(urllib.request.Request(f"{BASE_URL}/frontend/logout.php"))
    
    # Fresh session for login
    opener_a_login, cj_a_login = make_session()
    login_data_a = urllib.parse.urlencode({
        'action': 'login',
        'email': student_a_email,
        'password': student_a_pass,
        'role': 'student'
    }).encode('utf-8')
    resp = opener_a_login.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/auth_controller.php?format=json", data=login_data_a))
    login_json = json.loads(resp.read().decode('utf-8'))
    
    # Access dashboard
    dash_resp = opener_a_login.open(urllib.request.Request(f"{BASE_URL}/frontend/student/index.php"))
    dash_html = dash_resp.read().decode('utf-8')
    welcome_found = "Welcome back, Nimal!" in dash_html
    
    t2_pass = login_json.get('success') is True and welcome_found
    record(2, "Student A Logout & Login Re-authentication", "Successful re-authentication and access to dashboard with greeting 'Welcome back, Nimal!'", f"Login success={login_json.get('success')}, Dashboard greeted Nimal={welcome_found}", t2_pass)
except Exception as e:
    record(2, "Student A Logout & Login Re-authentication", "Successful re-authentication", f"Exception: {e}", False)

# ==============================================================================
# TEST 3: Register Student B with a different email and password
# ==============================================================================
student_b_email = f"student.b_{int(time.time())}@nsbm.ac.lk"
student_b_pass = "StudentBPass456!"
student_b_name = "Kamal Gunaratne"

opener_b, cj_b = make_session()
reg_data_b = urllib.parse.urlencode({
    'action': 'register',
    'name': student_b_name,
    'email': student_b_email,
    'password': student_b_pass,
    'faculty': 'Business',
    'degree_program': 'BSc (Hons) Business Management',
    'academic_year': 'Year 1'
}).encode('utf-8')

try:
    resp = opener_b.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/auth_controller.php?format=json", data=reg_data_b))
    res_json_b = json.loads(resp.read().decode('utf-8'))
    student_b_id = res_json_b.get('user', {}).get('id')
    t3_pass = res_json_b.get('success') is True and student_b_id != student_a_id
    record(3, "Register Student B with Separate Credentials", "Separate Student B account registered successfully", f"Success={res_json_b.get('success')}, User ID={student_b_id}, Email={res_json_b.get('user', {}).get('email')}", t3_pass)
except Exception as e:
    record(3, "Register Student B with Separate Credentials", "Separate Student B account registered", f"Exception: {e}", False)

# ==============================================================================
# TEST 4: Verify Student A and B have separate accounts & personal dashboard data
# ==============================================================================
try:
    # Fetch Student A dashboard
    dash_a = opener_a_login.open(urllib.request.Request(f"{BASE_URL}/frontend/student/index.php")).read().decode('utf-8')
    # Fetch Student B dashboard
    dash_b = opener_b.open(urllib.request.Request(f"{BASE_URL}/frontend/student/index.php")).read().decode('utf-8')
    
    a_has_nimal = "Welcome back, Nimal!" in dash_a and "Kamal" not in dash_a
    b_has_kamal = "Welcome back, Kamal!" in dash_b and "Nimal" not in dash_b
    
    t4_pass = a_has_nimal and b_has_kamal and (student_a_id != student_b_id)
    record(4, "Account Isolation Between Student A and Student B", "Distinct identities, faculty, and greetings rendered without cross-contamination", f"Student A has Nimal (no Kamal)={a_has_nimal}, Student B has Kamal (no Nimal)={b_has_kamal}, Distinct IDs ({student_a_id} != {student_b_id})", t4_pass)
except Exception as e:
    record(4, "Account Isolation Between Student A and Student B", "Distinct personal accounts", f"Exception: {e}", False)

# ==============================================================================
# TEST 5: Try logging in with an incorrect password -> Denied
# ==============================================================================
try:
    bad_opener, _ = make_session()
    bad_login_data = urllib.parse.urlencode({
        'action': 'login',
        'email': student_a_email,
        'password': 'TotallyWrongPassword999!',
        'role': 'student'
    }).encode('utf-8')
    resp = bad_opener.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/auth_controller.php?format=json", data=bad_login_data))
    bad_json = json.loads(resp.read().decode('utf-8'))
    t5_pass = bad_json.get('success') is False and "invalid" in bad_json.get('message', '').lower()
    record(5, "Incorrect Password Access Denial", "Login rejected with success: false and error message", f"Success={bad_json.get('success')}, Message='{bad_json.get('message')}'", t5_pass)
except Exception as e:
    record(5, "Incorrect Password Access Denial", "Login rejected", f"Exception: {e}", False)

# ==============================================================================
# TEST 6: Duplicate registration prevention
# ==============================================================================
try:
    dup_opener, _ = make_session()
    dup_data = urllib.parse.urlencode({
        'action': 'register',
        'name': "Duplicate Attempter",
        'email': student_a_email, # already used by student A
        'password': 'SomeOtherPassword123!',
        'faculty': 'Engineering'
    }).encode('utf-8')
    resp = dup_opener.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/auth_controller.php?format=json", data=dup_data))
    dup_json = json.loads(resp.read().decode('utf-8'))
    t6_pass = dup_json.get('success') is False and ("already exists" in dup_json.get('message', '').lower() or "duplicate" in dup_json.get('message', '').lower())
    record(6, "Duplicate Email Registration Prevention", "Registration rejected with duplicate email notice", f"Success={dup_json.get('success')}, Message='{dup_json.get('message')}'", t6_pass)
except Exception as e:
    record(6, "Duplicate Email Registration Prevention", "Registration rejected", f"Exception: {e}", False)

# ==============================================================================
# TEST 7: Create two separate admin accounts
# ==============================================================================
admin1_email = f"admin.alpha_{int(time.time())}@nsbm.lk"
admin1_pass = "AdminAlphaPass1!"
admin1_name = "Admin Alpha"

admin2_email = f"admin.beta_{int(time.time())}@nsbm.lk"
admin2_pass = "AdminBetaPass2!"
admin2_name = "Admin Beta"

opener_adm1, _ = make_session()
reg_data_adm1 = urllib.parse.urlencode({
    'action': 'register',
    'name': admin1_name,
    'email': admin1_email,
    'password': admin1_pass,
    'role': 'admin'
}).encode('utf-8')

opener_adm2, _ = make_session()
reg_data_adm2 = urllib.parse.urlencode({
    'action': 'register',
    'name': admin2_name,
    'email': admin2_email,
    'password': admin2_pass,
    'role': 'admin'
}).encode('utf-8')

try:
    r1 = opener_adm1.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/auth_controller.php?format=json", data=reg_data_adm1))
    j1 = json.loads(r1.read().decode('utf-8'))
    adm1_id = j1.get('user', {}).get('id')

    r2 = opener_adm2.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/auth_controller.php?format=json", data=reg_data_adm2))
    j2 = json.loads(r2.read().decode('utf-8'))
    adm2_id = j2.get('user', {}).get('id')

    t7_pass = j1.get('success') is True and j2.get('success') is True and adm1_id != adm2_id
    record(7, "Create Two Separate Admin Accounts", "Both admin accounts created in MySQL with role 'admin'", f"Admin 1 ID={adm1_id} ({j1.get('user', {}).get('role')}), Admin 2 ID={adm2_id} ({j2.get('user', {}).get('role')})", t7_pass)
except Exception as e:
    record(7, "Create Two Separate Admin Accounts", "Both admin accounts created", f"Exception: {e}", False)

# ==============================================================================
# TEST 8: Verify both admins can log in independently & access Admin Dashboard
# ==============================================================================
try:
    # Admin 1 login
    adm1_log_opener, _ = make_session()
    r1 = adm1_log_opener.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/auth_controller.php?format=json", data=urllib.parse.urlencode({
        'action': 'login', 'email': admin1_email, 'password': admin1_pass, 'role': 'admin'
    }).encode('utf-8')))
    j1 = json.loads(r1.read().decode('utf-8'))
    dash1_html = adm1_log_opener.open(urllib.request.Request(f"{BASE_URL}/frontend/admin/index.php")).read().decode('utf-8')
    adm1_ok = j1.get('success') is True and "Admin dashboard" in dash1_html and "Admin Alpha" in dash1_html

    # Admin 2 login
    adm2_log_opener, _ = make_session()
    r2 = adm2_log_opener.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/auth_controller.php?format=json", data=urllib.parse.urlencode({
        'action': 'login', 'email': admin2_email, 'password': admin2_pass, 'role': 'admin'
    }).encode('utf-8')))
    j2 = json.loads(r2.read().decode('utf-8'))
    dash2_html = adm2_log_opener.open(urllib.request.Request(f"{BASE_URL}/frontend/admin/index.php")).read().decode('utf-8')
    adm2_ok = j2.get('success') is True and "Admin dashboard" in dash2_html and "Admin Beta" in dash2_html

    t8_pass = adm1_ok and adm2_ok
    record(8, "Independent Admin Logins & Console Access", "Both admins access Admin Dashboard with their respective identities", f"Admin 1 ok={adm1_ok}, Admin 2 ok={adm2_ok}", t8_pass)
except Exception as e:
    record(8, "Independent Admin Logins & Console Access", "Both admins access Admin Dashboard", f"Exception: {e}", False)

# ==============================================================================
# TEST 9: Verify students cannot access admin pages by direct URL
# ==============================================================================
try:
    resp = opener_a_login.open(urllib.request.Request(f"{BASE_URL}/frontend/admin/index.php"))
    blocked = False
    status_code = getattr(resp, 'status', getattr(resp, 'code', 200))
    location = resp.headers.get('Location', '')
    if status_code in [302, 303, 307] and "login.php" in location and "error=" in location:
        blocked = True
    elif status_code == 403:
        blocked = True
    else:
        body = resp.read().decode('utf-8', errors='ignore')
        if "Access denied" in body or "Administrator privileges required" in body or "login.php" in location:
            blocked = True

    t9_pass = blocked
    record(9, "Role-Based Access Control: Student Blocked from Admin", "Student redirected away from /admin/ with Access Denied error", f"Status={status_code}, Location='{location}', Blocked={blocked}", t9_pass)
except Exception as e:
    record(9, "Role-Based Access Control: Student Blocked from Admin", "Student blocked from admin pages", f"Exception: {e}", False)

# ==============================================================================
# TEST 10: Verify logged-out users cannot access protected dashboard URLs
# ==============================================================================
try:
    guest_opener, _ = make_session()
    
    # Check student index
    resp_stud = guest_opener.open(urllib.request.Request(f"{BASE_URL}/frontend/student/index.php"))
    code_stud = getattr(resp_stud, 'status', getattr(resp_stud, 'code', 200))
    loc_stud = resp_stud.headers.get('Location', '')
    stud_blocked = (code_stud in [302, 303, 307] and "login.php" in loc_stud)

    # Check admin index
    resp_adm = guest_opener.open(urllib.request.Request(f"{BASE_URL}/frontend/admin/index.php"))
    code_adm = getattr(resp_adm, 'status', getattr(resp_adm, 'code', 200))
    loc_adm = resp_adm.headers.get('Location', '')
    adm_blocked = (code_adm in [302, 303, 307] and "login.php" in loc_adm)

    t10_pass = stud_blocked and adm_blocked
    record(10, "Guest Access Blocked on Protected Dashboards", "Anonymous guest redirected to login.php for both student & admin dashboards", f"Student protected={stud_blocked} (Location: {loc_stud}), Admin protected={adm_blocked} (Location: {loc_adm})", t10_pass)
except Exception as e:
    record(10, "Guest Access Blocked on Protected Dashboards", "Anonymous guest blocked", f"Exception: {e}", False)

# ==============================================================================
# TEST 11: Verify logout destroys the session
# ==============================================================================
try:
    logout_resp = opener_b.open(urllib.request.Request(f"{BASE_URL}/frontend/logout.php"))
    loc_logout = logout_resp.headers.get('Location', '')
    
    post_logout_resp = opener_b.open(urllib.request.Request(f"{BASE_URL}/frontend/student/index.php"))
    code_post = getattr(post_logout_resp, 'status', getattr(post_logout_resp, 'code', 200))
    loc_post = post_logout_resp.headers.get('Location', '')
    
    t11_pass = (code_post in [302, 303, 307] and "login.php" in loc_post)
    record(11, "Session Destruction on Logout", "Subsequent dashboard requests redirected to login after logout.php is invoked", f"Logout Location='{loc_logout}', Post-logout status={code_post}, Target='{loc_post}'", t11_pass)
except Exception as e:
    record(11, "Session Destruction on Logout", "Session destroyed on logout", f"Exception: {e}", False)

# ==============================================================================
# TEST 12: Website forms, buttons, uploads, downloads, discussions & groups
# ==============================================================================
try:
    # 1. Contact Form
    contact_data = urllib.parse.urlencode({
        'action': 'submit_contact',
        'name': 'Inquirer User',
        'email': 'inquirer@example.com',
        'subject': 'General Inquiry',
        'message': 'Testing contact submission endpoint functionality.'
    }).encode('utf-8')
    r_contact = opener_a_login.open(urllib.request.Request(f"{BASE_URL}/frontend/contact.php", data=contact_data))
    contact_ok = getattr(r_contact, 'status', 200) in [200, 302]

    # 2. Discussion Post
    disc_data = urllib.parse.urlencode({
        'action': 'create_question',
        'title': 'Test Question About Binary Trees',
        'body': 'What is the optimal balancing condition in AVL trees?',
        'faculty': 'Computing',
        'category': 'Computing',
        'tags': 'trees, algorithms'
    }).encode('utf-8')
    r_disc = opener_a_login.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/discussion_controller.php?format=json", data=disc_data))
    j_disc = json.loads(r_disc.read().decode('utf-8'))
    disc_ok = j_disc.get('success') is True or 'id' in j_disc or 'created' in str(j_disc).lower()

    # 3. Resource Download Record
    dl_data = urllib.parse.urlencode({
        'action': 'record_download',
        'resource_id': 1
    }).encode('utf-8')
    r_dl = opener_a_login.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/student_controller.php?format=json", data=dl_data))
    j_dl = json.loads(r_dl.read().decode('utf-8'))
    dl_ok = j_dl.get('success') is True

    # 4. Study Group Join
    grp_data = urllib.parse.urlencode({
        'action': 'join_group',
        'group_id': 1
    }).encode('utf-8')
    r_grp = opener_a_login.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/study_group_controller.php?format=json", data=grp_data))
    j_grp = json.loads(r_grp.read().decode('utf-8'))
    grp_ok = j_grp.get('success') is True or 'already' in j_grp.get('message', '').lower()

    t12_pass = contact_ok and dl_ok and (disc_ok or grp_ok)
    record(12, "Interactive Features: Forms, Downloads, Discussions & Groups", "Contact, discussions, downloads, and group operations execute cleanly", f"Contact={contact_ok}, Download record={dl_ok}, Discussion={disc_ok}, Study Group={grp_ok}", t12_pass)
except Exception as e:
    record(12, "Interactive Features: Forms, Downloads, Discussions & Groups", "Interactive features working", f"Exception: {e}", False)

# ==============================================================================
# TEST 13: Dashboard metrics retrieved dynamically from MySQL
# ==============================================================================
try:
    r_dash = opener_a_login.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/student_controller.php?action=dashboard_data"))
    j_dash = json.loads(r_dash.read().decode('utf-8'))
    metrics = j_dash.get('metrics', {})
    dl_count = metrics.get('downloaded_count', 0)
    
    r_adm = adm1_log_opener.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/admin_controller.php?action=admin_data"))
    j_adm = json.loads(r_adm.read().decode('utf-8'))
    adm_metrics = j_adm.get('metrics', {})
    total_students = adm_metrics.get('total_students', 0)
    
    t13_pass = dl_count >= 1 and total_students >= 2
    record(13, "Live MySQL Retrieval of Dashboard Metrics", "Metrics reflect real relational counts (downloads incremented, total students dynamic)", f"Student A downloads={dl_count} (>=1), Admin total_students={total_students} (>=2)", t13_pass)
except Exception as e:
    record(13, "Live MySQL Retrieval of Dashboard Metrics", "Metrics retrieved from MySQL", f"Exception: {e}", False)

# ==============================================================================
# TEST 14: Relational isolation and privacy of user data
# ==============================================================================
try:
    r_my_dl = opener_a_login.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/student_controller.php?action=dashboard_data"))
    j_my_dl = json.loads(r_my_dl.read().decode('utf-8'))
    a_downloads = j_my_dl.get('my_downloads', [])
    
    opener_b_login, _ = make_session()
    opener_b_login.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/auth_controller.php?format=json", data=urllib.parse.urlencode({
        'action': 'login', 'email': student_b_email, 'password': student_b_pass, 'role': 'student'
    }).encode('utf-8')))
    r_b_dash = opener_b_login.open(urllib.request.Request(f"{BASE_URL}/backend/controllers/student_controller.php?action=dashboard_data"))
    j_b_dash = json.loads(r_b_dash.read().decode('utf-8'))
    b_downloads = j_b_dash.get('my_downloads', [])
    
    privacy_respected = len(a_downloads) >= 1 and len(b_downloads) == 0
    t14_pass = privacy_respected
    record(14, "User Data Privacy & Relational Isolation", "Student B cannot view Student A's private downloads or activity records", f"Student A download records={len(a_downloads)}, Student B download records={len(b_downloads)} (Clean isolation)", t14_pass)
except Exception as e:
    record(14, "User Data Privacy & Relational Isolation", "User data privacy preserved", f"Exception: {e}", False)

print("="*80)
print(f"SUMMARY: {sum(1 for r in results if r['status'] == 'PASS')} / {len(results)} TESTS PASSED")
print("="*80)

