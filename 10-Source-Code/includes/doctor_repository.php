
<?php

/**
 * US-05: Search doctors by name and/or specialty.
 *
 * Returns active doctors who have professional profiles.
 * An empty search returns all matching active doctors.
 */
function searchDoctors(
    mysqli $conn,
    string $doctorName = '',
    string $specialty = ''
): array {

    $doctorName = trim($doctorName);
    $specialty = trim($specialty);

    $sql = "
        SELECT
            u.id AS doctor_id,
            u.full_name,
            dp.specialty,
            dp.qualifications,
            dp.years_experience,
            dp.biography
        FROM users AS u
        INNER JOIN doctor_profiles AS dp
            ON dp.user_id = u.id
        WHERE u.role = 'doctor'
          AND u.account_status = 'active'
          AND (? = '' OR u.full_name LIKE ?)
          AND (? = '' OR dp.specialty LIKE ?)
        ORDER BY u.full_name ASC
    ";

    $namePattern = '%' . $doctorName . '%';
    $specialtyPattern = '%' . $specialty . '%';

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'ssss',
        $doctorName,
        $namePattern,
        $specialty,
        $specialtyPattern
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $doctors = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $doctors;
}

/**
 * US-06: Retrieve professional information for one doctor.
 *
 * Returns null when the doctor does not exist,
 * is inactive, or has no professional profile.
 */
function getDoctorById(mysqli $conn, int $doctorId): ?array
{
    if ($doctorId <= 0) {
        return null;
    }

    $sql = "
        SELECT
            u.id AS doctor_id,
            u.full_name,
            dp.specialty,
            dp.qualifications,
            dp.years_experience,
            dp.biography
        FROM users AS u
        INNER JOIN doctor_profiles AS dp
            ON dp.user_id = u.id
        WHERE u.id = ?
          AND u.role = 'doctor'
          AND u.account_status = 'active'
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();

    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();

    $stmt->close();

    return $doctor ?: null;
}

/**
 * US-06: Retrieve current and future doctor availability.
 *
 * Returns the doctor's availability ranges,
 * ordered by date and start time.
 */
function getDoctorAvailability(mysqli $conn, int $doctorId): array
{
    if ($doctorId <= 0) {
        return [];
    }

    $sql = "
        SELECT
            da.id AS availability_id,
            da.availability_date,
            da.start_time,
            da.end_time,
            da.slot_duration_minutes
        FROM doctor_availability AS da
        INNER JOIN users AS u
            ON u.id = da.doctor_id
        WHERE da.doctor_id = ?
          AND u.role = 'doctor'
          AND u.account_status = 'active'
          AND (
              da.availability_date > CURDATE()
              OR (
                  da.availability_date = CURDATE()
                  AND da.end_time > CURTIME()
              )
          )
        ORDER BY
            da.availability_date ASC,
            da.start_time ASC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('i', $doctorId);

    $stmt->execute();

    $result = $stmt->get_result();

    $availability = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $availability;
}

