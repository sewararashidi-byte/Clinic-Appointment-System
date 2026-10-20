# Sprint 2 — Day 4 Regression Testing Report

**Project:** Clinic Appointment System  
**Course:** ITC315 — Software Engineering  
**Methodology:** Scrum  
**Sprint:** Sprint 2  
**Date:** October 20, 2026  
**Developer / Tester:** Sewara Rashidi  
**Testing Environment:** PHP, MySQL, XAMPP, and Web Browser  
**Testing Type:** Manual Integration and Regression Testing

## 1. Testing Objective

The purpose of Day 4 regression testing was to verify that the functionality implemented during Sprint 2 continued to operate correctly after the backend and frontend changes were integrated into the `main` branch.

Testing focused on four user stories:

- **US-05:** Doctor Search
- **US-06:** Doctor Information and Availability
- **US-07:** Available Appointment Dates, Time Slots, and Booking
- **US-15:** Doctor Availability Management

The tests examined doctor search, professional information display, appointment booking, database persistence, and protection of existing patient appointments.

## 2. Test Environment

| Component | Description |
|---|---|
| Application | Clinic Appointment System |
| Local server | XAMPP — Apache |
| Backend | PHP |
| Database | MySQL |
| Frontend | HTML and Bootstrap |
| Database inspection | phpMyAdmin |
| Version control | GitHub |
| Test accounts | Active patient and test doctor accounts |
| Test doctor | Test Doctor, Doctor ID 3 |

All tests were performed against Sewara's local development environment.

## 3. Regression Test Results

| Test ID | User Story | Test Scenario | Actual Result | Status |
|---|---|---|---|---|
| D4-T01 | US-05 | Search for a doctor using the Cardiology specialty | Matching Test Doctor appeared in search results | PASS |
| D4-T02 | US-05 | Search for a doctor using the name "Test" | Test Doctor appeared with the View Doctor option | PASS |
| D4-T03 | US-05 | Search using a nonexistent doctor's name | Appropriate no-results message appeared | PASS |
| D4-T04 | US-06 | View a doctor's professional information | Name, specialty, qualifications, experience, and biography displayed correctly | PASS |
| D4-T05 | US-06 | View updated doctor availability | October 21 availability and corresponding appointment slots displayed correctly | PASS |
| D4-T06 | US-06 | Navigate from Doctor Details back to Doctor Search | Back to Doctor Search button worked correctly | PASS |
| D4-T07 | US-07 | Book an available appointment on October 21 | Booking succeeded, selected slot disappeared, and another unbooked slot remained available | PASS |
| D4-T08 | US-07 | Verify appointment storage in MySQL | Two confirmed appointment records were present | PASS |
| D4-T09 | US-07 | Refresh the Doctor Details page after booking | Booked slot remained unavailable and the previous success message disappeared | PASS |
| D4-T10 | US-15 | Attempt to update availability containing a confirmed booking | Update was rejected and existing availability remained unchanged | PASS |
| D4-T11 | US-15 | Attempt to delete availability containing a confirmed booking | Deletion was rejected and availability remained present | PASS |
| D4-T12 | US-07 / US-15 | Verify appointment integrity after rejected availability changes | Both confirmed appointments remained stored in MySQL | PASS |

## 4. Detailed Test Observations

### 4.1 US-05 — Doctor Search

Three manual regression tests were performed.

The doctor search functionality returned matching doctors when searching by specialty and name. An invalid search term returned an appropriate no-results message.

**Result:** 3 tests passed.

### 4.2 US-06 — Doctor Information and Availability

Three manual regression tests were performed.

The system successfully displayed the selected doctor's professional details, including name, specialty, qualifications, experience, and biography.

The updated October 21 availability was visible to the patient, and the Back to Doctor Search navigation worked correctly.

**Result:** 3 tests passed.

### 4.3 US-07 — Appointment Booking

The patient successfully booked an appointment for October 21, 2026, from 10:00 AM to 10:30 AM.

The system displayed the booking success message and removed the booked slot from the available appointment list. The remaining 10:30–11:00 AM slot continued to appear as available.

Database inspection through phpMyAdmin confirmed that the newly booked appointment was stored with a confirmed status.

After refreshing the Doctor Details page, the booked slot remained unavailable and the one-time success message was no longer displayed.

**Result:** 3 booking-related tests passed, with an additional appointment-integrity test shared with US-15.

### 4.4 US-15 — Doctor Availability Management

The doctor attempted to update and delete an availability period containing a confirmed appointment.

Both operations were rejected by the backend. The October 21 availability remained unchanged, preventing accidental disruption of the patient's booking.

**Result:** 2 availability-protection tests passed, with an additional appointment-integrity test shared with US-07.

## 5. Database Integrity Verification

At the end of Day 4 testing, phpMyAdmin showed two confirmed appointments in Sewara's local database.

| Appointment Date | Start Time | End Time | Status |
|---|---|---|---|
| 2026-10-20 | 09:00:00 | 09:30:00 | confirmed |
| 2026-10-21 | 10:00:00 | 10:30:00 | confirmed |

Both appointments remained present after the rejected availability update and deletion operations.

This confirms that the tested operations did not remove or alter the existing appointment records.

## 6. Testing Summary

| User Story / Area | Tests Passed |
|---|---:|
| US-05 — Doctor Search | 3 |
| US-06 — Doctor Information and Availability | 3 |
| US-07 — Booking and database persistence | 3 |
| US-15 — Availability update and deletion protection | 2 |
| US-07 / US-15 — Final database integrity | 1 |
| **Total** | **12** |

**Total Tests Executed:** 12  
**Passed:** 12  
**Failed:** 0  
**Overall Day 4 Regression Test Result:** PASS

These results are based on manual testing and the observed application and database behavior.

## 7. Remaining Work and Limitations

The recorded tests confirm that the implemented functionality operated correctly in the scenarios tested. They do not establish that all possible inputs, browser configurations, or concurrent booking scenarios have been verified.

At the time of this report:

- **T2.8:** A separate appointment-date selection and filtering feature remained to be completed.
- Additional integration testing would be needed after T2.8 was implemented.
- Comprehensive appointment conflict handling and cancellation workflows remained outside the completed Day 4 testing scope.
- Automated regression testing had not been performed as part of these recorded checks.

These remaining activities should be tracked through the Sprint Backlog and relevant user stories.

## 8. Conclusion

Sprint 2 Day 4 regression testing demonstrated that the implemented doctor search, doctor information, availability display, appointment booking, and booked-availability protection functions continued to operate correctly after integration.

All 12 recorded manual test checks passed.

The results provide testing evidence for the current Sprint 2 increment and support the ongoing verification of the team's Sprint Goal.

**Testing Status:** PASS for all documented Day 4 scenarios.

**Prepared by:** Sewara Rashidi  
**Related Sprint:** Sprint 2  
**Report Date:** October 20, 2026