# US-07 — Patient Appointment Booking Test Results

**Project:** Clinic Appointment System  
**Course:** ITC315 — Software Engineering  
**Development Methodology:** Scrum  
**Sprint:** Sprint 2  
**Testing Date:** October 19, 2026  
**User Story:** US-07 — Patient Appointment Booking  
**Developers:** Sewara Rashidi (Backend) and Sonbol Almas (Frontend)  
**Testing Type:** Manual Functional, Integration, Security, and Regression Testing  
**Overall Result:** PASS for the test cases executed and recorded below

---

## 1. Testing Objective

The objective of this testing activity was to verify that an authenticated patient can view available appointment slots, select a suitable time, and successfully book an appointment with a doctor.

Testing also examined whether the system correctly saves appointment information, prevents reuse of booked slots, rejects invalid booking requests, enforces role-based access restrictions, and protects existing appointments when doctors modify their availability.

The tests were performed as part of Sprint 2 to validate the integration of US-07 with US-15, Doctor Availability Management.

## 2. Components Tested

| Component | Description |
|---|---|
| `patient/doctor-details.php` | Displays doctor information, available appointment slots, booking buttons, and feedback messages |
| `includes/appointment_repository.php` | Generates appointment slots and excludes unavailable or booked times |
| `actions/appointment_book.php` | Validates and processes patient appointment-booking requests |
| `actions/availability_update.php` | Prevents updates to availability containing confirmed appointments |
| `actions/availability_delete.php` | Prevents deletion of availability linked to appointments |
| `database/schema.sql` | Defines the `appointments` database table and its constraints |
| MySQL `appointments` table | Stores appointment records and their confirmation status |

All source-code paths above are located inside `10-Source-Code/`.

## 3. Test Environment

| Item | Environment |
|---|---|
| Application | Clinic Appointment System |
| Frontend | HTML, Bootstrap, and PHP |
| Backend | PHP |
| Database | MySQL |
| Local server | XAMPP (Apache and MySQL) |
| Development tools | Visual Studio Code and GitHub Desktop |
| Test accounts | Active patient and active doctor accounts in local development databases |
| Test method | Browser-based manual tests and phpMyAdmin database inspection |

Sewara and Sonbol tested the application using their respective local MySQL databases. The database records used on the two computers were independent.

## 4. Backend and Booking Security Test Results — Sewara

| Test ID | Test Scenario | Actual Result | Status |
|---|---|---|---|
| US07-B01 | Generate appointment slots from doctor availability | Four 30-minute slots were generated for October 20, 2026, from 9:00 AM to 11:00 AM | PASS |
| US07-B02 | Book a valid available appointment | The 9:00–9:30 AM slot was booked successfully and a success message appeared | PASS |
| US07-B03 | Verify appointment storage | phpMyAdmin showed one confirmed appointment for Patient ID 8 and Doctor ID 3 | PASS |
| US07-B04 | Exclude a booked slot from availability | The available-slot count decreased from four to three after booking | PASS |
| US07-B05 | Attempt to book the same slot again | The duplicate request was rejected; the database still contained one appointment | PASS |
| US07-B06 | Submit an invalid 9:15 AM starting time | The request was rejected and no additional appointment was created | PASS |
| US07-B07 | Access the booking action without logging in | The user was redirected to the login page | PASS |
| US07-B08 | Access the patient booking action as a doctor | Doctor access was blocked by role-based authorization | PASS |
| US07-B09 | Submit a booking form with an invalid CSRF token | The system displayed an invalid or expired booking form error | PASS |
| US07-B10 | Verify database after CSRF rejection | The original appointment remained the only appointment record | PASS |

### Backend Database Verification

The confirmed test appointment on Sewara's computer contained:

| Field | Recorded Value |
|---|---|
| Appointment ID | 1 |
| Patient ID | 8 |
| Doctor ID | 3 |
| Availability ID | 2 |
| Appointment date | 2026-10-20 |
| Start time | 09:00:00 |
| End time | 09:30:00 |
| Status | confirmed |

## 5. Integration and Regression Test Results — US-15 and US-07

These tests verified that the newly implemented appointment-booking functionality did not compromise the existing Doctor Availability Management functionality.

