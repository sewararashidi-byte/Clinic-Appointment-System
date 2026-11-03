# Sprint 1 — Developer Pre-Sprint Plan

**Project:** Clinic Appointment System  
**Developers:** Sewara Rashidi and Sonbol Almas  
**Sprint duration:** 4–5 development days  
**Focus:** User Accounts and Authentication

## 1. Sprint Goal

Develop the foundational authentication functionality of the Clinic Appointment System so users can register, log in securely, and access functions according to their assigned roles.

## 2. User Stories and Acceptance Criteria

| Story | Feature | Acceptance criteria from Product Backlog |
|---|---|---|
| US-01 / FR-01 | User Registration | 1. User can enter required registration information.<br>2. System validates the information.<br>3. System creates the account when the information is valid. |
| US-02 / FR-02 | Secure Login | 1. User can enter login credentials.<br>2. System validates the credentials.<br>3. User is granted access when the credentials are valid. |
| US-03 / FR-03 | Role-Based Access | 1. System identifies the user's role after login.<br>2. System provides access according to the user's role.<br>3. Unauthorized functions are restricted. |

## 3. Division of Development Work

| **Area**                           | **Sewara**      | **Sonbol**      |
|------------------------------------|-----------------|-----------------|
| GitHub setup                       | Together        | Together        |
| Technology stack decision          | Together        | Together        |
| Project architecture               | Together        | Together        |
| Database design                    | **Lead**        | Review          |
| Backend authentication             | **Lead**        | Support         |
| Registration backend               | **Lead**        | Support         |
| Login backend                      | **Lead**        | Support         |
| Role-based authorization           | **Lead**        | Support         |
| Registration frontend              | Support         | **Lead**        |
| Login frontend                     | Support         | **Lead**        |
| UI/UX and styling                  | Review          | **Lead**        |
| Basic dashboards                   | Support         | **Lead**        |
| Main Use Case Diagram              | Review          | **Lead**        |
| Registration Sequence Diagram      | **Lead**        | Review          |
| Login Sequence Diagram             | **Lead**        | Review          |
| Role-Based Access Sequence Diagram | **Lead**        | Review          |
| Frontend/backend integration       | Together        | Together        |
| Testing                            | Together        | Together        |
| Bug fixing                         | Together        | Together        |
| Test documentation                 | Together        | Together        |
| Pull-request review                | Review Sonbol's | Review Sewara's |

## 4. Detailed Work Plan by Day

| Day | Development work | UML / Documentation |
|---|---|---|
| Day 1 | Divide responsibilities, clone GitHub repository, configure GitHub Desktop and VS Code, agree on collaboration workflow, discuss technology stack and roles | Plan main Use Case Diagram and identify the 3 Sprint 1 Sequence Diagrams |
| Day 2 | Initialize full-stack project, prepare database/user model, start registration frontend + backend | Create/refine overall Use Case Diagram + Registration Sequence Diagram |
| Day 3 | Complete registration and implement secure login | Create Login Sequence Diagram |
| Day 4 | Implement role-based access, basic dashboards and frontend/backend integration | Create Role-Based Access Sequence Diagram |
| Day 5 if needed | Full testing, fix bugs, polish UI, verify authentication flow | Review/update UML, document test results, prepare Sprint Review |

## 5. Planned Testing and Integration

- Frontend/backend integration — Together / Together.
- Testing — Together / Together.
- Bug fixing — Together / Together.
- Test documentation — Together / Together.
- Day 5 if needed: Full testing, fix bugs, polish UI, verify authentication flow.

## 6. UML and Documentation

| Diagram / document | Sewara | Sonbol |
|---|---|---|
| Main Use Case Diagram | Review | Lead |
| Registration Sequence Diagram | Lead | Review |
| Login Sequence Diagram | Lead | Review |
| Role-Based Access Sequence Diagram | Lead | Review |

Plan main Use Case Diagram and identify the 3 Sprint 1 Sequence Diagrams; create/refine diagrams alongside development; review/update UML and document test results.

## 7. Planning Decisions and Technical Notes

- GitHub setup — Together / Together.
- Technology stack decision — Together / Together.
- Project architecture — Together / Together.
- Pull-request review — Sewara reviews Sonbol's; Sonbol reviews Sewara's.
- The original plan sets a **4–5 day** schedule; Day 5 is conditional. GitHub Desktop and VS Code were selected for repository-based collaboration.
