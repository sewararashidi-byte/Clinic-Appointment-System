# Sprint 4 — Developer Pre-Sprint Plan

**Project:** Clinic Appointment System  
**Developers:** Sewara Rashidi and Sonbol Almas  
**Sprint duration:** 2 development days  
**Focus:** Cancellation, Rescheduling, History, Reminders and Doctor Profiles

## 1. Sprint Goal

**Proposed Sprint Goal:** Enable patients to manage booked appointments through cancellation, rescheduling, and history, provide approved upcoming-appointment reminders, and let doctors maintain their own professional profile, while preserving the authentication, availability, and double-booking safeguards implemented in Sprints 1–3.



## 2. User Stories and Acceptance Criteria

### US-09 — Appointment Cancellation (FR-10)

**User story:** As a patient, I want to cancel my appointment so that I can inform the clinic when I can no longer attend.

**Existing backlog acceptance criteria:**

1.  Patient can view eligible appointments for cancellation.
2.  Patient can select an appointment to cancel.
3.  System updates the appointment status after cancellation.



### US-10 — Appointment Rescheduling (FR-11)

**User story:** As a patient, I want to reschedule my appointment so that I can choose another available time when my plans change.

**Existing backlog acceptance criteria:**

1.  Patient can select an existing appointment to reschedule.
2.  System displays available alternative slots.
3.  System updates the appointment after a new slot is selected.



### US-12 — Appointment History (FR-13)

**User story:** As a patient, I want to view my upcoming and previous appointments so that I can keep track of my appointments.

**Existing backlog acceptance criteria:**

1.  Patient can view upcoming appointments.
2.  Patient can view previous appointments.
3.  Appointment details are displayed accurately.



### US-13 — Appointment Notifications/Reminders (FR-14)

**User story:** As a patient, I want to receive appointment reminders so that I do not forget my scheduled appointment.

**Existing backlog acceptance criteria:**

1.  System identifies upcoming appointments.
2.  System sends a reminder for an upcoming appointment according to the configured schedule.
3.  Reminder contains relevant appointment details.





### US-14 — Doctor Profile Management (FR-15)

**User story:** As a doctor, I want to manage my professional profile so that patients can see accurate information about me.

**Existing backlog acceptance criteria:**

1.  Doctor can view professional profile.
2.  Doctor can update permitted professional information.
3.  System saves updated information.



## 3. Division of Development Work

### Developer Responsibility Split

| Area        | Sewara                                                      | Sonbol                              | Joint verification                               |
|-------------|-------------------------------------------------------------|-------------------------------------|--------------------------------------------------|
| US-09       | Cancellation backend, schema/status, ownership, concurrency | Cancellation action UI and feedback | Policy, release-slot and authorization tests     |
| US-10       | Reschedule transaction, availability queries, conflicts     | Slot picker and reschedule form     | Rollback, races, correctness                     |
| US-12       | Secure history/upcoming query and grouping                  | History/list page and navigation    | Data accuracy, past/upcoming, role access        |
| US-13       | Reminder rules, persisted data, actual scheduler/delivery   | Reminder view or delivery UI        | Timing, details, deduplication, changes          |
| US-14       | Doctor profile backend, validation, authorization           | Doctor profile edit page            | DB persistence and patient-facing doctor details |
| UML/testing | Sequence diagrams, backend test evidence                    | Frontend evidence, UI review        | Regression, review, GitHub integration           |




## 4. Detailed Work Plan by Day

### Day 1

**Early: Sprint Planning and safety gate**

- Husna clarifies the open policies or identifies temporary scope limitations; Mineh coordinates the event.
- Both developers review `schema.sql`, appointment repository, booking action, doctor profiles, and dashboards; confirm exact endpoints and input/output contract.
- Estimate tasks, agree the two-day Sprint Goal/capacity, create branches from latest `main`.

**Sewara — backend priorities**

1.  Examine appointment status enum, unique constraints and transaction locks.
2.  Implement secure patient appointment listing / upcoming / previous foundation (US-12).
3.  Implement US-09 cancellation after rules are confirmed; validate that status updates do not break unique slot guarantees or history.
4.  Begin US-10 atomic rescheduling; reuse booking availability/conflict logic.
5.  Run repository-level tests and record only actual outcomes.

**Sonbol — frontend priorities**

1.  Implement patient appointment listing, history, and dashboard navigation against agreed backend output (US-12).
2.  Add eligible cancellation controls, confirmation, and result feedback (US-09).
3.  Start reschedule page/alternative slot selection (US-10) after agreeing API/form fields.
4.  Connect and test completed paths in browser as backend modules become available.

**Together:** Test US-12 and US-09 end-to-end; test US-10 initial happy/negative path if available; push small focused commits and reviewed Pull Requests; record actual blockers and remaining tasks.

**Day 1 target (not a completion claim):** Secure appointment-list/history page and working cancellation, with rescheduling underway.

### Day 2

**Sewara — backend priorities**

1.  Complete US-10 conflict-safe rescheduling and negative cases.
2.  Implement US-14 doctor-owned professional-profile retrieval/updates, using existing profile tables.
3.  Implement US-13 approved reminder mechanism and storage, if channel, timing, and scheduler are confirmed.
4.  Prepare required sequence diagrams / modeling notes.
5.  Complete security, SQL-state verification, syntax and integration testing; help resolve defects.

**Sonbol — frontend priorities**

1.  Finish US-10 rescheduling interface and booking feedback.
2.  Implement US-14 Doctor Profile view/edit form; confirm patient-facing doctor details display the changes.
3.  Implement the approved US-13 reminder delivery/view if technically supported.
4.  Complete navigation, validation, empty states, and responsive behavior.
5.  Run manual browser checks for each feature and collaborative regression tests with Sewara.

**Together:** Integrate/migrate on both local databases where necessary; execute acceptance and negative tests, record results in one report per story, complete GitHub PRs, review Sprint Goal and genuinely finished stories, prepare a Sprint Review demonstration. Product Owner acceptance and Scrum event records require the actual event; no invented feedback/progress.

**Day 2 target (not a completion claim):** Working and tested scope for the stories agreed in Sprint Planning, with transparent carryover if needed.

## 5. Planned Testing and Integration

**US-09:** eligible owned appointment cancels; status persists; patient B cannot cancel patient A; past/cancelled/ineligible booking rejected according to policy; duplicate POST safe; cancelled slot availability and unique index correct; database records remain consistent.

**US-10:** alternative slots reflect availability; reschedule succeeds; confirmed conflict blocked; original booking remains if new slot becomes unavailable; other patient's appointment cannot change; date/time/doctor fields consistent; previously booked slot handling correct; race/duplicate requests where feasible.

**US-12:** current vs previous records classified correctly; details/status accurate; empty state; patient A cannot view patient B; invalid session denied; sorted by actual appointment date/time.

**US-13:** upcoming detection; approved timing and channel; actual delivery demonstrated; correct patient/doctor/date/time; do not send after cancellation; rescheduled data updated; no duplicate sends; negative/no-due cases. If scheduler cannot be demonstrated, mark delivery acceptance criterion not verified.

**US-14:** own doctor profile loads; approved fields save and persist; invalid/overlength inputs rejected; another doctor/patient blocked; valid CSRF; no SQL injection through prepared statements; modified details visible to patients through US-05/US-06 views.

**Regression:** registration/login/role access, doctor search/info, doctor availability creation and overlap safeguards, booking, double-booking protection, confirmation, patient profile, doctor appointment schedule; specifically retest booking after cancellation/rescheduling. Perform PHP lint on new/modified files. 




