

<?php


/**
 * Sprint 5 — US-17: Patient Information Management
 * Staff Patient Editing Interface
 * Frontend integration: Sonbol
 */


require_once __DIR__ . '/../config/auth.php';


requireRole(['receptionist', 'administrator']);


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_patient_repository.php';


// Validate the selected patient ID.
$patientId = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);


if (!is_int($patientId)) {
    http_response_code(400);
    exit('Invalid patient ID.');
}


// Retrieve patient information.
$patient = getStaffPatientById($conn, $patientId);


$conn->close();


if ($patient === null) {
    http_response_code(404);
    exit('Patient not found.');
}


// Create the existing CSRF token.
if (empty($_SESSION['staff_patient_csrf_token'])) {
    $_SESSION['staff_patient_csrf_token'] =
        bin2hex(random_bytes(32));
}


// Read one-time messages.
$success = $_SESSION['staff_patient_success'] ?? '';
$error = $_SESSION['staff_patient_error'] ?? '';


unset(
    $_SESSION['staff_patient_success'],
    $_SESSION['staff_patient_error']
);


// Escape displayed values.
function escapePatientEdit($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
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


    <title>Edit Patient | Clinic Appointment System</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        href="../assets/css/style.css"
        rel="stylesheet"
    >


    <style>
        .staff-navbar {
            background: #13385b;
            padding: 18px 0;
        }


        .staff-brand {
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 700;
        }


        .staff-brand:hover {
            color: #a3eee1;
            text-decoration: none;
        }


        .edit-container {
            max-width: 850px;
        }


        .page-title {
            color: #173e61;
            font-size: 2rem;
            font-weight: 700;
        }


        .page-description {
            color: #63788b;
        }


        .back-link {
            color: #087d85;
            font-weight: 700;
        }


        .back-link:hover {
            color: #075a60;
        }


        .edit-card {
            background: white;
            border: 1px solid #e0eaf0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 12px 32px rgba(
                25, 71, 103, 0.08
            );
        }


        .edit-card-header {
            padding: 25px 30px;
            background: #f4f9fb;
            border-bottom: 1px solid #e4edf2;
        }


        .edit-card-header h2 {
            color: #173e61;
            font-size: 1.2rem;
            font-weight: 700;
        }


        .edit-card-body {
            padding: 32px;
        }


        .section-heading {
            color: #173e61;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 20px;
        }


        .form-label {
            font-size: 0.9rem;
            color: #344e63;
            font-weight: 600;
            margin-bottom: 8px;
        }


        .form-control {
            min-height: 46px;
            border: 1px solid #d5e0e8;
            border-radius: 10px;
            padding: 10px 14px;
        }


        .form-control:focus {
            border-color: #16888e;
            box-shadow: 0 0 0 0.2rem rgba(
                22, 136, 142, 0.13
            );
        }


        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }


        .btn-save {
            background: #087d85;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 22px;
            font-weight: 700;
        }


        .btn-save:hover {
            background: #08656b;
            color: white;
        }


        .btn-back {
            background: #eef3f6;
            color: #38536a;
            border-radius: 10px;
            padding: 12px 22px;
            font-weight: 700;
        }


        .btn-back:hover {
            background: #dfe9ef;
            color: #173e61;
        }


        .privacy-note {
            background: #edf8f7;
            border: 1px solid #d5eeea;
            border-radius: 12px;
            padding: 15px 18px;
            color: #376a66;
            font-size: 0.88rem;
        }


        .required-mark {
            color: #b73a43;
        }


        @media (max-width: 576px) {
            .page-title {
                font-size: 1.55rem;
            }


            .edit-card-header {
                padding: 20px;
            }


            .edit-card-body {
                padding: 20px;
            }


            .btn-save,
            .btn-back {
                width: 100%;
                text-align: center;
            }
        }
    </style>


</head>


<body>


<nav class="staff-navbar">
    <div class="container">


        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">


            <a
                href="../dashboards/admin-dashboard.php"
                class="staff-brand"
            >
                🏥 Clinic Appointment System
            </a>


            <a
                href="../logout.php"
                class="btn btn-outline-light btn-sm"
            >
                Logout
            </a>


        </div>


    </div>
</nav>


