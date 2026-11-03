# Sprint 2 — Developer Pre-Sprint Plan

**Project:** Clinic Appointment System  
**Developers:** Sewara Rashidi and Sonbol Almas  
**Sprint duration:** 4 development days  
**Focus:** Doctor Availability, Search and Appointment Booking

## 1. Sprint Goal

Enable doctors to manage availability and patients to search for doctors, view information and availability, select appointment slots and book appointments (the focus of US-15, US-05, US-06 and US-07).

## 2. User Stories and Acceptance Criteria

### US-15 — Doctor Availability Management

As a doctor, I want to manage my availability so that patients can book appointments during my available times.

Acceptance criteria:

1.  Doctor can set available dates and times.

2.  Doctor can update their availability.

3.  Updated availability is reflected when patients view appointment slots.

### US-05 — Doctor Search

As a patient, I want to search for doctors so that I can find a suitable doctor for my appointment.

Acceptance criteria:

1.  Patient can enter search criteria.

2.  System displays matching doctors.

3.  Patient can select a doctor to view their information.

### US-06 — Doctor Information and Availability

As a patient, I want to view a doctor's professional information and availability so that I can choose a suitable doctor and time.

Acceptance criteria:

1.  Patient can view the doctor's professional information.

2.  Patient can view the doctor's available dates and times.

3.  Displayed availability reflects the doctor's current availability.

### US-07 — Available Slots and Appointment Booking

As a patient, I want to view available appointment slots and book an appointment so that I can schedule a consultation with a doctor.

Acceptance criteria:

1.  System displays available appointment slots.

2.  Patient can select an available slot.

3.  System confirms the appointment after successful booking.

## 3. Division of Development Work

| Story / area | Sewara — backend and database | Sonbol — interface and integration |
|---|---|---|
| US-15 — Doctor Availability | Design database additions; update `schema.sql`; establish relationships/foreign keys; implement basic doctor-availability backend; protect availability functionality so only doctors can use it | Design Doctor Availability interface; add Doctor Dashboard navigation; build availability form/interface |
| US-05 — Doctor Search | Retrieve doctors; process search criteria; return matching doctor records | Build `doctor-search.php`: search input/filter, matching-doctor cards/table and “View Doctor” action |
| US-06 — Doctor Information | Retrieve selected doctor information; retrieve current availability; enforce patient authentication | Build doctor-details UI with professional information, available dates/times and link to slots |
| US-07 — Booking | Retrieve slots; validate patient/session and selected doctor/slot; create appointment record; return booking success/error | Build slot-selection interface, booking form/button, success/error messages and navigation |
| UML and tests | Lead Sprint 2 sequence diagrams, backend/security, database consistency, authorization tests | Update/review Use Case Diagram; frontend validation, navigation and responsive/UI reviews |
| Integration | Together | Together |

## 4. Detailed Work Plan by Day

### Day 1 — Planning, database foundation & doctor availability

**Sewara**

- Review US-15, US-05, US-06, US-07.

- Document Product Owner clarifications.

- Design database additions.

- Update schema.sql.

- Establish relationships/foreign keys.

- Implement basic doctor-availability backend.

- Protect availability functionality so only doctors can use it.

- Begin SD-04 Doctor Availability sequence model.

**Sonbol**

- Review UI needs.

- Design Doctor Availability interface.

- Add Doctor Dashboard navigation for availability.

- Build availability form/interface.

- Prepare patient doctor-search page structure.

- Update Use Case Diagram if necessary.

**Together**

- Integrate doctor availability UI → PHP → MySQL.

- Create test doctor data if needed.

- Verify a doctor can create availability.

- Verify patient cannot open doctor availability management.

**Day 1 deliverable**

A logged-in doctor can create/manage basic availability stored in MySQL.

### Day 2 — Doctor Search + Doctor Information

**Sewara**

Implement US-05 backend:

- retrieve doctors,

- process search criteria,

- return matching doctor records.

Implement US-06 backend:

- retrieve selected doctor information,

- retrieve current availability,

- enforce patient authentication.

**Sonbol**

Build:

doctor-search.php

Features:

- search input/filter,

- matching-doctor cards/table,

- “View Doctor” action.

Build doctor-details UI:

- professional information,

- current available dates/times,

- button/link toward available slots.

**Together**

Test:

- valid search,

- no-result search,

- matching doctors,

- selecting doctor,

- doctor information display,

- current availability display.

**Day 2 deliverable**

Patient can search for a doctor, select one, and view the doctor's information and current availability.

### Day 3 — Available Slots & Appointment Booking

This is the heaviest day.

**Sewara**

Implement US-07 backend:

- retrieve available slots for selected doctor,

- validate patient/session,

- validate selected doctor/slot,

- create appointment record,

- return booking success/error message.

Create or update:

appointments

database structure as required.

Begin booking sequence diagram.

**Sonbol**

Build:

- slot-selection interface,

- appointment booking form/button,

- booking success/error message,

- navigation from doctor detail → available slots → booking.

Add client-side validation where relevant.

**Together**

Integrate:

Search Doctor

↓

View Doctor

↓

View Availability

↓

Choose Slot

↓

Book Appointment

↓

Success Message

**Day 3 deliverable**

Authenticated patient can complete the first full appointment-booking path.

### Day 4 — Testing, UML, integration & Sprint completion

**Sewara**

- backend/security review,

- database consistency review,

- authorization tests,

- complete Sprint 2 sequence diagrams,

- document actual test results.

**Sonbol**

- responsive/UI review,

- navigation review,

- frontend validation testing,

- final Use Case Diagram review.

**Both**

Run complete end-to-end testing:

Doctor logs in

→ creates availability

Patient logs in

→ searches doctor

→ selects doctor

→ views information

→ views availability

→ chooses slot

→ books appointment

→ sees success message

Also test negative cases.

Then:

- fix real bugs,

- merge PRs,

- run Sprint 1 regression,

- prepare Sprint Review demonstration.

**Day 4 deliverable**

US-15, US-05, US-06, and US-07 integrated, tested, documented and merged into main.

## 5. Planned Testing and Integration



For **US-15**:

- doctor creates valid availability,

- doctor updates availability,

- patient view reflects updated availability,

- patient cannot access doctor availability-management page.

For **US-05**:

- search finds matching doctor,

- search with no match shows clear result,

- patient can select doctor.

For **US-06**:

- selected doctor's professional data is correct,

- available dates/times display,

- updated availability is reflected.

For **US-07**:

- available slots display,

- valid slot can be selected,

- appointment record is created,

- success message appears,

- unauthenticated user cannot book,

- invalid/missing slot is rejected.






