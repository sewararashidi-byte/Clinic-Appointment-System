# Sprint 1 - US-02 and US-03 Test Results

## Project
Clinic Appointment System

## Sprint
Sprint 1 - User Accounts and Authentication

## Related User Stories

- **US-02:** As a registered user, I want to log in securely so that I can access the functions available to my role.
- **US-03:** As a user, I want system role-based access so I can only access appropriate functions and information.

## Test Environment

- Local development environment using XAMPP
- PHP backend
- MySQL database through phpMyAdmin
- Google Chrome browser
- Database: `clinic_appointment_system`
- Table: `users`

## Test Data Setup

Public registration creates users with the `patient` role and `pending` account status.

For role-based access testing, selected test accounts were manually updated in phpMyAdmin to the required role and account status.

These manual database changes were used only as test fixtures and do not represent implementation of administrator account-management functionality.

---

## TC-LOGIN-01 - Valid Active Patient Login

**Test Account**

- Email: `activepatient@example.com`
- Role: `patient`
- Account Status: `active`

**Test Procedure**

1. Opened the login page.
2. Entered a valid patient email and password.
3. Submitted the login form.

**Expected Result**

- Valid credentials are accepted.
- An authenticated session is created.
- The patient role is identified.
- The user is redirected to `patient-dashboard.php`.

**Actual Result**

The login was successful. The system redirected the user to the Patient Dashboard and displayed:

- `Patient Dashboard`
- `Welcome, Active Patient`
- `You are logged in as a patient.`

**Status:** PASS

---

## TC-LOGIN-02 - Incorrect Password

**Test Account**

- Email: `activepatient@example.com`
- Password: Incorrect password

**Test Procedure**

1. Entered the valid patient email.
2. Entered an incorrect password.
3. Submitted the login form.

**Expected Result**

- Authentication is rejected.
- The user remains on the login page.
- No dashboard access is granted.
- An invalid-credentials message is displayed.

**Actual Result**

The system rejected the login and displayed:

`Invalid email or password.`

No dashboard access was granted.

**Status:** PASS

---

## TC-LOGIN-03 - Unknown Email Address

**Test Data**

- Email: `nobody@example.com`
- Password: `Clinic@123`

**Test Procedure**

1. Entered an email address that does not exist in the users table.
2. Entered a password.
3. Submitted the login form.

**Expected Result**

The system rejects the credentials and displays a generic invalid-credentials message.

**Actual Result**

The system displayed:

`Invalid email or password.`

The user remained on the login page.

**Status:** PASS

---

## TC-LOGIN-04 - Pending Account Login

**Test Account**

- Email: `pendingpatient@example.com`
- Role: `patient`
- Account Status: `pending`

**Test Procedure**

1. Registered a new patient account.
2. Left the account status as `pending`.
3. Attempted to log in using valid credentials.

**Expected Result**

The user must not be given access while the account is pending administrator approval.

**Actual Result**

The system blocked access and displayed:

`Your account is awaiting administrator approval.`

No dashboard access was granted.

**Status:** PASS

---

## TC-LOGIN-05 - Empty Login Fields

**Test Procedure**

1. Opened the login page.
2. Left the email and password fields empty.
3. Submitted the form.

**Expected Result**

The system rejects the submission and informs the user that the required fields must be completed.

**Actual Result**

The system displayed:

`Email and password are required.`

The user remained on the login page.

**Status:** PASS

---

## TC-LOGIN-06 - Invalid Email Format

**Test Data**

- Email: `invalidemail`
- Password: `Clinic@123`

**Test Procedure**

1. Entered an incorrectly formatted email address.
2. Entered a password.
3. Submitted the form.

**Expected Result**

The system rejects the request because the email format is invalid.

**Actual Result**

The system displayed:

`Please enter a valid email address.`

**Status:** PASS

---

## TC-LOGIN-07 - Rejected Account Login

**Test Account**

- Email: `rejectedpatient@example.com`
- Role: `patient`
- Account Status: `rejected`

**Test Procedure**

1. Registered the test patient.
2. Changed the test account status to `rejected` in phpMyAdmin.
3. Attempted to log in using valid credentials.

**Expected Result**

