

<?php


/**
 * Doctor Information Editing
 * Staff frontend interface.
 */


require_once __DIR__ . '/../config/auth.php';


requireRole(['receptionist', 'administrator']);


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_doctor_repository.php';


// Validate doctor ID.
$doctorId = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);


if (!is_int($doctorId)) {
    http_response_code(400);
    exit('Invalid doctor ID.');
}


// Retrieve the selected doctor.
$doctor = getStaffDoctorById($conn, $doctorId);


$conn->close();


if ($doctor === null) {
    http_response_code(404);
    exit('Doctor not found.');
}


// Preserve existing CSRF protection.
if (empty($_SESSION['staff_doctor_csrf_token'])) {
    $_SESSION['staff_doctor_csrf_token'] =
        bin2hex(random_bytes(32));
}


// Read one-time feedback messages.
$success = $_SESSION['staff_doctor_success'] ?? '';
$error = $_SESSION['staff_doctor_error'] ?? '';


unset(
    $_SESSION['staff_doctor_success'],
    $_SESSION['staff_doctor_error']
);


function escapeDoctorEdit($value): string
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


    <title>Edit Doctor | Clinic Appointment System</title>


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
            background: #ffffff;
            border: 1px solid #e0eaf0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 12px 32px rgba(25, 71, 103, 0.08);
        }


        .edit-card-header {
            background: #f4f9fb;
            border-bottom: 1px solid #e4edf2;
            padding: 25px 30px;
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
            color: #344e63;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
        }


        .form-control {
            min-height: 46px;
            padding: 10px 14px;
            border: 1px solid #d5e0e8;
            border-radius: 10px;
        }


        .form-control:focus {
            border-color: #16888e;
            box-shadow: 0 0 0 0.2rem rgba(22, 136, 142, 0.13);
        }


        .form-control[readonly] {
            background: #f3f6f8;
            color: #687c8c;
            cursor: default;
        }


        .btn-save {
            background: #087d85;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px 22px;
            font-weight: 700;
        }


        .btn-save:hover {
            background: #08656b;
            color: #ffffff;
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


        .info-note {
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


            .edit-card-header,
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


    <a href="doctors.php" class="back-link">
        &larr; Back to Doctor Management
    </a>


    <div class="mt-4 mb-4">
        <h1 class="page-title">
            Edit Doctor Information
        </h1>


        <p class="page-description">
            Review doctor details and update contact
            information or consultation room assignments.
        </p>
    </div>


    <?php if ($success !== ''): ?>
        <div class="alert alert-success" role="status">
            <?= escapeDoctorEdit($success) ?>
        </div>
    <?php endif; ?>


    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= escapeDoctorEdit($error) ?>
        </div>
    <?php endif; ?>


    <section class="edit-card">


        <div class="edit-card-header">


            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">


                <div>
                    <h2 class="mb-1">Doctor Details</h2>
                    <p class="text-muted small mb-0">
                        Update the permitted information below.
                    </p>
                </div>


                <span class="badge bg-primary rounded-pill px-3 py-2">
                    Doctor #<?= (int) $doctor['id'] ?>
                </span>


            </div>
        </div>


        <div class="edit-card-body">


            <form
                method="POST"
                action="../actions/staff_doctor_update.php"
            >


                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapeDoctorEdit(
                        $_SESSION['staff_doctor_csrf_token']
                    ) ?>"
                >


                <input
                    type="hidden"
                    name="doctor_id"
                    value="<?= (int) $doctor['id'] ?>"
                >


                <h3 class="section-heading">
                    Professional Information
                </h3>


                <div class="row g-3 mb-4">


                    <div class="col-12 col-md-6">


                        <label
                            for="doctorName"
                            class="form-label"
                        >
                            Doctor Name
                        </label>


                        <input
                            id="doctorName"
                            type="text"
                            class="form-control"
                            value="<?= escapeDoctorEdit(
                                $doctor['full_name']
                            ) ?>"
                            readonly
                        >


                    </div>


                    <div class="col-12 col-md-6">


                        <label
                            for="specialty"
                            class="form-label"
                        >
                            Specialization
                        </label>


                        <input
                            id="specialty"
                            type="text"
                            class="form-control"
                            value="<?= escapeDoctorEdit(
                                $doctor['specialty'] ?? ''
                            ) ?>"
                            readonly
                        >


                    </div>


                    <div class="col-12">


                        <label
                            for="consultationRoom"
                            class="form-label"
                        >
                            Consultation Room
                            <span class="text-muted fw-normal">
                                (Optional)
                            </span>
                        </label>


                        <input
                            id="consultationRoom"
                            type="text"
                            name="consultation_room"
                            class="form-control"
                            maxlength="100"
                            placeholder="e.g. Room 101"
                            value="<?= escapeDoctorEdit(
                                $doctor['consultation_room'] ?? ''
                            ) ?>"
                        >


                        <div class="form-text">
                            Assign a consultation room or leave blank.
                        </div>


                    </div>


                </div>


                <hr class="my-4">


                <h3 class="section-heading">
                    Contact Information
                </h3>


                <div class="row g-3">


                    <div class="col-12 col-md-6">


                        <label
                            for="doctorPhone"
                            class="form-label"
                        >
                            Phone Number
                            <span class="required-mark">*</span>
                        </label>


                        <input
                            id="doctorPhone"
                            type="tel"
                            name="phone"
                            class="form-control"
                            maxlength="20"
                            autocomplete="tel"
                            required
                            value="<?= escapeDoctorEdit(
                                $doctor['phone']
                            ) ?>"
                        >


                    </div>


                    <div class="col-12 col-md-6">


                        <label
                            for="doctorEmail"
                            class="form-label"
                        >
                            Email Address
                            <span class="required-mark">*</span>
                        </label>


                        <input
                            id="doctorEmail"
                            type="email"
                            name="email"
                            class="form-control"
                            maxlength="150"
                            autocomplete="email"
                            required
                            value="<?= escapeDoctorEdit(
                                $doctor['email']
                            ) ?>"
                        >


                    </div>


                </div>


                <div class="info-note mt-4">
                    <strong>Information:</strong>
                    Doctor names and specializations are
                    displayed for reference. This form updates
                    contact details and consultation room
                    assignments only.
                </div>


                <div class="d-flex flex-wrap gap-3 mt-4">


                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        Save Doctor Changes
                    </button>


                    <a
                        href="doctors.php"
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
        Doctor Management
    </p>


</main>


</body>
</html>