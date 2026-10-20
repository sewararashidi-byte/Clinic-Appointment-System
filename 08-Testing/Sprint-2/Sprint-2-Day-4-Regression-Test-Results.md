# Sprint 2 — Day 4 Regression Testing Report

**Project:** Clinic Appointment System  
**Course:** ITC315 — Software Engineering  
**Methodology:** Scrum  
**Sprint:** Sprint 2  
**Date:** October 20, 2026  
**Developer / Tester:** Sewara Rashidi  and Sonbol Almas
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


Testing was performed on Sewara's and Sonbol's separate
local development environments. Each developer used
XAMPP, PHP, and MySQL to verify the functionality
assigned to them.


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

## Sonbol — T2.8 Appointment Date Selection Testing

**Test Date:** October 20, 2026  
**Developer / Tester:** Sonbol Almas  
**User Story:** US-07 — Available Appointment Dates and Time Slots  
**Sprint Task:** T2.8 — Appointment Date Selection and Filtering  
**Testing Environment:** PHP, MySQL, XAMPP, and Web Browser

### Testing Objective

The objective was to verify that patients can select an appointment date, view available time slots for that date, clear the selected date filter, and successfully book an appointment without affecting existing booking functionality.

### Test Results

| Test ID | Test Scenario | Actual Result | Status |
|---|---|---|---|
| D4-T13 | Select October 24, 2026, when no appointment slots are available | The system displayed a date-specific no-available-slots message | PASS |
| D4-T14 | Select October 25, 2026, when the doctor has availability from 10:00 AM to 11:00 AM | Two available 30-minute slots appeared with booking buttons | PASS |
| D4-T15 | Click Show All Dates after selecting a date | The date filter was cleared and available appointment slots remained visible | PASS |
| D4-T16 | Book the October 25 appointment from 10:00 AM to 10:30 AM after filtering by date | Booking succeeded, the selected slot disappeared, and the remaining slot stayed available | PASS |
| D4-T17 | Verify the new appointment in MySQL | The October 25 appointment was stored with confirmed status; both existing test appointments were present | PASS |
| D4-T18 | Select October 25 again after booking | The booked 10:00–10:30 AM slot was hidden, while the 10:30–11:00 AM slot remained available | PASS |

### Database Verification

Sonbol verified the following new appointment in her local MySQL database:

| Field | Verified Value |
|---|---|
| Doctor ID | 3 |
| Appointment date | 2026-10-25 |
| Start time | 10:00:00 |
| End time | 10:30:00 |
| Status | confirmed |

The previously booked October 20 appointment was also still present.

### Testing Conclusion

All six recorded T2.8 manual tests passed.

The results confirm that appointment date selection and filtering function correctly in the tested scenarios and remain integrated with the existing appointment-booking backend.

**T2.8 Testing Result: PASS**


## 6. Testing Summary

The Sprint 2 Day 4 regression testing was performed by
Sewara Rashidi and Sonbol Almas using their respective
local development environments.

| Developer | Tests Executed | Passed | Failed |
|---|---:|---:|---:|
| Sewara Rashidi | 12 | 12 | 0 |
| Sonbol Almas | 6 | 6 | 0 |
| **Total** | **18** | **18** | **0** |

**Total Tests Executed:** 18

**Passed:** 18

**Failed:** 0

**Overall Day 4 Regression Testing Result:** PASS

The results confirm that the implemented functionality
worked correctly in the 18 recorded manual test scenarios.



## 7. Remaining Work and Limitations

### 7.1 Completed Sprint 2 Task

T2.8 — Appointment Date Selection and Filtering
was implemented and tested by Sonbol Almas.

Patients can now select an appointment date, view
available time slots for that date, clear the date
filter, and book an available appointment.

All six recorded T2.8 manual tests passed.

### 7.2 Remaining Testing Limitations

Although all 18 recorded Day 4 tests passed,
the following areas were not fully verified:

- Automated regression testing.
- Simultaneous appointment-booking requests
  from multiple patients.
- Overlapping doctor availability periods.
- Appointment cancellation and rebooking.
- Testing across multiple browsers and devices.

These limitations should be considered when
planning future development and testing activities.

### 7.3 Sprint Backlog Status

The implementation and testing tasks covered by
the Day 4 report have been completed.

Final acceptance of the Sprint 2 user stories
remains subject to the team's Definition of Done
and Sprint Review.


## 8. Conclusion


## 8. Conclusion

Sprint 2 Day 4 regression testing verified the
integrated functionality of the Clinic Appointment System
across four user stories:

- US-05 — Doctor Search
- US-06 — Doctor Information and Availability
- US-07 — Appointment Dates, Time Slots, and Booking
- US-15 — Doctor Availability Management

Sewara Rashidi completed 12 regression tests covering
doctor search, doctor information, appointment booking,
database persistence, and availability protection.

Sonbol Almas completed 6 additional tests covering
appointment date selection, date filtering, booking
after filtering, and database verification.

All 18 recorded manual tests passed successfully.

The results demonstrate that the implemented features
functioned correctly in the scenarios tested.

T2.8 — Appointment Date Selection and Filtering
has been implemented and tested.

Further testing may be required for concurrency,
overlapping availability, cancellation, and other
scenarios outside the current testing scope.

**Overall Day 4 Testing Status:** PASS

**Total Tests:** 18
**Passed:** 18
**Failed:** 0

**Prepared by:** Sewara Rashidi and Sonbol Almas
**Related Sprint:** Sprint 2
**Report Date:** October 20, 2026
