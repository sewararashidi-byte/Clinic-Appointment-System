<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

/* Check required fields */
if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Email and password are required.';
    $_SESSION['old_login_email'] = $email;

    header('Location: ../login.php');
    exit;
}

/* Validate email format */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error'] = 'Please enter a valid email address.';
    $_SESSION['old_login_email'] = $email;

    header('Location: ../login.php');
    exit;
}

/* Find user by email */
$stmt = $conn->prepare(
    'SELECT id, full_name, email, password_hash, role, account_status
     FROM users
     WHERE email = ?
     LIMIT 1'
);

$stmt->bind_param('s', $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

/* Invalid email or password */
if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['login_error'] = 'Invalid email or password.';
    $_SESSION['old_login_email'] = $email;

    $stmt->close();
    $conn->close();

    header('Location: ../login.php');
    exit;
}

/* Pending account */
if ($user['account_status'] === 'pending') {
    $_SESSION['login_error'] =
        'Your account is awaiting administrator approval.';

    $_SESSION['old_login_email'] = $email;

    $stmt->close();
    $conn->close();

    header('Location: ../login.php');
    exit;
}

/* Rejected account */
if ($user['account_status'] === 'rejected') {
    $_SESSION['login_error'] =
        'Your account is not approved for access.';

    $_SESSION['old_login_email'] = $email;

    $stmt->close();
    $conn->close();

    header('Location: ../login.php');
    exit;
}

/* Only active accounts may continue */
if ($user['account_status'] !== 'active') {
    $_SESSION['login_error'] =
        'Your account is not currently active.';

    header('Location: ../login.php');
    exit;
}

/* Secure the authenticated session */
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

unset($_SESSION['old_login_email']);

/* Redirect according to role */
switch ($user['role']) {
    case 'patient':
        header('Location: ../dashboards/patient-dashboard.php');
        break;

    case 'doctor':
        header('Location: ../dashboards/doctor-dashboard.php');
        break;

    case 'receptionist':
    case 'administrator':
        header('Location: ../dashboards/admin-dashboard.php');
        break;

    default:
        session_unset();
        session_destroy();

        header('Location: ../login.php');
        break;
}

$stmt->close();
$conn->close();

exit;
?>