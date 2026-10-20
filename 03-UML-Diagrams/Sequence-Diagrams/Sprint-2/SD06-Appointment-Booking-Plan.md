# SD06 — Patient Appointment Booking Sequence Diagram Plan

**Project:** Clinic Appointment System  
**Course:** ITC315 — Software Engineering  
**Sprint:** Sprint 2  
**Related User Story:** US-07 — Patient Appointment Booking  
**Related User Story:** US-15 — Doctor Availability Management  
**Diagram Type:** UML Sequence Diagram  
**Developer Responsible:** Sewara Rashidi  
**Date:** October 19, 2026

## 1. Purpose

The purpose of SD06 is to model the sequence of interactions required for a patient to book an appointment with a doctor.

The diagram illustrates communication between the patient-facing interface, PHP repositories, appointment-booking action, and MySQL database.

It also represents the validation and error-handling steps used to prevent invalid or conflicting bookings.

## 2. Related User Story

**US-07 — Patient Appointment Booking**

The system shall allow an authenticated patient to select an available appointment time with a doctor and submit a booking request.

### Acceptance Criteria

1. An authenticated patient can view available appointment slots.
2. The patient can select a valid slot and submit a booking request.
3. The system validates the submitted information and security token.
4. The system confirms that the selected slot is still available.
5. A successful booking is stored in the database with a confirmed status.
6. The patient receives a success or error message.
7. Booked appointment slots are excluded from the available-slot display.

## 3. Participants

| Participant | Responsibility |
|---|---|
| Patient | Views available slots and submits a booking request |
| Doctor Details Page | Displays doctor information and appointment-booking options |
| Doctor Repository | Retrieves doctor profiles and availability records |
| Appointment Repository | Generates appointment slots and filters booked times |
| Booking Action | Validates and processes booking requests |
| MySQL Database | Stores doctors, availability records, and appointments |

## 4. Main Sequence of Events

1. The patient opens the Doctor Details page.
2. The system retrieves the selected doctor's information.
3. The system retrieves the doctor's current and future availability.
4. The Appointment Repository generates available appointment slots and excludes booked times.
5. The page displays the available appointment slots.
6. The patient selects a slot and clicks **Book Appointment**.
7. The booking action verifies the patient's authorization, request data, and CSRF token.
8. The system begins a database transaction and locks the doctor's record.
9. The system retrieves current availability and checks the selected slot again.
10. If the slot is valid and available, the system inserts a confirmed appointment.
11. The transaction is committed.
12. The patient is redirected to the Doctor Details page with a booking success message.


### 4.1 Appointment Date Selection and Filtering (T2.8)

During Sprint 2 Day 4, the appointment-booking
interface was extended to support date selection.

The updated sequence includes:

1. The patient opens the Doctor Details page.
2. The patient selects an appointment date.
3. The patient clicks "Show Available Slots".
4. The system retrieves the doctor's current
   availability and booked appointment information.
5. The system filters available slots to display
   only those matching the selected date.
6. If matching slots exist, the patient can
   select an available time and book it.
7. If no slots are available, the system
   displays a date-specific message.
8. The patient can click "Show All Dates"
   to remove the filter.

**Related Task:** T2.8 — Appointment Date Selection
and Filtering

**Implemented by:** Sonbol Almas

**Testing:** Six manual tests were completed
successfully as part of Sprint 2 Day 4 testing.

**Database Changes:** None. The feature uses
the existing doctor availability and
appointments tables.

## 5. Alternative and Exception Flows

| Condition | Expected System Behavior |
|---|---|
| Doctor does not exist or is unavailable | Display a doctor unavailable message |
| No appointment slots are available | Display a message indicating that no slots are currently available |
| Invalid booking information | Reject the request and display an error |
| Invalid CSRF security token | Reject the booking request |
| Slot already booked | Reject the booking without creating a duplicate appointment |
| Slot no longer matches doctor availability | Reject the booking and ask the patient to choose another slot |
| Database operation fails | Roll back the transaction and display an error |

## 6. Preconditions

- The patient is authenticated and has permission to access the booking functionality.
- The selected doctor has an active account.
- The doctor has availability records when appointment slots are to be displayed.
- The application is connected to the MySQL database.

## 7. Postconditions

**Successful booking:**

- A confirmed appointment record is stored in the `appointments` table.
- The booked time is excluded from the available appointment slots.
- The patient receives a booking success message.

**Unsuccessful booking:**

- No new appointment is created for the rejected request.
- The patient receives an appropriate error message.

## 8. Technical Components

The following project files support the sequence diagram:

| File | Purpose |
|---|---|
| `patient/doctor-details.php` | Doctor details and patient booking interface |
| `includes/doctor_repository.php` | Doctor and availability retrieval |
| `includes/appointment_repository.php` | Appointment-slot generation and filtering |
| `actions/appointment_book.php` | Booking validation and database operations |
| `config/auth.php` | Authentication and role-based authorization |
| `config/database.php` | MySQL database connection |
| `database/schema.sql` | Appointment database structure |

These paths are relative to `10-Source-Code/`.

## 9. Database Interactions

The booking process uses the following database tables:

- `users`: verifies the doctor's account and supports patient identity.
- `doctor_profiles`: supplies doctor profile information.
- `doctor_availability`: stores the doctor's available date and time ranges.
- `appointments`: stores confirmed appointment bookings.

Database transactions and locking are used during booking to coordinate access to appointment availability. The database also includes a unique constraint on doctor, appointment date, and starting time.

## 10. Related Testing

The SD06 workflow was checked through manual backend and frontend tests, including:

- Displaying available appointment slots.
- Successfully booking an appointment.
- Verifying that booking information was stored in MySQL.
- Rejecting duplicate and invalid booking requests.
- Validating CSRF protection and access restrictions.
- Confirming that booked appointment slots disappear from the interface.
- Protecting booked doctor availability from modification or deletion.

**Related document:** `08-Testing/Sprint-2/US-07-Appointment-Booking-Test-Results.md`

## 11. Diagram Artifacts

The following files document SD06:

- `SD06-Appointment-Booking-Plan.md` — This planning and design explanation.
- `SD06-Appointment-Booking.mmd` — Editable Mermaid sequence diagram source.
- `SD06-Appointment-Booking.png` — Exported visual representation.

## 12. Conclusion

SD06 documents the implemented patient appointment-booking interaction for Sprint 2. It shows the main successful booking sequence and relevant alternative flows, including invalid requests and unavailable appointment slots.

The sequence diagram supports the system modeling requirements of the Software Engineering Scrum project and provides a reference for implementation review and testing.

**Diagram Status:** Created for Sprint 2 documentation.