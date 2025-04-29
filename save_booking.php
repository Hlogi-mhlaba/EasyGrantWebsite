<?php

// ✅ DEBUG: Print all POST data to confirm fields are coming through
echo "<pre>";
print_r($_POST);
echo "</pre>";
exit; // Stop the script here to see output

// Include autoload file if needed
require_once __DIR__ . '/vendor/autoload.php';

// Optional: use BookingHandler class
use Mhlab\Easygrant\BookingHandler;
$handler = new BookingHandler();

// Database connection details
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'easygrant';

// Create DB connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//file_put_contents("debug_log.txt", "Cellphone: " . ($_POST['cellphone'] ?? 'NOT SET') . PHP_EOL, FILE_APPEND);

// Validate required POST fields (excluding ticketnumber)
$required = ['fullname', 'idnumber', 'date', 'time', 'cellphone'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo "missing_$field";
        exit;
    }
}

// Sanitize input
$fullname   = $conn->real_escape_string($_POST['fullname']);
$idnumber   = $conn->real_escape_string($_POST['idnumber']);
$date       = $conn->real_escape_string($_POST['date']);
$time       = $conn->real_escape_string($_POST['time']);
$cellphone  = $conn->real_escape_string($_POST['cellphone']);

// Check if booking already exists (same ID, date & time)
$checkQuery = "SELECT 1 FROM bookings WHERE idnumber = '$idnumber' AND date = '$date' AND time = '$time' LIMIT 1";
$result = $conn->query($checkQuery);

if ($result->num_rows > 0) {
    echo "duplicate";
    $conn->close();
    exit;
}

// Generate a new ticket number
$ticketQuery = "SELECT MAX(CAST(SUBSTRING(ticketnumber, 5) AS UNSIGNED)) AS last_ticket FROM bookings WHERE ticketnumber LIKE 'TCK-%'";
$ticketResult = $conn->query($ticketQuery);

$newTicketNumber = 1;
if ($ticketResult && $row = $ticketResult->fetch_assoc()) {
    if (!empty($row['last_ticket'])) {
        $newTicketNumber = (int)$row['last_ticket'] + 1;
    }
}

$ticketnumber = "TCK-" . $newTicketNumber;

// Ensure the ticket number is unique (loop to be extra safe)
$checkTicketQuery = "SELECT 1 FROM bookings WHERE ticketnumber = '$ticketnumber' LIMIT 1";
while ($conn->query($checkTicketQuery)->num_rows > 0) {
    $newTicketNumber++;
    $ticketnumber = "TCK-" . $newTicketNumber;
    $checkTicketQuery = "SELECT 1 FROM bookings WHERE ticketnumber = '$ticketnumber' LIMIT 1";
}

echo "Saving: $fullname, $idnumber, $date, $time, $cellphone, $ticketnumber";

// Insert new booking
$stmt = $conn->prepare("INSERT INTO bookings (fullname, idnumber, date, time, cellphone, ticketnumber, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())");

$stmt->bind_param("ssssss", $fullname, $idnumber, $date, $time, $cellphone, $ticketnumber);

if ($stmt->execute()) {
    echo "success|" . $ticketnumber;
} else {
    echo "error|" . $stmt->error;
}

$stmt->close();
$conn->close();
?>
