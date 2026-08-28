<?php
// Database connection.
// Update the credentials below to match your local MySQL setup.

$host = 'localhost';
$db   = 'workshop_app';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
