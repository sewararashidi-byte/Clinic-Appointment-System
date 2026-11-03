# Sprint 5 — Developer Pre-Sprint Plan

**Project:** Clinic Appointment System  
**Developers:** Sewara Rashidi and Sonbol Almas  
**Sprint duration:** 1 development day + 1 separate final verification day  
**Focus:** Clinic Receptionist / Administrator Management

## 1. Sprint Goal

Allow authorized clinic receptionists/administrators to view and update patient and doctor information and manage appointments securely. If the team has sufficient time and Product Owner approval, also provide basic clinic information management. Keep all existing appointment workflows working.

## 2. User Stories and Acceptance Criteria

| Order | Story | Priority | Product Backlog acceptance criteria |
|---|---|---|---|
| 1 | US-17 / FR-19 — Patient Information Management | Should Have | 1. Authorized staff can view patient information.<br>2. Authorized staff can update permitted patient information.<br>3. System saves the updated patient information. |
| 2 | US-18 / FR-20 — Doctor Information Management | Should Have | 1. Authorized staff can view doctor information.<br>2. Authorized staff can update permitted doctor information.<br>3. System saves the updated doctor information. |
| 3 | US-19 / FR-21 — Appointment Management | Should Have | 1. Authorized staff can view appointments.<br>2. Authorized staff can manage appointment information according to their permissions.<br>3. System updates the appointment information successfully. |
| 4 | US-20 / FR-22 — Clinic Information Management | Could Have | 1. Authorized staff can view clinic information.<br>2. Authorized staff can update permitted clinic information.<br>3. System saves the updated clinic information. |

## 3. Division of Development Work

| Area | Sewara — backend/developer | Sonbol — frontend/integration | Joint proof |
|---|---|---|---|
| US-17 | Verify patient schema, safe list/update queries, role checks, validation and CSRF | Patient-management list and edit form | Permitted update succeeds; unauthorized access rejected |
| US-18 | Verify doctor/user schema, safe list/update queries, field validation and ownership/role checks | Doctor-management list and edit form | Updated doctor data visible to staff and patient search |
| US-19 | Reuse safe appointment services, status rules, conflicts and transactions; do not bypass booking safeguards | Appointment-management list, approved staff action form, result messages | Allowed appointment action works; occupied slot and unauthorized changes rejected |
| US-20 (if committed) | Confirm fields/schema; create/update clinic settings safely | Clinic details view/edit screen | Edited clinic details persist after reload |
| Whole Sprint | Database backup/migrations, testing, sequence diagrams, security documentation | Shared style, staff dashboard links, page screenshots, responsive checks | Integration test, review PRs, merge, final regression report |


### Separate Final Verification Day (planned)

| Time | Final-check work | Evidence/output |
|---|---|---|
| 08:30–10:00 | Registration, login/logout, role-based access, patient and doctor profiles | Browser checks and failures list |
| 10:00–11:30 | Doctor search/details, availability, appointment booking, confirmation, booked-slot protection | Final workflow results/screenshots |
| 11:30–13:00 | Appointment history, cancellation, rescheduling, reminder safe check; check Task Scheduler remains disabled | Database, Mailtrap and UI evidence |
| 13:00–15:00 | All committed Sprint 5 administration stories; staff permissions and persistence | Story results and PHP syntax check |
| 15:00–17:00 | Final navigation/mobile layout, real data labels, no secrets in repository, sequence/use-case diagrams |  |
| 17:00–19:00 | Review source files, migration scripts and GitHub PRs; ensure local and GitHub `main` match | Repository verified |
| 19:00–21:00 | Final Scrum documents/Review feedback with Mineh and Husna, contribution summary, presentation rehearsal | Actual final review notes |
| By 22:00 | Final submission ready and uploaded, allowing a buffer before 23:59 | Submission confirmation |

## 5. Planned Testing and Integration

### US-17 — Patient records
- Authorized staff can list and open a fictional patient; update approved non-sensitive fields; reload and see saved values.
- A patient or doctor cannot open staff-only pages. Invalid inputs must be rejected without changing the original record.
- Ensure staff do not get unnecessary access to sensitive patient details.

### US-18 — Doctor records
- Authorized staff can list/open Test Doctor and update approved information; reload and see saved values.
- Patient-facing doctor details reflect permitted updates. Invalid edits and unauthorized access are rejected.
- Any doctor or receptionist account creation rules must be confirmed with Husna before implementation.

### US-19 — Staff appointments
- Authorized staff can view appointment date, doctor, patient, time and status.
- Complete at least one **PO-approved** staff action on a disposable booking, then verify the database state.
- Reject attempts to use an occupied slot, invalid status or unauthorized role; preserve transaction and slot integrity.

### US-20 — Clinic details (only if committed)
- Authorized staff can view, edit and reload approved clinic details.
- Missing/invalid fields are handled safely. Do not invent clinic contact data as real data.