<main class="container edit-container py-5">


    <a href="patients.php" class="back-link">
        &larr; Back to Patient Management
    </a>


    <div class="mt-4 mb-4">


        

        <h1 class="page-title">
            Edit Patient Information
        </h1>


        <p class="page-description">
            Update approved general patient details
            and contact information.
        </p>


    </div>


    <?php if ($success !== ''): ?>


        <div class="alert alert-success" role="status">
            <?= escapePatientEdit($success) ?>
        </div>


    <?php endif; ?>


    <?php if ($error !== ''): ?>


        <div class="alert alert-danger" role="alert">
            <?= escapePatientEdit($error) ?>
        </div>


    <?php endif; ?>


    <section class="edit-card">


        <div class="edit-card-header">


            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">


                <div>
                    <h2 class="mb-1">
                        Patient Details
                    </h2>


                    <p class="text-muted small mb-0">
                        Edit the permitted information below.
                    </p>
                </div>


                <span class="badge bg-primary rounded-pill px-3 py-2">
                    Patient #<?= (int) $patient['id'] ?>
                </span>


            </div>


        </div>


        <div class="edit-card-body">


            <form
                method="POST"
                action="../actions/staff_patient_update.php"
            >


                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapePatientEdit(
                        $_SESSION['staff_patient_csrf_token']
                    ) ?>"
                >


                <input
                    type="hidden"
                    name="patient_id"
                    value="<?= (int) $patient['id'] ?>"
                >


                <h3 class="section-heading">
                    Personal Information
                </h3>


                <div class="row g-3 mb-4">


                    <div class="col-12 col-md-8">


                        <label
                            for="fullName"
                            class="form-label"
                        >
                            Full Name
                            <span class="required-mark">*</span>
                        </label>


                        <input
                            id="fullName"
                            type="text"
                            name="full_name"
                            class="form-control"
                            maxlength="100"
                            autocomplete="name"
                            required
                            value="<?= escapePatientEdit(
                                $patient['full_name']
                            ) ?>"
                        >


                    </div>


                    <div class="col-12 col-md-4">


                        <label
                            for="patientAge"
                            class="form-label"
                        >
                            Age
                        </label>


                        <input
                            id="patientAge"
                            type="number"
                            name="age"
                            class="form-control"
                            min="1"
                            max="120"
                            value="<?= escapePatientEdit(
                                $patient['age']
                            ) ?>"
                        >


                    </div>


                </div>


                <hr class="my-4">


                <h3 class="section-heading">
                    Contact Information
                </h3>


                <div class="row g-3">


                    <div class="col-12 col-md-6">


                        <label
                            for="patientPhone"
                            class="form-label"
                        >
                            Phone Number
                            <span class="required-mark">*</span>
                        </label>


                        <input
                            id="patientPhone"
                            type="tel"
                            name="phone"
                            class="form-control"
                            maxlength="20"
                            autocomplete="tel"
                            required
                            value="<?= escapePatientEdit(
                                $patient['phone']
                            ) ?>"
                        >


                    </div>


                    <div class="col-12 col-md-6">


                        <label
                            for="patientEmail"
                            class="form-label"
                        >
                            Email Address
                            <span class="required-mark">*</span>
                        </label>


                        <input
                            id="patientEmail"
                            type="email"
                            name="email"
                            class="form-control"
                            maxlength="150"
                            autocomplete="email"
                            required
                            value="<?= escapePatientEdit(
                                $patient['email']
                            ) ?>"
                        >


                    </div>


                    <div class="col-12">


                        <label
                            for="homeAddress"
                            class="form-label"
                        >
                            Home Address
                            <span class="text-muted fw-normal">
                                (Optional)
                            </span>
                        </label>


                        <textarea
                            id="homeAddress"
                            name="home_address"
                            class="form-control"
                            maxlength="255"
                            rows="3"
                            autocomplete="street-address"
                            placeholder="Enter patient's home address"
                        ><?= escapePatientEdit(
                            $patient['home_address'] ?? ''
                        ) ?></textarea>


                        <div class="form-text">
                            Leave blank if no address is available.
                        </div>


                    </div>


                </div>


                <div class="privacy-note mt-4">
                    <strong>Privacy:</strong>
                    This form updates general patient
                    information only. Sensitive medical
                    information is not displayed or changed.
                </div>


                <div class="d-flex flex-wrap gap-3 mt-4">


                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        Save Patient Changes
                    </button>


                    <a
                        href="patients.php"
                        class="btn btn-back"
                    >
                        Cancel / Back
                    </a>


                </div>


            </form>


        </div>


    </section>


    <p class="text-muted small text-center mt-4">
        Clinic Appointment System
        &middot;
        Patient Information Management
    </p>


</main>


</body>
</html>