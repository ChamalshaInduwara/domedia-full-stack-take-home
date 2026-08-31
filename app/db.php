<?php

// Database connection.
// Update the credentials below to match your local MySQL setup.

$host = 'localhost';
$db   = 'workshop_app';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    header('Content-Type: application/json');
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);

    exit;
}