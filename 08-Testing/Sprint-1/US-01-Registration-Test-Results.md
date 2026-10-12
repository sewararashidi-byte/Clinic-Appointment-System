# US-01 User Registration - Test Results

## Sprint
Sprint 1 - User Accounts and Authentication

## User Story
US-01 - User Registration

**User Story:**  
As a user, I want to create an account so that I can access the Clinic Appointment System.

## Test Environment

- Backend: PHP
- Database: MySQL
- Database Management: phpMyAdmin
- Local Server: XAMPP
- Frontend: HTML, Bootstrap, CSS, JavaScript
- Browser: Google Chrome

---

## TC-REG-01 - Valid Patient Registration

**Test Data**

- Full Name: Test Patient
- Age: 25
- Phone Number: 0700000000
- Email: testpatient1@example.com
- Password: Valid password matching the required password policy

**Expected Result**

- Registration should succeed.
- A patient record should be created in the database.
- Role should be `patient`.
- Account status should be `pending`.
- Password should be stored as a hash rather than plain text.

**Actual Result**

- Registration completed successfully.
- One user record was created in the `users` table.
- Role was stored as `patient`.
- Account status was stored as `pending`.
- Password was stored as a hashed value beginning with `$2y$`.

**Result:** PASS

---

## TC-REG-02 - Duplicate Email Registration

**Test Data**

- A second registration was attempted using:
  `testpatient1@example.com`

**Expected Result**

- The system should reject an email address that already exists.
- No additional database record should be created.

**Actual Result**

- The duplicate registration was rejected.
- The `users` table remained at one record.

**Result:** PASS

---

## TC-REG-03 - Invalid Password Requirements

**Test Data**

Password used:

`clinic123`

The password did not contain:
- an uppercase letter
- a special character

**Expected Result**

- The system should reject the password.
- Validation messages should be displayed.
- No user record should be created.

**Actual Result**

- The system displayed password validation messages.
- Registration was rejected.
- The `users` table remained at one record.

**Result:** PASS

---

## Test Summary

| Test Case | Description | Result |
|---|---|---|
| TC-REG-01 | Valid patient registration | PASS |
| TC-REG-02 | Duplicate email prevention | PASS |
| TC-REG-03 | Password policy validation | PASS |

## Conclusion

The tested US-01 registration functionality successfully created a valid patient registration, prevented duplicate email registration, enforced the required password policy, stored passwords securely as hashes, and assigned the correct patient role and pending approval status.