
<?php

/**
 * Sprint 5 — US-18: Doctor Information Management
 *
 * Database functions for staff doctor management.
 * Calling pages must check staff authorization.
 */

/**
 * Retrieve registered doctors and professional details.
 */
function getStaffDoctors(mysqli $conn): array
{
    $sql = "
        SELECT
            u.id,
            u.full_name,
            u.email,
            u.phone,
            u.account_status,
            dp.specialty,
            dp.qualifications,
            dp.years_experience,
            dp.biography
        FROM users AS u
        LEFT JOIN doctor_profiles AS dp
            ON dp.user_id = u.id
        WHERE u.role = 'doctor'
        ORDER BY u.full_name ASC, u.id ASC
    ";

    $result = $conn->query($sql);

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Retrieve one doctor by user ID.
 */
function getStaffDoctorById(
    mysqli $conn,
    int $doctorId
): ?array {

    if ($doctorId <= 0) {
        return null;
    }

    $sql = "
        SELECT
            u.id,
            u.full_name,
            u.email,
            u.phone,
            u.account_status,
            dp.specialty,
            dp.qualifications,
            dp.years_experience,
            dp.biography,
            dp.consultation_room
        FROM users AS u
        LEFT JOIN doctor_profiles AS dp
            ON dp.user_id = u.id
        WHERE u.id = ?
          AND u.role = 'doctor'
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();

    $doctor = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $doctor ?: null;
}

/**
 * Sprint 5 — US-18
 * Update a doctor's administrative contact information.
 *
 * Only phone and email can be changed through this function.
 * The calling action must verify staff role and CSRF token.
 */
function updateStaffDoctorContact(
    mysqli $conn,
    int $doctorId,
    string $phone,
    string $email
): bool {

    $phone = trim($phone);
    $email = trim($email);

    if ($doctorId <= 0) {
        throw new DomainException('Invalid doctor.');
    }

    if (!preg_match('/^[0-9+() .-]{7,20}$/', $phone)) {
        throw new DomainException('Invalid phone number.');
    }

    if (
        strlen($email) > 150 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        throw new DomainException('Invalid email address.');
    }

    $sql = "
        UPDATE users
        SET
            phone = ?,
            email = ?
        WHERE id = ?
          AND role = 'doctor'
    ";

    $stmt = $conn->prepare($sql);

    try {
        $stmt->bind_param(
            'ssi',
            $phone,
            $email,
            $doctorId
        );

        $stmt->execute();

        return $stmt->affected_rows === 1;

    } finally {
        $stmt->close();
    }
}

/**
 * Sprint 5 — US-18
 * Update a doctor's consultation room.
 *
 * The calling action must verify staff authorization
 * and CSRF protection.
 */
function updateStaffDoctorConsultationRoom(
    mysqli $conn,
    int $doctorId,
    ?string $consultationRoom
): bool {

    if ($doctorId <= 0) {
        throw new DomainException('Invalid doctor.');
    }

    // The consultation room is optional.
    if ($consultationRoom !== null) {
        $consultationRoom = trim($consultationRoom);

        if (strlen($consultationRoom) > 100) {
            throw new DomainException(
                'Consultation room must not exceed 100 characters.'
            );
        }

        if ($consultationRoom === '') {
            $consultationRoom = null;
        }
    }

    // Verify that the doctor has a profile.
    $checkSql = "
        SELECT dp.id
        FROM doctor_profiles AS dp
        INNER JOIN users AS u
            ON u.id = dp.user_id
        WHERE dp.user_id = ?
          AND u.role = 'doctor'
        LIMIT 1
    ";

    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('i', $doctorId);
    $checkStmt->execute();

    $profile = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if (!$profile) {
        throw new DomainException(
            'Doctor profile not found.'
        );
    }

    // Update only the approved room assignment.
    $sql = "
        UPDATE doctor_profiles
        SET consultation_room = ?
        WHERE user_id = ?
    ";

    $stmt = $conn->prepare($sql);

    try {
        $stmt->bind_param(
            'si',
            $consultationRoom,
            $doctorId
        );

        $stmt->execute();

        return $stmt->affected_rows === 1;

    } finally {
        $stmt->close();
    }
}
