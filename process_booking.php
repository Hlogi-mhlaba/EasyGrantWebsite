<?php
// Include the autoload file for any external libraries (e.g., Composer packages)
require_once __DIR__ . '/vendor/autoload.php';

// Connect to the database
include 'db_connect.php';

// Check if form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve form inputs (user's booking information)
    $fullname = $_POST['fullname'];  // Full name of the user
    $idnumber = $_POST['idnumber'];  // ID number
    $date = $_POST['date'];          // Selected date for the booking
    $time = $_POST['time'];          // Selected time for the booking
    $cellphone = $_POST['cellphone'];
    //$ticketnumber = $_POST['ticketnumber'];

    // Default value
$newTicketNumber = 1;

$ticketQuery = "SELECT MAX(CAST(SUBSTRING(ticketnumber, 5) AS UNSIGNED)) AS last_ticket FROM bookings WHERE ticketnumber LIKE 'TCK-%'";
$ticketResult = $conn->query($ticketQuery);

    if ($ticketResult && $row = $ticketResult->fetch_assoc()) {
        if (!empty($row['last_ticket'])) {
            $newTicketNumber = (int)$row['last_ticket'] + 1;
        }
    }
    
 // Generate the ticket number string like "TCK-1", "TCK-2", etc.
$ticketNumber = 'TCK-' . $newTicketNumber;

    // Prepare the SQL statement for inserting the booking data into the database
    $stmt = $conn->prepare("INSERT INTO bookings (fullname, idnumber, date, time, cellphone, ticketnumber) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        // Bind parameters: "ssss" means all 6 are strings
        $stmt->bind_param("ssssss", $fullname, $idnumber, $date, $time, $cellphone, $ticketNumber);

        // Execute the statement
        if ($stmt->execute()) {
            echo "   <div style='text-align: center; margin-top: 100px;'>
            <h2>Booking successful!</h2>
            <p>Your ticket number is <strong>$ticketNumber</strong></p>
            <a href='index.html'>
                <button>Return to Home</button>
            </a> 
                </div> ";
        } else {
            echo "Error executing statement: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Close database connection
    $conn->close();
}
?>
