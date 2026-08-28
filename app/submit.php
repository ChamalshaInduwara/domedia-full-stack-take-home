<?php

require 'db.php';

header('Content-Type: application/json');

$response = array();

// Collect submitted values safely
$name        = $_POST['name'] ?? '';
$email       = $_POST['email'] ?? '';
$phone       = $_POST['phone'] ?? '';
$workshop_id = $_POST['workshop_id'] ?? '';
$seats       = $_POST['seats'] ?? '';

// Validate input
$errors = array();

if (empty(trim($name))) {
    $errors[] = 'Name is required';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email is required';
}

if (!filter_var($workshop_id, FILTER_VALIDATE_INT) || $workshop_id < 1) {
    $errors[] = 'A valid workshop is required';
}

if (!filter_var($seats, FILTER_VALIDATE_INT) || $seats < 1 || $seats > 10) {
    $errors[] = 'Seats must be between 1 and 10';
}

// Return validation errors
if (count($errors) > 0) {
    $response['success'] = false;
    $response['errors'] = $errors;

    echo json_encode($response);
    exit;
}

// Convert numeric values to integers
$workshop_id = (int) $workshop_id;
$seats = (int) $seats;

mysqli_begin_transaction($conn);

try {

    // Lock this workshop row.
    // This prevents two registrations for the same workshop
    // from checking capacity at exactly the same time.
    $workshopStmt = mysqli_prepare(
        $conn,
        "SELECT capacity
         FROM workshops
         WHERE id = ?
         FOR UPDATE"
    );

    if (!$workshopStmt) {
        throw new Exception('Failed to prepare workshop query.');
    }

    mysqli_stmt_bind_param(
        $workshopStmt,
        "i",
        $workshop_id
    );

    if (!mysqli_stmt_execute($workshopStmt)) {
        mysqli_stmt_close($workshopStmt);
        throw new Exception('Failed to load workshop.');
    }

    $workshopResult = mysqli_stmt_get_result($workshopStmt);
    $workshop = mysqli_fetch_assoc($workshopResult);

    mysqli_stmt_close($workshopStmt);

    // Check workshop exists
    if (!$workshop) {
        mysqli_rollback($conn);

        $response['success'] = false;
        $response['message'] = 'Workshop not found.';

        echo json_encode($response);
        exit;
    }

    // Calculate already booked seats
    $bookedStmt = mysqli_prepare(
        $conn,
        "SELECT COALESCE(SUM(seats), 0) AS booked_seats
         FROM registrations
         WHERE workshop_id = ?"
    );

    if (!$bookedStmt) {
        throw new Exception('Failed to prepare capacity query.');
    }

    mysqli_stmt_bind_param(
        $bookedStmt,
        "i",
        $workshop_id
    );

    if (!mysqli_stmt_execute($bookedStmt)) {
        mysqli_stmt_close($bookedStmt);
        throw new Exception('Failed to check booked seats.');
    }

    $bookedResult = mysqli_stmt_get_result($bookedStmt);
    $booked = mysqli_fetch_assoc($bookedResult);

    mysqli_stmt_close($bookedStmt);

    $capacity = (int) $workshop['capacity'];
    $bookedSeats = (int) $booked['booked_seats'];
    $remainingSeats = $capacity - $bookedSeats;

    // Reject if requested seats exceed remaining capacity
    if ($seats > $remainingSeats) {
        mysqli_rollback($conn);

        $response['success'] = false;
        $response['message'] = 'Not enough seats available.';

        echo json_encode($response);
        exit;
    }

    // Insert safely using a prepared statement
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO registrations
            (workshop_id, full_name, email, phone, seats)
         VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('Failed to prepare registration.');
    }

    mysqli_stmt_bind_param(
        $stmt,
        "isssi",
        $workshop_id,
        $name,
        $email,
        $phone,
        $seats
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception('Registration insert failed.');
    }

    mysqli_stmt_close($stmt);

    // Save transaction
    mysqli_commit($conn);

    $response['success'] = true;
    $response['message'] = 'Registration successful!';

} catch (Throwable $e) {

    mysqli_rollback($conn);

    $response['success'] = false;
    $response['message'] = 'Database error. Registration failed.';
}

echo json_encode($response);