| Test ID | Test Scenario | Actual Result | Status |
|---|---|---|---|
| US07-I01 | Update availability linked to a confirmed appointment | The system blocked the update and displayed an explanatory error | PASS |
| US07-I02 | Update availability without confirmed bookings | The doctor successfully updated an unbooked availability record | PASS |
| US07-I03 | Delete availability linked to a patient appointment | The system blocked deletion and retained the availability record | PASS |
| US07-I04 | Delete availability without appointments | A temporary October 23 test availability record was successfully deleted | PASS |
| US07-I05 | Verify appointment integrity after update and deletion tests | The original confirmed appointment was still present in MySQL | PASS |

The backend prevents accidental changes to booked availability by checking for associated appointments. The update and deletion actions also coordinate with booking operations using database transactions and doctor-record locking.

## 6. Frontend and End-to-End Test Results — Sonbol

| Test ID | Test Scenario | Actual Result | Status |
|---|---|---|---|
| US07-F01 | Display available appointment slots | The Doctor Details page displayed an available 10:00–10:30 AM slot with a Book Appointment button | PASS |
| US07-F02 | Submit an appointment booking through the patient interface | The booking succeeded, and the green success message appeared | PASS |
| US07-F03 | Verify the frontend booking in MySQL | phpMyAdmin showed a confirmed appointment for Patient ID 1 and Doctor ID 3 | PASS |
| US07-F04 | Verify booked-slot filtering in the interface | The booked 10:00–10:30 AM slot disappeared, and no further slots were displayed | PASS |
| US07-F05 | Refresh the page after successful booking | The slot remained unavailable, and the one-time success message no longer appeared | PASS |

### Frontend Database Verification

The confirmed test appointment on Sonbol's computer contained:

| Field | Recorded Value |
|---|---|
| Appointment ID | 1 |
| Patient ID | 1 |
| Doctor ID | 3 |
| Appointment date | 2026-10-20 |
| Start time | 10:00:00 |
| End time | 10:30:00 |
| Status | confirmed |

This result demonstrates successful communication between the patient-facing interface, PHP booking action, and MySQL database.

## 7. PHP Syntax Verification

PHP syntax checks were performed during development on the following files:

| File | Result |
|---|---|
| `actions/appointment_book.php` | No syntax errors reported |
| `actions/availability_update.php` | No syntax errors reported |
| `actions/availability_delete.php` | No syntax errors reported |
| `patient/doctor-details.php` | No syntax errors reported |

Syntax verification confirms that these files can be parsed by PHP. Functional behavior was verified separately through manual testing.

## 8. Test Summary

| Test Category | Passed | Failed |
|---|---:|---:|
| Backend and booking security | 10 | 0 |
| Availability integration and regression | 5 | 0 |
| Frontend and end-to-end | 5 | 0 |
| **Total recorded test cases** | **20** | **0** |

**Result:** All 20 recorded manual test cases passed.

The results support the Sprint 2 implementation of US-07 in the scenarios tested. They do not establish that every possible booking conflict or concurrency scenario has been tested.

## 9. Limitations and Follow-Up Work

The following areas remain outside the completed US-07 testing scope:

- Comprehensive appointment conflict handling and concurrency stress testing associated with US-08.
- Appointment cancellation and rebooking workflows. The current unique database constraint may require revision when cancelled slots are made bookable again.
- Automated regression tests covering the booking workflow.
- Testing with a larger variety of doctor schedules, overlapping availability periods, and edge-case time configurations.

These items must be considered when their corresponding user stories or subsequent integration tasks are addressed. No test result is claimed for functionality that has not yet been implemented or verified.

## 10. Conclusion

Manual testing demonstrated that the implemented appointment-booking workflow functions correctly in the tested scenarios.

Patients were able to view available appointment slots, submit bookings, receive confirmation messages, and see booked slots removed from availability. MySQL database inspections confirmed successful storage of appointments.

Invalid appointment times, duplicate booking attempts, unauthorized access, and invalid CSRF tokens were rejected during backend testing. Integration testing also confirmed that doctors could not modify or delete availability linked to existing appointments, while normal changes to unbooked availability remained functional.

**US-07 Testing Status: PASS for the documented scenarios.**

**Prepared for:** Sprint 2 Developer Documentation  
**Related User Stories:** US-07 and US-15  
**Repository:** Clinic Appointment System