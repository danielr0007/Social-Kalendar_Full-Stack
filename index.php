<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// !LOGIN LOGIC.....................................................
// !.........................................................................
// !.........................................................................
// !.........................................................................
if (isset($_POST['login'])) {
    session_start();

    // echo $_POST['email'];
    // echo $_POST['thepassword'];
    $email_input = $_POST['email'];
    $password_input = $_POST['thepassword'];

    // TODO DB info.................
    include "data_base.php";

    // Create connection
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query the database for the user
    $sql = "SELECT * FROM users WHERE useremail = '$email_input' AND password = '$password_input'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
      // User authenticated; set session variable and redirect to access page
      $_SESSION['useremail'] = $email_input;
      $_POST = array();
      header("Location: calendar_main.php");
    } else {
      // Invalid credentials; redirect back to login page
      $login_failed = "Login failed";
      header("Location: index.php?error=1");
    }

$conn->close();
}

// TODO html to inform user
$success = " ";
$already_registered = " ";
$login_failed = " ";
// !CREATE ACCOUNT LOGIC.....................................................
// !.........................................................................
// !.........................................................................
// !.........................................................................
if (isset($_POST['create_account'])) {
    $firstname = $_POST['first_name'];
    $lastname = $_POST['last_name'];
    $new_email = $_POST['new_email'];
    $new_password = $_POST['new_password'];

    // Database info
    include "data_base.php";
    
    // Socialkalendar2020!

    // Create connection
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $tableName = "users"; // Replace with your actual table name

    $sql = "SELECT useremail FROM $tableName WHERE useremail = '$new_email'"; //query to check if email is already in DB

    // executes the query above
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // echo "User already exists";
        $already_registered = "Email address is already in use";

        // Closes a previously opened database connection.
        mysqli_close($conn);
    } else {
        // echo "Table is empty.";

        // query that inserts user info into DB
        $sql = "INSERT INTO users (first_name, last_name, useremail, password) VALUES ('$firstname', '$lastname', '$new_email', '$new_password')";

        // Performs a query on the database
        mysqli_query($conn, $sql);

        // Closes a previously opened database connection.
        mysqli_close($conn);
        $success = "You've been registered!";
        $_POST = array();
    }
} else {
    /* If there is a problem with the connection this code runs */
    // echo "Error executing query: " . $conn->error;
}

?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>login page</title>

    <link rel="stylesheet" href="login.css" />
  </head>
  <body>
    <div class="mainContainer">
      <div class="mainContainerContent">
        <div class="ContainerLeft">
          <div class="logoContainer">
            <img src="images/logo.png" />
          </div>
        </div>
        <div class="ContainerRight">
          <div class="loginForm">
            <form class="loginFormTop" action="index.php" method="POST">
              <input type="email" name="email" placeholder="Email" id="loginEmail" />
              <input
                type="password"
                name="thepassword"
                placeholder="Password"
                id="loginPassword"
              />
              <input type="submit" name="login" value="Login" id="login-button"></>
              <!-- <p>Forgot password?</p> -->
              <p id="loginFailedAlert"><?php echo $login_failed; ?></p>
              <p id="alreadyRegisteredAlert"><?php echo $already_registered; ?></p>
              <p id="userRegisteredAlert"><?php echo $success; ?></p>
            </form>
            <div class="loginFormBottom">
              <button id="createAccountButton">Create new account</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="footer">
      <div>
        <p>
          <a href="https://github.com/danielr0007">Daniel</a> 2023
        </p>
      </div>
    </div>

    <!-- ! Sign up form........................................................................... -->
    <!-- ......................................................................................... -->
    <!-- ......................................................................................... -->
    <!-- ......................................................................................... -->
    <!-- ......................................................................................... -->
    <!-- ......................................................................................... -->
    <div class="signUpForm hide">
        <div class="signUpFormTitle">
            <span id="signUpFormCloseButton" class="material-symbols-outlined">
                X
            </span>
            <h1>Sign Up</h1>
            <p>It's quick and easy.</p>
        </div>
        <form action="index.php" method="POST" class="signUpFormBody">
            <div class="signUpFormBodyTop">
                <input type="text" name="first_name" placeholder="First Name" id="firstName" />
                <input type="text" name="last_name" placeholder="Last Name" id="lastName" />
            </div>

            <input type="email" name="new_email" placeholder="Email" id="email" />
            <input type="password" name="new_password" placeholder="New password" id="password" />
            <input type="submit" name="create_account" id="signUpButton">
        </form>
    </div>

    <div class="overlay hide"></div>

    <script>
        let signUpForm = document.querySelector(".signUpForm");
        let overlay = document.querySelector(".overlay");
        let firstNameField = document.getElementById("firstName");
        let lastNameField = document.getElementById("lastName");

        document.getElementById("createAccountButton").addEventListener("click", function () {
            signUpForm.classList.remove("hide");
            overlay.classList.remove("hide");
        });

        document.getElementById("signUpFormCloseButton").addEventListener("click", function () {
            firstNameField.value = "";
            lastNameField.value = "";

            signUpForm.classList.add("hide");
            overlay.classList.add("hide");
        });

        
    </script>
  </body>
</html>