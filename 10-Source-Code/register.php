<?php
session_start();

$errors = $_SESSION['registration_errors'] ?? [];
$success = $_SESSION['registration_success'] ?? '';
$old = $_SESSION['old_registration'] ?? [];

unset($_SESSION['registration_errors']);
unset($_SESSION['registration_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Patient Registration | Clinic Appointment System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7 col-md-9">

            <div class="card shadow border-0">

                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">
                        <h1 class="h3">Create Patient Account</h1>

                        <p class="text-muted">
                            Register to access the Clinic Appointment System.
                        </p>
                    </div>

                    <?php if ($success): ?>

                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                        </div>

                    <?php endif; ?>


                    <?php if (!empty($errors)): ?>

                        <div class="alert alert-danger">

                            <strong>Please correct the following:</strong>

                            <ul class="mb-0 mt-2">

                                <?php foreach ($errors as $error): ?>

                                    <li><?= htmlspecialchars($error) ?></li>

                                <?php endforeach; ?>

                            </ul>

                        </div>

                    <?php endif; ?>


                    <form
                        id="registrationForm"
                        action="actions/register_process.php"
                        method="POST"
                        novalidate
                    >

                        <div class="mb-3">

                            <label for="full_name" class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="full_name"
                                name="full_name"
                                value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                                required
                            >

                        </div>


                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label for="age" class="form-label">
                                    Age
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="age"
                                    name="age"
                                    min="1"
                                    value="<?= htmlspecialchars($old['age'] ?? '') ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label for="phone" class="form-label">
                                    Phone Number
                                </label>

                                <input
                                    type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                                    required
                                >

                            </div>

                        </div>


                        <div class="mb-3">

                            <label for="email" class="form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label for="password" class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                required
                            >

                            <div class="form-text">
                                Minimum 8 characters, including one uppercase letter
                                and one special character.
                            </div>

                        </div>


                        <div class="mb-4">

                            <label for="confirm_password" class="form-label">
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="confirm_password"
                                name="confirm_password"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Create Account
                        </button>

                    </form>


                    <div class="text-center mt-4">

                        <p class="mb-0">
                            Already have an account?
                            <a href="login.php">Login</a>
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script src="assets/js/register-validation.js"></script>

</body>
</html>