A rejected account must not be allowed to access the system.

**Actual Result**

The system blocked access and displayed:

`Your account is not approved for access.`

No dashboard access was granted.

**Status:** PASS

---

## TC-RBAC-01 - Patient Accesses Patient Dashboard

**Test Account**

- Email: `activepatient@example.com`
- Role: `patient`
- Account Status: `active`

**Expected Result**

The patient is allowed to access the Patient Dashboard.

**Actual Result**

The patient was successfully redirected to and shown the Patient Dashboard.

**Status:** PASS

---

## TC-RBAC-02 - Patient Attempts to Access Doctor Dashboard

**Test Account**

- Email: `activepatient@example.com`
- Role: `patient`

**Test Procedure**

1. Logged in as an active patient.
2. Manually opened the URL for `doctor-dashboard.php`.

**Expected Result**

- Doctor Dashboard access is denied.
- The patient is redirected to the Patient Dashboard.
- An unauthorized-access message is displayed.

**Actual Result**

The system denied access, redirected the user to the Patient Dashboard, and displayed:

`You are not authorized to access that page.`

**Status:** PASS

---

## TC-RBAC-03 - Doctor Accesses Doctor Dashboard

**Test Account**

- Email: `testdoctor@example.com`
- Role: `doctor`
- Account Status: `active`

**Test Procedure**

1. Logged in using the doctor test account.
2. Submitted valid credentials.

**Expected Result**

The system identifies the `doctor` role and redirects the user to `doctor-dashboard.php`.

**Actual Result**

The system displayed:

- `Doctor Dashboard`
- `Welcome, Test Doctor`
- `You are logged in as a doctor.`

**Status:** PASS

---

## TC-RBAC-04 - Receptionist Accesses Shared Admin Dashboard

**Test Account**

- Email: `testreceptionist@example.com`
- Role: `receptionist`
- Account Status: `active`

**Expected Result**

The receptionist is redirected to the shared administrative/staff dashboard.

**Actual Result**

The system redirected the receptionist to `admin-dashboard.php` and displayed:

- `Admin Dashboard`
- `Welcome, Test Receptionist`
- `You are logged in as receptionist.`

**Status:** PASS

---

## TC-RBAC-05 - Administrator Accesses Admin Dashboard

**Test Account**

- Email: `testadmin@example.com`
- Role: `administrator`
- Account Status: `active`

**Expected Result**

The administrator is redirected to the Admin Dashboard.

**Actual Result**

The system redirected the administrator to `admin-dashboard.php` and displayed:

- `Admin Dashboard`
- `Welcome, Test Administrator`
- `You are logged in as administrator.`

**Status:** PASS

---

## TC-RBAC-06 - Unauthenticated User Attempts Protected Dashboard Access

**Test Procedure**

1. Ensured the user was logged out.
2. Manually opened `patient-dashboard.php`.

**Expected Result**

- Access is denied.
- The user is redirected to the login page.
- A login-required message is displayed.

**Actual Result**

The system redirected the user to the login page and displayed:

`Please log in to access this page.`

**Status:** PASS

---

## TC-AUTH-01 - Logout

**Test Procedure**

1. Logged in as an active patient.
2. Clicked the Logout button.

**Expected Result**

- The authenticated session is destroyed.
- The user is redirected to the login page.

**Actual Result**

The user was successfully redirected to the login page after logout.

**Status:** PASS

---

## Testing Summary

A total of **14 authentication and role-based access test cases** were successfully completed.

- Passed: **14**
- Failed: **0**

The tests confirmed that:

- Valid active users can authenticate successfully.
- Incorrect and unknown credentials are rejected.
- Pending and rejected accounts cannot access the system.
- Required-field and email-format validation operate correctly.
- Patient, doctor, receptionist, and administrator roles are redirected to the appropriate dashboards.
- Unauthorized access to protected role pages is restricted.
- Unauthenticated users cannot directly access protected dashboards.
- Logout terminates access and returns the user to the login page.

## Testing Notes

During testing, the local MySQL service temporarily stopped, which caused a database-connection error during one attempted login. After MySQL was restarted, the affected login test was repeated successfully. This was an environment/service interruption rather than a failure of the authentication functionality.