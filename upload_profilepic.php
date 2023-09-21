<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

$useremail = $_SESSION['useremail'];
$targetDirectory = "uploads/"; // Directory to store uploaded files


function add_image()
{
    global $useremail, $conn;
    // File upload handling
    if (isset($_POST["submit_pic"])) {
        global $targetDirectory;
        $targetFile = $targetDirectory . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the file is an image
        if(!$_FILES["image"]["tmp_name"]) return;
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            // echo "File is not an image.";
            $_SESSION['upload_profile_pic_status'] = "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size (you can change this to your preferred size)
        if ($_FILES["image"]["size"] > 400000) {
            // echo "Sorry, your file is too large.";
            $_SESSION['upload_profile_pic_status'] = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats (you can customize this)
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            // echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $_SESSION['upload_profile_pic_status'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Checks if all the validations passed
        if ($uploadOk == 0) {
            // echo "Sorry, your file was not uploaded.";
        } else {
            $sql = "SELECT profile_pic FROM users WHERE useremail = '$useremail'"; //Query
            $result = $conn->query($sql); //Executes Query
            $row = $result->fetch_assoc(); //Fetches first row and turns to php array
            $filenameToDelete = $row['profile_pic']; //Gets the needed value from array and puts into variable

            // Checks if anything was returned to proceed with adequate code
            if ($row['profile_pic'] === null) {
                // Move the file to the target directory
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    // Insert the file details into the database
                    $sql = "UPDATE users SET profile_pic = '$targetFile' WHERE useremail = '$useremail'";
                    if ($conn->query($sql) === true) {
                        // $_SESSION['upload_profile_pic_status'] = "The file " . htmlspecialchars(basename($_FILES["image"]["name"])) . " has been uploaded and saved to the database.";
                        $_SESSION['upload_profile_pic_status'] = "";
                    } else {
                        // echo "Error: " . $sql . "<br>" . $conn->error;
                        $_SESSION['upload_profile_pic_status'] = "Sorry, there was an error uploading your file. Try again later.";
                    }
                } else {
                    // echo "Sorry, there was an error uploading your file.";
                    $_SESSION['upload_profile_pic_status'] = "Sorry, there was an error uploading your file. Try again later.";
                }
            } else {
                // Check if the file exists before attempting to delete it.
                if (file_exists($filenameToDelete)) {
                    // Attempt to delete the file.
                    if (unlink($filenameToDelete)) {
                        // echo "File '$filenameToDelete' has been deleted successfully.";
                        // Move the file to the target directory
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                            // Insert the file details into the database
                            $sql = "UPDATE users SET profile_pic = '$targetFile' WHERE useremail = '$useremail'";
                            if ($conn->query($sql) === true) {
                                // echo "The file " . htmlspecialchars(basename($_FILES["image"]["name"])) . " has been uploaded and saved to the database.";
                                // $_SESSION['upload_profile_pic_status'] = "The file " . htmlspecialchars(basename($_FILES["image"]["name"])) . " has been uploaded and saved to the database.";
                                $_SESSION['upload_profile_pic_status'] = "";
                            } else {
                                // echo "Error: " . $sql . "<br>" . $conn->error;
                                $_SESSION['upload_profile_pic_status'] = "Sorry, there was an error uploading your file. Try again later.";
                            }
                        } else {
                            // echo "Sorry, there was an error uploading your file.";
                            $_SESSION['upload_profile_pic_status'] = "Sorry, there was an error uploading your file. Try again later.";
                        }
                    } else {
                        // echo "Failed to delete '$filenameToDelete'.";
                    }
                } else {
                    // echo "File '$filenameToDelete' does not exist in the directory.";
                }
            }
        }
    }
}

// DB info....
include "data_base.php";

// Create connection
$conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

add_image();
$conn->close();
header("Location: calendar_main.php");
?>
