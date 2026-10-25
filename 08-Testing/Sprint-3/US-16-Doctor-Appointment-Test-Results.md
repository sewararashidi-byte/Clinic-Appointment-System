# US-16 — Doctor Appointment Management: Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Testing Date:** October 25, 2026  
**Testers:** Sewara Rashidi (Backend), Sonbol Almas (Frontend)  
**User Story:** US-16 — Doctor Appointment Management  
**Backend Branch:** `feature/s3-doctor-appointments-backend`  
**Frontend Branch:** `feature/s3-doctor-appointments-frontend`  
**Environment:** PHP, MySQL, XAMPP, PHP CLI, Web Browser

---

## 1. Testing Objective

Verify that authenticated doctors can view their confirmed patient appointments through the Clinic Appointment System.

Testing covers backend appointment retrieval, frontend appointment display, doctor-specific data access, authentication, role-based authorization, empty appointment lists, read-only functionality, and PHP syntax verification.

The objective is to evaluate the implementation against the Product Owner's requirements for US-16.

## 2. User Story and Product Owner Requirements

**US-16 — Doctor Appointment Management**

The Product Owner, Husna Zala Khan, confirmed the following requirements:

- Doctors can view their scheduled appointments.
- Appointment information must include the patient's name, appointment date, and appointment time.
- Doctors cannot directly modify or cancel appointments.
- Appointment modifications and cancellations are handled by the receptionist or administrator.

The implemented doctor-facing appointment list retrieves appointments with the `confirmed` status.

Each authenticated doctor must be restricted to their own appointment records.

## 3. Backend Implementation — Sewara Rashidi

### 3.1 Appointment Repository

**File:** `10-Source-Code/includes/appointment_repository.php`

Implemented the following function:

`getDoctorAppointments(mysqli $conn, int $doctorId): array`

The function:

- Retrieves confirmed appointments belonging to the specified doctor.
- Includes appointment reference, patient name, appointment date, start time, end time, and appointment status.
- Uses a prepared SQL statement.
- Filters appointments using the doctor's database ID.
- Orders appointments by date and starting time.
- Returns an empty array when no matching appointments are found.
- Does not modify or cancel appointment records.

### 3.2 Doctor Appointments Data Loader

**File:** `10-Source-Code/includes/doctor_appointments_data.php`

The data loader:

- Requires an authenticated user with the doctor role.
- Retrieves the doctor's ID from the authenticated PHP session.
- Calls `getDoctorAppointments()` using the authenticated doctor's ID.
- Makes the resulting appointment records available to the frontend page.
- Provides an error message when a valid doctor session is unavailable.

The doctor ID is obtained from the server-side session rather than from a user-controlled URL parameter.

## 4. Frontend Implementation — Sonbol Almas

### 4.1 Doctor Appointments Page

**File:** `10-Source-Code/doctor/appointments.php`

Sonbol implemented a Doctor Appointments page using PHP and Bootstrap.

The page:

- Displays a heading identifying the doctor's appointment schedule.
- Loads appointment data through Sewara's existing backend.
- Displays confirmed appointments in a responsive table.
- Includes appointment reference, patient name, appointment date, start time, end time, and status.
- Displays an informational message when no confirmed appointments exist.
- Displays an error message when appointment data cannot be loaded.
- Escapes displayed appointment values using HTML escaping.
- Provides navigation back to the Doctor Dashboard.

The interface is read-only.

No appointment editing or cancellation buttons are included.

### 4.2 Doctor Dashboard Navigation

**File:** `10-Source-Code/dashboards/doctor-dashboard.php`

Sonbol added a **My Appointments** navigation button to the existing Doctor Dashboard.

The button directs authenticated doctors to:

`../doctor/appointments.php`

The existing Doctor Dashboard functionality was retained.

## 5. Database Implementation

US-16 uses the existing appointment and user information stored in MySQL.

The backend retrieves appointment records from the `appointments` table and patient names from the `users` table.

No new database tables, columns, or migrations were required for the US-16 frontend implementation.

The Doctor Appointments page does not create, update, or delete appointment records.

## 6. Test Environment and Test Data

### 6.1 Backend Testing — Sewara

**Test Doctor ID:** 3

Sewara performed repository-level tests against her local MySQL database.

A temporary PHP CLI test script retrieved appointment records and compared the results with MySQL queries.

No appointment records were inserted, updated, or deleted during backend testing.

### 6.2 Frontend Testing — Sonbol

Sonbol performed manual browser testing using her local XAMPP environment.

Testing covered:

- Access through the Doctor Dashboard.
- Display of confirmed appointments.
- Visibility of patient names, dates, and times.
- Read-only appointment presentation.
- Patient-role access restrictions.
- Unauthenticated access restrictions.
- Isolation of appointments between doctor accounts.
- Display of an empty appointment list.

The results below reflect the seven frontend tests Sonbol reported as successful.

