# SD-01 User Registration Sequence Diagram Plan

## Related User Story
US-01 - User Registration

## Participants
- User
- Registration Page
- Registration Controller / PHP
- MySQL Database

## Main Sequence

1. User opens the registration page.
2. Registration page displays the registration form.
3. User enters the required registration information and submits the form.
4. Registration page sends the submitted data to the registration controller.
5. Registration controller validates the submitted information.
6. Registration controller checks the database for an existing email address.
7. Database returns the email-check result.
8. Registration controller securely hashes the password.
9. Registration controller sends the new user information to the database.
10. Database stores the user account.
11. Registration controller returns a successful registration response.
12. Registration page displays an account-created confirmation to the user.

## Alternative Flows

- Invalid information: The system displays a validation error.
- Duplicate email: The system informs the user that the email is already registered.

## Implementation Note

The final sequence diagram will be reviewed after US-01 is implemented to ensure that the UML model matches the actual PHP and MySQL implementation.