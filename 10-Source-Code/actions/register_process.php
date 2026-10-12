<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$age = trim($_POST['age'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

/* Required fields */
if (
    $full_name === '' ||
    $age === '' ||
    $phone === '' ||
    $email === '' ||
    $password === '' ||
    $confirm_password === ''
) {
    $errors[] = 'All registration fields are required.';
}

/* Age validation */
if ($age !== '' && (!ctype_digit($age) || (int)$age <= 0)) {
    $errors[] = 'Please enter a valid age.';
}

/* Email validation */
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

/* Password validation */
if ($password !== '') {
    if (strlen($password) < 8) {
        $errors[] = 'Password must contain at least 8 characters.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }
}

/* Confirm password */
if ($password !== $confirm_password) {
    $errors[] = 'Password and confirm password do not match.';
}

/* Check whether email already exists */
if (empty($errors)) {
    $check = $conn->prepare(
        'SELECT id FROM users WHERE email = ? LIMIT 1'
    );

    $check->bind_param('s', $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $errors[] = 'An account with this email already exists.';
    }

    $check->close();
}

/* Return errors to registration page */
if (!empty($errors)) {
    $_SESSION['registration_errors'] = $errors;

    $_SESSION['old_registration'] = [
        'full_name' => $full_name,
        'age' => $age,
        'phone' => $phone,
        'email' => $email
    ];

    header('Location: ../register.php');
    exit;
}

/* Securely hash password */
$password_hash = password_hash($password, PASSWORD_DEFAULT);

/* Public registration is always a patient */
$role = 'patient';

/* New accounts wait for administrator approval */
$account_status = 'pending';

/* Insert user */
$stmt = $conn->prepare(
    'INSERT INTO users
    (full_name, age, phone, email, password_hash, role, account_status)
    VALUES (?, ?, ?, ?, ?, ?, ?)'
);

$age_integer = (int)$age;

$stmt->bind_param(
    'sisssss',
    $full_name,
    $age_integer,
    $phone,
    $email,
    $password_hash,
    $role,
    $account_status
);

if ($stmt->execute()) {
    $_SESSION['registration_success'] =
        'Registration submitted successfully. Your account is awaiting administrator approval.';

    unset($_SESSION['old_registration']);

    header('Location: ../register.php');
    exit;
}

$_SESSION['registration_errors'] = [
    'Registration could not be completed. Please try again.'
];

header('Location: ../register.php');

$stmt->close();
$conn->close();

exit;
?>