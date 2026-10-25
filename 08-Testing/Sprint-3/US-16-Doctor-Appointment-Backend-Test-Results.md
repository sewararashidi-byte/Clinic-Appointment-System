# US-16 — Doctor Appointment Management: Backend Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Testing Date:** October 25, 2026  
**Tester:** Sewara Rashidi  
**User Story:** US-16 — Doctor Appointment Management  
**Branch:** `feature/s3-doctor-appointments-backend`  
**Environment:** PHP, MySQL, XAMPP, PHP CLI

---

## 1. Testing Objective

Verify that the Doctor Appointment Management backend retrieves confirmed appointments assigned to a doctor and returns the information required for displaying the doctor's appointment schedule.

The tests focus on appointment retrieval, returned data, and handling of an unknown doctor ID.

## 2. Product Owner Requirements

The Product Owner, Husna Zala Khan, confirmed that:

- Doctors can view their scheduled appointments.
- Appointment information includes patient name, appointment date, and time.
- Doctors cannot directly modify or cancel appointments.
- Appointment changes and cancellations are handled by the receptionist or administrator.

## 3. Backend Implementation

### Appointment Repository

**File:** `10-Source-Code/includes/appointment_repository.php`

Added the function:

`getDoctorAppointments(mysqli $conn, int $doctorId): array`

The function:

- Retrieves confirmed appointments for the specified doctor.
- Includes appointment reference, patient name, appointment date, start time, end time, and status.
- Uses a prepared SQL statement.
- Orders results by appointment date and starting time.
- Does not modify appointment records.

### Secure Data Loader

**File:** `10-Source-Code/includes/doctor_appointments_data.php`

The loader:

- Requires an authenticated doctor.
- Obtains the doctor ID from the login session.
- Calls `getDoctorAppointments()` using that ID.
- Makes appointment data available to the future Doctor Appointments page.

## 4. Test Environment and Data

**Test Doctor ID:** 3

Tests were executed against the existing local MySQL database.

A temporary PHP CLI test script was used to retrieve appointments and compare the results with database queries.

No appointment records were inserted, updated, or deleted during testing.

## 5. Backend Test Cases and Results

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US16-BT01 | Retrieve confirmed appointments for Doctor ID 3 and compare the result count with MySQL | Repository count matched the database count | PASS |
| US16-BT02 | Verify returned appointments contain required information | Returned appointments contained reference, patient name, date, start time, end time, and confirmed status | PASS |
| US16-BT03 | Retrieve appointments using nonexistent Doctor ID 999999 | Repository returned an empty array | PASS |

## 6. PHP Syntax Verification

| File | Result |
|---|---|
| `10-Source-Code/includes/appointment_repository.php` | PASS |
| `10-Source-Code/includes/doctor_appointments_data.php` | PASS |

Both files passed PHP syntax validation.

## 7. Security Considerations

The repository filters appointment records by doctor ID.

The data loader requires the doctor role and obtains the doctor ID from the authenticated PHP session rather than from a user-controlled URL parameter.

The implemented feature is read-only and provides no appointment editing or cancellation actions.

These measures are present in the implementation. Browser-level authorization and isolation between two actual doctor accounts have not yet been tested.

## 8. Testing Summary

| Category | Executed | Passed | Failed |
|---|---:|---:|---:|
| Backend functional tests | 3 | 3 | 0 |
| PHP syntax checks | 2 | 2 | 0 |

**Functional Tests: 3 PASS, 0 FAIL**

**PHP Syntax Checks: 2 PASS**

## 9. Remaining Testing and Integration

The following activities remain:

1. Implement the Doctor Appointments frontend with Sonbol.
2. Test the complete appointment viewing workflow in a browser.
3. Verify that two different authenticated doctors cannot view each other's appointments.
4. Verify the empty appointment list interface.
5. Confirm that no doctor-facing editing or cancellation controls are provided.
6. Perform regression testing after merging the frontend and backend.
7. Create SD07 — Doctor Appointment Management Sequence Diagram.
8. Obtain final Product Owner review and complete the team's Definition of Done.

## 10. Conclusion

The US-16 Doctor Appointment Management backend passed three repository-level functional tests and two PHP syntax checks.

The implementation retrieves confirmed appointments and prepares doctor-specific data for the frontend without permitting appointment modification.

**Current Status:** Initial backend implementation and CLI testing completed. Frontend integration, browser security testing, UML documentation, and final acceptance remain pending.
