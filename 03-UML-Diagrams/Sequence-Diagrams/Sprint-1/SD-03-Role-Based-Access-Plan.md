# SD-03 - Role-Based Access Sequence Diagram

## Related Requirement
FR-03 - Role-Based Access

## Related User Story
US-03 - Role-Based Access

## Actor
Authenticated User

## System Participants

1. Authenticated User
2. Requested Dashboard
3. Authorization Component (`auth.php`)
4. Session
5. Role-Specific Dashboard

## Main Authorization Flow

1. The authenticated user requests a protected dashboard.
2. The requested dashboard loads the authorization component.
3. `auth.php` checks whether the user has an authenticated session.
4. The system reads the user's role from the session.
5. The system compares the user's role with the roles allowed for the requested page.
6. If the user's role is authorized, access to the requested dashboard is granted.
7. The requested page is displayed to the user.

## Alternative Flow - User Not Logged In

If no authenticated session exists:

- Access is denied.
- The system redirects the user to the login page.
- A message is displayed asking the user to log in.

## Alternative Flow - Wrong Role

If the user is logged in but their role is not allowed for the requested page:

- Access to the requested dashboard is denied.
- An unauthorized-access message is stored.
- The user is redirected to the correct dashboard for their own role.

## Role Access Rules

### Patient
Allowed dashboard:

`patient-dashboard.php`

### Doctor
Allowed dashboard:

`doctor-dashboard.php`

### Receptionist
Allowed dashboard:

`admin-dashboard.php`

### Administrator
Allowed dashboard:

`admin-dashboard.php`

## Security Notes

- Protected pages require an authenticated session.
- Role information is taken from the authenticated session.
- Users cannot access protected pages belonging to another role.
- Unauthorized users are redirected rather than being allowed to continue.
- Receptionist and administrator roles currently share the staff/admin dashboard while remaining separate roles internally.

## Implementation Status

Created during Sprint 1 Day 4 to document the implemented US-03 authorization flow using `auth.php`.