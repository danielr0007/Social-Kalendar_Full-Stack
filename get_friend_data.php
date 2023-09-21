<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
$useremail = $_SESSION['useremail'];

// Checks that the $_POST superglobal is not null or empty.........
if (isset($_POST)) {
    $data = file_get_contents("php://input");
    // decodes the JSON into a useable php array
    $user = json_decode($data, true);
    // do whatever you want with the $user array.
    // gets each needed user value from the array and stores into variable
    $friend_email = $user['friendEmail'];
}

// DB info
include "data_base.php";

// Create connection
$conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tableName = "calendar_data"; // Replace with your actual table name

// MYSQL Query to perform
$sql = "SELECT * FROM $tableName WHERE useremail = '$friend_email'";

// This line executes the SQL query stored in $sql against the database using the previously established database connection ($conn). 
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // echo 'not empty';
    // This line fetches the first row of data from the result set $result and stores it in the associative array
    $row = $result->fetch_assoc();
    // $userName = $row['useremail'];
    // $monthsData = $row['json_data'];
    // $friends = $row['friends'];

    // Finally, this line encodes the value in the $monthsData variable as JSON
    // echo json_encode($monthsData);
    echo json_encode($row);
} else {
    $arr = ['response' => 'empty'];
    $res = null;
    echo json_encode($arr);
    echo $res;
}


?>