<?php

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

// Connect to MySQL and the doctor search backend.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/doctor_repository.php';

$fullName = $_SESSION['full_name'] ?? 'Patient';

// Read doctor search criteria from the form.
$doctorName = isset($_GET['doctor_name']) && is_string($_GET['doctor_name'])
    ? trim($_GET['doctor_name'])
    : '';

$specialty = isset($_GET['specialty']) && is_string($_GET['specialty'])
    ? trim($_GET['specialty'])
    : '';

// Search only after the patient submits the form.
$hasSearched = isset($_GET['doctor_name'])
    || isset($_GET['specialty']);

$doctors = [];

if ($hasSearched) {
    $doctors = searchDoctors($conn, $doctorName, $specialty);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Doctor Search - Clinic Appointment System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2>Search Doctors</h2>

            <p class="text-muted mb-0">
                Logged in as
                <strong>
                    <?php
                    echo htmlspecialchars(
                        $fullName,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
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

        <h4 class="mb-4">Find a Doctor</h4>

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
                        value="<?php
                            echo htmlspecialchars(
                                $doctorName,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                        ?>"
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
                        value="<?php
                            echo htmlspecialchars(
                                $specialty,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                        ?>"
                    >

                </div>

            </div>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Search Doctors
            </button>

            <div class="form-text mt-2">
                Search by doctor name, medical specialty, or both.
            </div>

        </form>

        <?php if ($hasSearched): ?>

            <div class="mt-4">

                <hr>

                <h4 class="mb-3">Search Results</h4>

                <?php if (empty($doctors)): ?>

                    <div class="alert alert-info">
                        No doctors found matching your search criteria.
                    </div>

                <?php else: ?>

                    <p class="text-muted">
                        <?php echo count($doctors); ?>
                        doctor(s) found.
                    </p>

                    <div class="row g-3">

                        <?php foreach ($doctors as $doctor): ?>

                            <div class="col-md-6">

                                <div class="card h-100">

                                    <div class="card-body">

                                        <h5 class="card-title">
                                            <?php
                                            echo htmlspecialchars(
                                                $doctor['full_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </h5>

                                        <p class="card-text">
                                            <strong>Specialty:</strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $doctor['specialty'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </p>

                                        <p class="card-text">
                                            <strong>Qualifications:</strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $doctor['qualifications'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </p>

                                        <!-- View Doctor Details -->

                                        <a
                                            href="doctor-details.php?doctor_id=<?php echo (int) $doctor['doctor_id']; ?>"
                                            class="btn btn-outline-primary"
                                        >
                                            View Doctor
                                        </a>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>