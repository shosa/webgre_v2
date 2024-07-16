<?php
require_once '../../config/config.php';
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the search term from the query string
$searchTerm = $_GET['q'];

// Prepare and execute the SQL query
$sql = "SELECT DISTINCT art, des FROM inv_anagrafiche WHERE art LIKE '%$searchTerm%' OR des LIKE '%$searchTerm%'";
$result = $conn->query($sql);

// Fetch the results into an array
$suggestions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'art' => $row['art'],
            'des' => $row['des'],
        ];
    }
}

// Output the suggestions as JSON
header('Content-Type: application/json');
echo json_encode($suggestions);

// Close the database connection
$conn->close();
?>