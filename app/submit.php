<?php
require 'db.php';

$response = array();

// Collect the submitted values
$name        = $_POST['name'] ?? '';
$email       = $_POST['email'] ?? '';
$phone       = $_POST['phone'] ?? '';
$workshop_id = $_POST['workshop_id'] ?? '';
$seats       = $_POST['seats'] ?? '';

// Validate the input
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

if (count($errors) > 0) {
    $response['success'] = false;
    $response['errors'] = $errors;

    echo json_encode($response);
    exit;
}

// Convert validated numeric values to integers
$workshop_id = (int) $workshop_id;
$seats = (int) $seats;

// Check workshop capacity
$capacityStmt = mysqli_prepare(
    $conn,
    "SELECT w.capacity, COALESCE(SUM(r.seats), 0) AS booked_seats
     FROM workshops w
     LEFT JOIN registrations r ON w.id = r.workshop_id
     WHERE w.id = ?
     GROUP BY w.id, w.capacity"
);

if (!$capacityStmt) {
    $response['success'] = false;
    $response['message'] = 'Database error. Please try again.';

    echo json_encode($response);
    exit;
}

mysqli_stmt_bind_param(
    $capacityStmt,
    "i",
    $workshop_id
);

if (!mysqli_stmt_execute($capacityStmt)) {
    mysqli_stmt_close($capacityStmt);

    $response['success'] = false;
    $response['message'] = 'Database error. Please try again.';

    echo json_encode($response);
    exit;
}

$result = mysqli_stmt_get_result($capacityStmt);
$workshop = mysqli_fetch_assoc($result);

mysqli_stmt_close($capacityStmt);

// Check whether workshop exists
if (!$workshop) {
    $response['success'] = false;
    $response['message'] = 'Workshop not found.';

    echo json_encode($response);
    exit;
}

// Calculate remaining seats
$capacity = (int) $workshop['capacity'];
$bookedSeats = (int) $workshop['booked_seats'];
$remainingSeats = $capacity - $bookedSeats;

// Reject registration if requested seats exceed remaining capacity
if ($seats > $remainingSeats) {
    $response['success'] = false;
    $response['message'] = 'Not enough seats available.';

    echo json_encode($response);
    exit;
}

// Save the registration using a prepared statement
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO registrations
        (workshop_id, full_name, email, phone, seats)
     VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    $response['success'] = false;
    $response['message'] = 'Database error. Registration failed.';

    echo json_encode($response);
    exit;
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

if (mysqli_stmt_execute($stmt)) {
    $response['success'] = true;
    $response['message'] = 'Registration successful!';
} else {
    $response['success'] = false;
    $response['message'] = 'Database error. Registration failed.';
}

mysqli_stmt_close($stmt);

echo json_encode($response);