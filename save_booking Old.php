<?php
require_once __DIR__ . '/vendor/autoload.php';

use Mhlab\Easygrant\BookingHandler;

$handler = new BookingHandler();
$handler->saveBooking($_POST); // Assuming you create this method

echo "Booking saved!";
333333656357673635663546/
// Database connection
$host = 'localhost';
$username = 'root'; // default XAMPP MySQL username
$password = '';     // default XAMPP MySQL password is empty
$database = 'easygrant'; // your database name

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get and sanitize POST data
$fullname = $conn->real_escape_string($_POST['fullname']);
$idnumber = $conn->real_escape_string($_POST['idnumber']);
$date = $conn->real_escape_string($_POST['date']);
$time = $conn->real_escape_string($_POST['time']);
$cellphone = $conn->real_escape_string($_POST['cellphone']);
$ticketnumber = $conn->real_escape_string($_POST['ticketnumber']);

//Check for duplicate booking
$checkQuery = "SELECT * FROM bookings WHERE idnumber = '$idnumber' AND date = '$date' AND time = '$time'";
$result = $conn->query($checkQuery);

if ($result->num_rows > 0) {
    echo "duplicate"; // duplicate booking found
} else {
    // Insert into database
    $insertQuery = "INSERT INTO bookings (Fullname, idnumber, date, time, cellphone, ticketnumber, created_at) 
                    VALUES ('$fullname', '$idnumber', '$date', '$time',  '$cellphone', '$ticketnumber', NOW())";

    if ($conn->query($insertQuery) === TRUE) {
        echo "success";
    } else {
        echo "error";
    }
}

// Close connection
$conn->close();
?>
