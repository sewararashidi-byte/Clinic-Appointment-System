
<?php

/**
 * Sprint 3 - US-04: Patient Profile Management
 *
 * Retrieve the profile information of a patient.
 *
 * The patient ID will be obtained from the authenticated
 * session by the calling code, not from user-submitted
 * profile identifiers.
 */

function getPatientProfile(
    mysqli $conn,
    int $patientId
): ?array {

    if ($patientId <= 0) {
        return null;
    }

    $sql = "
        SELECT
            id,
            full_name,
            age,
            phone,
            blood_type,
            disease_type,
            close_relatives_count
        FROM users
        WHERE id = ?
          AND role = 'patient'
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'i',
        $patientId
    );

    $stmt->execute();

    $profile = $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();

    return $profile ?: null;
}

/**
 * Sprint 3 - US-04: Patient Profile Management
 *
 * Update the profile of an existing patient.
 *
 * Input validation is performed by the calling action.
 * The patient ID must come from the authenticated session.
 */
function updatePatientProfile(
    mysqli $conn,
    int $patientId,
    string $fullName,
    string $phone,
    ?int $age,
    ?string $bloodType,
    ?string $diseaseType,
    ?int $closeRelativesCount
): bool {

    if ($patientId <= 0) {
        return false;
    }

    $sql = "
        UPDATE users
        SET
            full_name = ?,
            phone = ?,
            age = ?,
            blood_type = ?,
            disease_type = ?,
            close_relatives_count = ?
        WHERE id = ?
          AND role = 'patient'
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'ssissii',
        $fullName,
        $phone,
        $age,
        $bloodType,
        $diseaseType,
        $closeRelativesCount,
        $patientId
    );

    $success = $stmt->execute();

    $stmt->close();

    return $success;
}
