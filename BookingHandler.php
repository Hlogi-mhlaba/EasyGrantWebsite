<?php

namespace Mhlab\Easygrant;

class BookingHandler
{
    private $conn;

    public function __construct()
    {
        // Database connection setup
        $host = 'localhost';
        $username = 'root';
        $password = '';
        $database = 'easygrant';

        $this->conn = new \mysqli($host, $username, $password, $database);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function saveBooking($postData)
    {
        // Validate all required fields
        $required = ['fullname', 'idnumber', 'date', 'time', 'cellphone', 'ticketnumber'];
        foreach ($required as $field) {
            if (empty($postData[$field])) {
                echo "missing_$field";
                return;
            }
        }

        // Sanitize input
        $fullname     = $this->conn->real_escape_string($postData['fullname']);
        $idnumber     = $this->conn->real_escape_string($postData['idnumber']);
        $date         = $this->conn->real_escape_string($postData['date']);
        $time         = $this->conn->real_escape_string($postData['time']);
        $cellphone    = $this->conn->real_escape_string($postData['cellphone']);
        $ticketnumber = $this->conn->real_escape_string($postData['ticketnumber']);

        // Check for duplicate booking
        $checkQuery = "SELECT * FROM bookings WHERE idnumber = '$idnumber' AND date = '$date' AND time = '$time'";
        $result = $this->conn->query($checkQuery);

        if ($result->num_rows > 0) {
            echo "duplicate";
        } else {
            // Insert new booking using prepared statement
            $stmt = $this->conn->prepare("INSERT INTO bookings (Fullname, idnumber, date, time, cellphone, ticketnumber, created_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, NOW())");

            $stmt->bind_param("ssssss", $fullname, $idnumber, $date, $time, $cellphone, $ticketnumber);

            if ($stmt->execute()) {
                echo "success";
            } else {
                echo "error: " . $stmt->error;
            }

            $stmt->close();
        }

        $this->conn->close();
    }
}
