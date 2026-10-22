# US-08 — Prevent Double-Booking: Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Date:** October 22, 2026  
**Tester:** Sewara Rashidi  
**User Story:** US-08 — Prevent Double-Booking  
**Environment:** PHP, MySQL, XAMPP, web browser

## Test Scenario

Two different authenticated patients attempted to book the same appointment slot.

**Test appointment:**
- Doctor ID: 3
- Date: October 23, 2026
- Time: 10:00–10:30 AM
- Confirmed appointment ID: 5

## Results

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US08-T01 | Patient 1 books an available slot | Booking succeeded | PASS |
| US08-T02 | Patient 2 attempts the same slot from an unchanged page | System rejected the booking and reported that the slot was already booked | PASS |
| US08-T03 | Verify duplicate booking prevention in MySQL | Only one appointment was stored for the tested doctor, date, and start time | PASS |
| US08-T04 | Refresh Patient 2's Doctor Details page | The booked 10:00–10:30 AM slot was excluded; the available 10:30–11:00 AM slot remained visible | PASS |

## Summary

**Tests executed:** 4  
**Passed:** 4  
**Failed:** 0

The tested scenario confirms that an existing confirmed appointment cannot be booked again through the application. The database unique index on doctor ID, appointment date, and start time was also verified.

## Remaining Verification

- Test overlapping appointments with different start times.
- Test near-simultaneous booking requests where feasible.

**Current conclusion:** The tested double-booking behavior works correctly. Full US-08 verification remains in progress.



## Additional Database Inspection

**Date:** October 22, 2026

A read-only SQL query was executed to identify
overlapping doctor availability periods.

**Actual result:** MySQL returned zero rows.

**Finding:** No overlapping availability periods
currently exist in the local test database.

This inspection does not prove that the system
prevents new overlapping availability records.

**Follow-up:** Clarify overlapping availability
rules with the Product Owner. Simultaneous booking
and overlapping-time edge cases remain untested.
