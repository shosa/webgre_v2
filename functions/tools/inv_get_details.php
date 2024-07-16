<?php
// Replace these values with your actual database credentials
require_once '../../config/config.php';
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the article code from the query string
$articleCode = $_GET['art'];

// Prepare and execute the SQL query to get details based on the article code
$sql = "SELECT art, des FROM inv_anagrafiche WHERE art = '$articleCode'";
$result = $conn->query($sql);

// Check if the query was successful
if ($result) {
    // Fetch the result as an associative array
    $details = $result->fetch_assoc();

    // Output the details as JSON
    header('Content-Type: application/json');
    echo json_encode($details);
} else {
    // Handle the error if the query fails
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the database connection
$conn->close();
?>