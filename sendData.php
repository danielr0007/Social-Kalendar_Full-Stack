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
    // $name = $user['username'];
    $friends = json_encode($user['friends']);
    $requests = json_encode($user['requests']);
    // encode json to make it database ready and store in variable
    $json_data = json_encode($user['jsonData']);
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

$sql = "SELECT useremail FROM $tableName WHERE useremail = '$useremail'"; //query to check if email is already in DB

 // executes the query above
$result = $conn->query($sql);
// echo $result->num_rows;

if ($result->num_rows > 0) {
    echo "Table is not empty. Updating existing user data";

    // updates the data to the new
    $sql2 = "UPDATE calendar_data
        SET useremail = '$useremail', json_data = '$json_data'
        WHERE useremail = '$useremail';
        ";

    // Performs a query on the database
    mysqli_query($conn, $sql2);

    // Closes a previously opened database connection.
    mysqli_close($conn);

} else {
    echo "Table is empty. Making new user data table";

    $sql2 = "INSERT INTO calendar_data (useremail, json_data, friends, requests) VALUES ('$useremail', '$json_data', '$friends', '$requests')";
    
    // Performs a query on the database
    mysqli_query($conn, $sql2);
    
    // Closes a previously opened database connection.
    mysqli_close($conn);
}
?>
