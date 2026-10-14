<?php
session_start();

$loginError = $_SESSION['login_error'] ?? '';
$oldEmail = $_SESSION['old_login_email'] ?? '';

unset($_SESSION['login_error']);
unset($_SESSION['old_login_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Clinic Appointment System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="container mt-5">
    <div class="row justify-content-center">

        <div class="col-md-6 col-lg-5">

            <div class="card shadow p-4">

                <h2 class="text-center mb-4">
                    Login
                </h2>

                <?php if ($loginError !== ''): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($loginError); ?>
                    </div>
                <?php endif; ?>

                <form
                    action="actions/login_process.php"
                    method="POST"
                    novalidate
                >

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Email Address
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($oldEmail); ?>"
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
                    </div>

                    <div class="d-grid">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Login
                        </button>
                    </div>

                </form>

                <p class="text-center mt-3 mb-0">
                    Don't have an account?
                    <a href="register.php">Register here</a>
                </p>

            </div>

        </div>

    </div>
</div>

</body>
</html>