## 7. Backend Test Cases and Results — Sewara

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US16-BT01 | Retrieve confirmed appointments for Doctor ID 3 and compare the result count with MySQL | Repository count matched the database count | PASS |
| US16-BT02 | Verify returned appointments contain required information | Returned records contained appointment reference, patient name, date, start time, end time, and confirmed status | PASS |
| US16-BT03 | Retrieve appointments using nonexistent Doctor ID 999999 | Repository returned an empty array | PASS |

**Backend Functional Testing: 3 PASS, 0 FAIL.**

## 8. Frontend, Integration, and Security Tests — Sonbol

| Test ID | Test Case | Expected Result | Reported Actual Result | Status |
|---|---|---|---|---|
| US16-FT01 | Authenticated doctor clicks My Appointments from the Doctor Dashboard | Doctor Appointments page opens | Page opened successfully | PASS |
| US16-FT02 | Doctor with confirmed appointments opens the appointment page | Correct patient names, dates, times, references, and status are displayed | Appointment information displayed successfully | PASS |
| US16-FT03 | Examine the Doctor Appointments interface | No Edit or Cancel buttons are available | No editing or cancellation controls were displayed | PASS |
| US16-FT04 | Patient attempts to open the Doctor Appointments URL | Patient access is blocked | Access was blocked | PASS |
| US16-FT05 | Logged-out visitor attempts to open the Doctor Appointments URL | Unauthenticated access is blocked | Access was blocked | PASS |
| US16-FT06 | A second authenticated doctor opens the appointment page | Appointments belonging to the first doctor are not displayed | Doctor-specific appointment isolation was successful | PASS |
| US16-FT07 | Doctor with no confirmed appointments opens the page | An empty-state message is displayed | The no-confirmed-appointments message appeared | PASS |

**Frontend, Integration, and Security Testing: 7 PASS, 0 FAIL.**

The seven results were reported by Sonbol during manual browser testing.

Individual screenshots and automated browser test logs have not been attached to this report.

## 9. PHP Syntax Verification

PHP syntax checks were performed for the backend and frontend implementation files.

### 9.1 Backend Syntax Verification — Sewara

| File | Result |
|---|---|
| `10-Source-Code/includes/appointment_repository.php` | PASS |
| `10-Source-Code/includes/doctor_appointments_data.php` | PASS |

**Backend PHP Syntax Checks: 2 PASS.**

### 9.2 Frontend Syntax Verification — Sonbol

| File | Result |
|---|---|
| `10-Source-Code/doctor/appointments.php` | PASS |
| `10-Source-Code/dashboards/doctor-dashboard.php` | PASS |

**Frontend PHP Syntax Checks: 2 PASS.**

### 9.3 Syntax Verification Summary

| Category | Files Checked | Passed | Failed |
|---|---:|---:|---:|
| Backend PHP | 2 | 2 | 0 |
| Frontend PHP | 2 | 2 | 0 |
| **Total** | **4** | **4** | **0** |

**PHP Syntax Verification: 4 PASS, 0 FAIL.**

Syntax verification is recorded separately from functional and security testing.

## 10. Security and Data Protection Verification

### 10.1 Authentication

The Doctor Appointments page requires users to be authenticated.

**Verified by:** US16-FT05  
**Result:** PASS

### 10.2 Role-Based Authorization

Only users with the doctor role may access the Doctor Appointments page.

A patient account was used to verify that access is restricted.

**Verified by:** US16-FT04  
**Result:** PASS

### 10.3 Doctor Appointment Ownership

The backend retrieves the doctor ID from the authenticated PHP session.

The appointment repository restricts results to records belonging to that doctor.

Sonbol tested appointment access using different authenticated doctor accounts.

The reported result was that appointments belonging to the first doctor were not displayed to the second doctor.

**Verified by:** US16-FT06  
**Result:** PASS

### 10.4 Read-Only Appointment Access

The Doctor Appointments page displays appointment information but does not provide editing or cancellation controls.

The backend retrieval function also performs a SELECT operation rather than modifying appointment records.

**Verified by:** US16-FT03 and backend implementation review  
**Result:** PASS for the tested read-only interface.

**Test limitation:** The absence of editing controls verifies the implemented doctor-facing interface. It does not, by itself, constitute testing of every appointment modification endpoint elsewhere in the system.

### 10.5 Prepared SQL Statements

The appointment repository uses a prepared SQL statement to retrieve doctor-specific appointment records.

A separate SQL injection penetration test was not performed.

### 10.6 Output Escaping

The frontend uses HTML escaping when displaying appointment information.

A separate cross-site scripting test was not performed.

## 11. Empty Appointment List Verification

US-16 must handle doctors who do not have confirmed appointments.

The frontend checks whether the appointment result collection is empty.

When no confirmed appointments are available, the interface displays:

**“You have no confirmed appointments.”**

Sonbol reported that this behavior was verified successfully.

**Verified by:** US16-FT07  
**Result:** PASS

This prevents the interface from displaying an empty table without explanation.

## 12. Combined Testing Summary

