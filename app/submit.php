<?php
require 'db.php';

$response = array();

// Collect the submitted values
$name        = $_POST['name'];
$email       = $_POST['email'];
$phone       = $_POST['phone'];
$workshop_id = $_POST['workshop_id'];
$seats       = $_POST['seats'];

// Validate the input
$errors = array();

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email is required';
}

if ($seats < 1 || $seats > 10) {
    $errors[] = 'Seats must be between 1 and 10';
}

if (count($errors) > 0) {
    $response['success'] = false;
    $response['errors'] = $errors;

    echo json_encode($response);
    exit;
}

// Save the registration using a prepared statement
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO registrations (workshop_id, full_name, email, phone, seats)
     VALUES (?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "isssi",
    $workshop_id,
    $name,
    $email,
    $phone,
    $seats
);

mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$response['success'] = true;
$response['message'] = 'Registration successful!';

echo json_encode($response);