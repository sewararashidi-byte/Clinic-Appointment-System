# SD04 — Doctor Availability Management: Sprint 3 Update Notes

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Developer:** Sewara Rashidi  
**Date:** October 24, 2026  
**Related User Stories:** US-15 — Doctor Availability Management; US-08 — Prevent Double Booking

## 1. Purpose

The SD04 Doctor Availability Management Sequence Diagram was originally developed during Sprint 2 to represent the doctor's availability creation, update, and deletion workflows.

During Sprint 3, SD04 was revised to reflect additional scheduling conflict prevention requirements confirmed by the Product Owner.

## 2. Changes Made in Sprint 3

### Availability Creation

The diagram was updated to represent:

- Checking for overlapping availability periods belonging to the same doctor on the same date.
- Starting a database transaction and locking the doctor's record.
- Rejecting overlapping availability.
- Committing successful availability creation.
- Rolling back rejected or failed operations.

### Availability Update

The diagram was updated to represent:

- Verifying that the availability belongs to the logged-in doctor.
- Preventing updates to availability containing confirmed appointments.
- Checking whether updated availability overlaps another period.
- Excluding the current availability record from the overlap check.
- Committing valid updates and rolling back rejected changes.

### Availability Deletion

The existing Sprint 2 deletion workflow was retained in the diagram.

The deletion implementation was not independently changed or verified as part of this US-08 update.

## 3. Related Implementation Files

- `10-Source-Code/actions/availability_create.php`
- `10-Source-Code/actions/availability_update.php`

## 4. Related Testing

The US-08 testing report documents appointment double-booking tests, availability creation tests, availability update tests, and post-merge regression tests.

**Document:** `08-Testing/Sprint-3/US-08-Double-Booking-Test-Results.md`

**Recorded Results:** 17 tests passed, 0 failed.

Controlled simultaneous-request stress testing remains unverified.

## 5. Updated Diagram Files

- `SD04-Doctor-Availability-Management-Sprint3.mmd` — Editable Mermaid source.
- `SD04-Doctor-Availability-Management-Sprint3.svg` — Exported sequence diagram.
- `SD04-Doctor-Availability-Management-Sprint3-Update-Notes.md` — Description of the changes.

## 6. Conclusion

The Sprint 3 revision of SD04 extends the original Sprint 2 sequence diagram to document the implemented availability overlap protection.

The original Sprint 2 diagram and documentation remain unchanged to preserve the project's development history.
