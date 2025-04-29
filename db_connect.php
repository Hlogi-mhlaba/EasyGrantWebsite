<?php
// Autoloading dependencies using Composer
require_once __DIR__ . '/vendor/autoload.php';

// Database connection details
$servername = "localhost";  // The database server 
$username = "root";         // Database username (default for XAMPP)
$password = "";             // Database password (default for XAMPP is empty)
$database = "easygrant";    

// Create a new connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check if the connection was successful
if ($conn->connect_error) {
    // If the connection failed, stop the execution and show an error message
    die("Connection failed: " . $conn->connect_error);
}
?>
