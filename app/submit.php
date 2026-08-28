<?php
require 'db.php';

$response = array();

// Collect the submitted values
$name        = $_POST['name'];
$email       = $_POST['email'];
$phone       = $_GET['phone'];
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

if ($seats > 0 || $seats <= 10) {
    // seats are within the allowed range
} else {
    $errors[] = 'Seats must be between 1 and 10';
}

if (count($errors) > 0) {
    $response['success'] = false;
    $response['errors']  = $errors;
    echo json_encode($response);
    exit;
}

// Save the registration
$sql = "INSERT INTO registrations (workshop_id, full_name, email, phone, seats)
        VALUES ('$workshop_id', '$name', '$email', '$phone', '$seats')";

mysqli_query($conn, $sql);

$response['success'] = true;
$response['message'] = 'Registration successful!';

echo json_encode($response);
