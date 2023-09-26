<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// TODO html to inform user
$success = " ";
$already_registered = " ";
$login_failed = " ";

 // TODO DB info.................
 include "data_base.php";

 // Create connection
 $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

 // Check connection
 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
 }

// !LOGIN LOGIC.....................................................
// !.........................................................................
// !.........................................................................
// !.........................................................................
if (isset($_POST['login'])) {
  session_start();
  $email_input = filter_input(INPUT_POST, "email", FILTER_SANITIZE_SPECIAL_CHARS); //Sanitizes input for security
  $password_input = filter_input(INPUT_POST, "thepassword", FILTER_SANITIZE_SPECIAL_CHARS); //Sanitizes input for security

  // Query the database for the user
  $stmt = $conn->prepare("SELECT * FROM users WHERE useremail = ?"); //Separates the query from the input for extra security
  $stmt->bind_param('s', $email_input); //Binds the input from user to SQL query
  $stmt->execute(); //Executes Query
  $result = $stmt->get_result(); //Gets Query result

  if ($result->num_rows == 1) {
      $user = $result->fetch_assoc();
      if (password_verify($password_input, $user['password'])) { //Checks inputted password with hash in DB
          // User authenticated; set session variable and redirect to access page
          $_SESSION['useremail'] = $email_input;
          header("Location: calendar_main.php");
          exit;
      }
  }

  // Invalid credentials; redirect back to login page
  $login_failed = "Login failed";
  header("Location: index.php?error=1");
  exit; //Terminates Script
}


// !CREATE ACCOUNT LOGIC.....................................................
// !.........................................................................
// !.........................................................................
// !.........................................................................
if (isset($_POST['create_account'])) {
    $firstname = strtolower(filter_input(INPUT_POST, "first_name", FILTER_SANITIZE_SPECIAL_CHARS));
    $lastname = strtolower(filter_input(INPUT_POST, "last_name", FILTER_SANITIZE_SPECIAL_CHARS));
    $new_email = filter_input(INPUT_POST, "new_email", FILTER_SANITIZE_SPECIAL_CHARS);
    $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT); // Hash the password

    // Use a prepared statement to check if the email exists
    $stmt = $conn->prepare("SELECT useremail FROM users WHERE useremail = ?");
    $stmt->bind_param("s", $new_email);
    $stmt->execute();
    $stmt->store_result();


    if ($stmt->num_rows > 0) {
      $already_registered = "Email address is already in use";
  } else {
      // Use a prepared statement to insert user info into the DB
      $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, useremail, password) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $firstname, $lastname, $new_email, $new_password);

      if ($stmt->execute()) {
          $success = "You've been registered!";
          $_POST = array(); // Clear the form data
      } else {
          // Handle the database error
          // echo "Error executing query: " . $stmt->error;
      }
    }
  }

$conn->close(); //Closes database connection
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