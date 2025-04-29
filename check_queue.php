<?php
// check_queue.php
include 'db_connect.php'; // Make sure this file sets up $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idnumber = $_POST['queueID'];

    // Get the booking info for this ID
    $query = "SELECT * FROM bookings WHERE idnumber = ? ORDER BY date, time LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $idnumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($booking = $result->fetch_assoc()) {
        // Count how many people are ahead based on date & time
        $positionQuery = "SELECT COUNT(*) AS position FROM bookings WHERE (date < ? OR (date = ? AND time < ?))";
        $stmt2 = $conn->prepare($positionQuery);
        $stmt2->bind_param("sss", $booking['date'], $booking['date'], $booking['time']);
        $stmt2->execute();
        $stmt2->bind_result($position);
        $stmt2->fetch();

        $queuePosition = $position + 1; // Include the user's own position
        $stmt2->close();

        // Display info
        echo "<div style='padding: 40px; text-align: center;'>";
        echo "<h2>Your Queue Position: $queuePosition</h2>";
        echo "<table border='1' cellpadding='10' style='margin: 0 auto;'>";
        echo "<tr><th>Full Name</th><th>ID Number</th><th>Date</th><th>Time</th><th>Cellphone</th><th>Ticket Number</th></tr>";
        echo "<tr>";
        echo "<td>" . $booking['fullname'] . "</td>";
        echo "<td>" . $booking['idnumber'] . "</td>";
        echo "<td>" . $booking['date'] . "</td>";
        echo "<td>" . $booking['time'] . "</td>";
        echo "<td>" . $booking['cellphone'] . "</td>";
        echo "<td>" . $booking['ticketnumber'] . "</td>";
        echo "</tr></table><br>";

        echo "<a href='index.html'><button>Return to Home</button></a>";
        echo "</div>";
    } else {
        echo "<p style='text-align: center;'>No booking found for ID number: <strong>$idnumber</strong></p>";
        echo "<div style='text-align: center;'><a href='index.php'><button>Try Again</button></a></div>";
    }
}
?>
