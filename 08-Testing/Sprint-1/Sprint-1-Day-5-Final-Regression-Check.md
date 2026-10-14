# Sprint 1 - Day 5 Final Regression Check

## Project
Clinic Appointment System

## Sprint
Sprint 1 - User Accounts and Authentication

## Date
October 14, 2026

## Purpose

This final regression check was performed to confirm that the main Sprint 1 authentication functionality still worked correctly after the integration of registration, login, role-based access, dashboards, and logout features.

## Regression Checks Performed

### 1. Patient Registration

**Check**

A new patient account was registered through the registration interface.

**Expected Result**

- The account is created successfully.
- The account role is `patient`.
- The account status is `pending`.

**Actual Result**

The registration flow completed successfully and the created account followed the expected patient and pending-account behavior.

**Status:** PASS

---

### 2. Active Patient Login

**Check**

An active patient account was used to log in through the login interface.

**Expected Result**

- Valid credentials are accepted.
- The user is redirected to the Patient Dashboard.

**Actual Result**

The patient successfully logged in and reached the Patient Dashboard.

**Status:** PASS

---

### 3. Role-Based Access Restriction

**Check**

While logged in as a patient, the Doctor Dashboard URL was opened manually.

**Expected Result**

- Access to the Doctor Dashboard is denied.
- The patient is redirected to the Patient Dashboard.
- An unauthorized-access message is displayed.

**Actual Result**

The system blocked the unauthorized dashboard access and redirected the patient correctly.

**Status:** PASS

---

### 4. Logout

**Check**

The Logout button was used after authentication.

**Expected Result**

- The authenticated session is ended.
- The user is redirected to the login page.

**Actual Result**

Logout completed successfully and the user returned to the login page.

**Status:** PASS

---

## Final Result

All final Sprint 1 regression checks passed successfully.

- Checks performed: 4
- Passed: 4
- Failed: 0

No critical integration defect was found during the Day 5 final regression review.

## Developer Participation

- Sewara Rashidi reviewed the Sprint 1 backend, UML, testing documentation, and source-code structure.
- Sonbol Almas reviewed the Sprint 1 frontend pages for consistency and usability.
- Both Developers participated in the final end-to-end regression check.