| Testing Category | Executed | Passed | Failed |
|---|---:|---:|---:|
| Backend functional tests — Sewara | 3 | 3 | 0 |
| Frontend, integration, and security tests — Sonbol | 7 | 7 | 0 |
| **Total Functional and Security Tests** | **10** | **10** | **0** |

**Total Executed Test Cases:** 10  
**Total Passed:** 10  
**Total Failed:** 0

### Additional PHP Syntax Verification

| Verification | Passed | Failed |
|---|---:|---:|
| Backend PHP syntax checks | 2 | 0 |
| Frontend PHP syntax checks | 2 | 0 |
| **Total** | **4** | **0** |

**Overall Recorded Results:**

- **10 functional, integration, and security tests passed.**
- **4 PHP syntax checks passed.**
- **0 failed checks reported.**
- No appointment modifications were required during testing.

These tests provide evidence that:

- Doctors can access their appointment schedule.
- Confirmed appointment information is retrieved from MySQL.
- Patient names, appointment dates, times, references, and statuses are displayed.
- Doctors cannot edit or cancel appointments through the implemented page.
- Patient accounts cannot access the doctor appointment interface.
- Unauthenticated visitors cannot access the interface.
- Appointment lists are restricted to the authenticated doctor in the tested scenario.
- Empty appointment lists are handled appropriately.

## 13. Related UML Documentation

**Sequence Diagram:** SD07 — Doctor Appointment Management

The following files were created by Sewara and merged into `main`:

- `03-UML-Diagrams/Sequence-Diagrams/Sprint-3/SD07-Doctor-Appointment-Management-Sprint3.mmd`
- `03-UML-Diagrams/Sequence-Diagrams/Sprint-3/SD07-Doctor-Appointment-Management-Sprint3.png`
- `03-UML-Diagrams/Sequence-Diagrams/Sprint-3/SD07-Doctor-Appointment-Management-Sprint3-Plan.md`

The sequence diagram models:

- Doctor authentication and authorization.
- Retrieval of the authenticated doctor's ID.
- Doctor-specific appointment retrieval.
- Database operations.
- Display of confirmed appointments.
- Empty appointment list handling.
- Read-only appointment access.

## 14. Remaining Verification and Integration Work

The following activities remain pending:

| Activity | Current Status |
|---|---|
| Backend implementation | COMPLETED AND MERGED |
| Backend functional testing | COMPLETED — 3 PASS |
| Frontend implementation | COMPLETED LOCALLY |
| Frontend manual testing | COMPLETED — 7 PASS |
| PHP syntax verification | COMPLETED — 4 PASS |
| SD07 Sequence Diagram | COMPLETED AND MERGED |
| Combined US-16 testing documentation | PREPARED |
| Frontend commit and push | PENDING |
| Frontend Pull Request and merge into `main` | PENDING |
| Post-merge regression testing | PENDING |
| Product Owner review and acceptance | PENDING |

The post-merge regression test must verify that the Doctor Appointments interface continues to work after the frontend implementation has been merged into the shared `main` branch.

Product Owner acceptance must be recorded only after an actual review.

## 15. Development and Documentation Status

| Activity | Responsible Developer | Status |
|---|---|---|
| Appointment repository function | Sewara Rashidi | Completed |
| Secure doctor appointment data loader | Sewara Rashidi | Completed |
| Backend repository testing | Sewara Rashidi | 3 tests passed |
| Backend PHP syntax verification | Sewara Rashidi | 2 checks passed |
| SD07 Sequence Diagram | Sewara Rashidi | Completed and merged |
| Doctor Appointments interface | Sonbol Almas | Implemented locally |
| My Appointments dashboard navigation | Sonbol Almas | Implemented locally |
| Frontend functional and security testing | Sonbol Almas | 7 tests passed |
| Frontend PHP syntax verification | Sonbol Almas | 2 checks passed |
| Combined testing report | Development Team | Prepared with 10 passed tests |
| Frontend GitHub merge | Sonbol Almas | Pending |
| Post-merge regression testing | Development Team | Pending |
| Product Owner acceptance | Product Owner | Pending |

## 16. Conclusion

US-16 — Doctor Appointment Management has passed **10 documented functional, integration, and security test cases**, comprising:

- **3 backend functional tests** performed by Sewara Rashidi.
- **7 frontend, integration, and security tests** performed by Sonbol Almas.

Additionally, **four PHP files passed syntax verification**.

The completed tests provide evidence that authenticated doctors can view their own confirmed appointments, including patient information, appointment dates, and appointment times.

The implemented interface is read-only and does not provide appointment editing or cancellation controls.

Authentication, role-based access, doctor-specific appointment isolation, and empty appointment list behavior were also tested successfully.

**Current Status:** Backend implementation and UML documentation are merged into `main`. Frontend implementation and initial testing have been completed locally. The frontend commit, GitHub merge, post-merge regression testing, and Product Owner acceptance remain pending.

**Recorded Functional and Security Testing: 10 PASS — 0 FAIL.**

**PHP Syntax Verification: 4 PASS — 0 FAIL.**