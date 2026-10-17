<?php

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

$fullName = $_SESSION['full_name'] ?? 'Patient';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Doctor Search - Clinic Appointment System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Search Doctors
            </h2>

            <p class="text-muted mb-0">
                Logged in as
                <strong>
                    <?php echo htmlspecialchars($fullName); ?>
                </strong>
            </p>

        </div>

        <a
            href="../dashboards/patient-dashboard.php"
            class="btn btn-secondary"
        >
            Back to Dashboard
        </a>

    </div>


    <div class="card shadow p-4">

        <h4 class="mb-4">
            Find a Doctor
        </h4>

        <form method="GET">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label
                        for="doctor_name"
                        class="form-label"
                    >
                        Doctor Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="doctor_name"
                        name="doctor_name"
                        placeholder="Enter doctor name"
                    >

                </div>


                <div class="col-md-6 mb-3">

                    <label
                        for="specialty"
                        class="form-label"
                    >
                        Medical Specialty
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="specialty"
                        name="specialty"
                        placeholder="Enter specialty"
                    >

                </div>

            </div>


            <button
                type="submit"
                class="btn btn-primary"
                disabled
            >
                Search
            </button>

            <div class="form-text mt-2">
                Doctor search functionality will be implemented in Sprint 2 Day 2.
            </div>

        </form>

    </div>

</div>

</body>

</html>