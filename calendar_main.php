<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
// ?Check if the user is logged in/DECLINE access if not..................................................
if (!isset($_SESSION['useremail'])) {
    header("Location: index.php");
    exit();
}

// TODO Global variables working with.........
$useremail = $_SESSION['useremail']; //email of the current logged in user
$make_request_message_to_user = '';
// echo $_SESSION['upload_profile_pic_status'];
if(!isset($_SESSION['upload_profile_pic_status'])){ 
    // $upload_profile_pic_status = $_SESSION['upload_profile_pic_status']; //Holds any errors for profile pic upload to display
    $upload_profile_pic_status = "";

} else {
    $upload_profile_pic_status = $_SESSION['upload_profile_pic_status'];
}

// Table working with name
$tableName = "calendar_data"; // Replace with your actual table name

// TODO DB info.................
include "data_base.php";

 // Create connection
 $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

 // Check connection
 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
 }

// ?LOGIC TO HANDLE PLACING USER PROFILE PIC...............................................................
// ?LOGIC TO HANDLE PLACING USER PROFILE PIC...............................................................
// ?LOGIC TO HANDLE PLACING USER PROFILE PIC...............................................................
// ?LOGIC TO HANDLE PLACING USER PROFILE PIC...............................................................
// ?LOGIC TO HANDLE PLACING USER PROFILE PIC...............................................................
// ?LOGIC TO HANDLE PLACING USER PROFILE PIC...............................................................
$user_profile_pic = 'images/userDefault.png';
$get_profile_pic_query = "SELECT * FROM users WHERE useremail = '$useremail'";
$result = $conn->query($get_profile_pic_query);
// Checks if the friend request was sent to an existing account
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if($row['profile_pic'] === null){
        $user_profile_pic = 'images/userDefault.png';
    } else {
        $user_profile_pic = $row['profile_pic'];
    }
} else {
    $user_profile_pic = 'images/userDefault.png';
}


    // ?LOGIC TO HANDLE SHOWING FRIENDS AND FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE SHOWING FRIENDS AND FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE SHOWING FRIENDS AND FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE SHOWING FRIENDS AND FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE SHOWING FRIENDS AND FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE SHOWING FRIENDS AND FRIEND REQUESTS...............................................................

    $friend_requests = '';
    $user_friends = '';
    $friend_names_to_display_in_ui = array();
    $friend_profile_pics = array();
    $requestors_names_to_display_in_ui = array();
    $requestors_profile_pics = array();
    // MYSQL Query to perform
    $sql = "SELECT * FROM $tableName WHERE useremail = '$useremail'"; // Query to get the info from DB for the user
    // This line executes the SQL query stored in $sql against the DB using the previously established database connection ($conn). 
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // echo 'not empty';
        // This line fetches the first row of data from the result set $result and stores it in the associative array
        $row = $result->fetch_assoc();
        $user_friends = json_decode($row['friends']); //array of friends fetched from DB / associative array
        $friend_requests = json_decode($row['requests']); //array of requests fetched from DB / associative array
        // Fetch the information of the friends from DB to be able to show their name in the UI.
        get_names_and_pics($user_friends);
        get_names_and_pics($friend_requests);
        // print_r($friend_profile_pics);
    } else {
        // echo 'empty db';
    }

    function get_names_and_pics ($array) {
        global $user_friends;
        global $conn;
        global $friend_names_to_display_in_ui;
        global $friend_profile_pics;
        global $requestors_names_to_display_in_ui;
        global $requestors_profile_pics;
        foreach($array as $friend){
            $query = "SELECT * FROM users WHERE useremail = '$friend'"; // Query to find the friends profile data
            $resultt = $conn->query($query); //Perform query
            if ($resultt->num_rows > 0) { //Check if anything was returned from DB
                $roww = $resultt->fetch_assoc(); //Turn the returned results first row into an associative php array
                $name = $roww['first_name']; // Gets the name of the friend
                $profile_pics = $roww['profile_pic']; // Gets the profile paths of the friend
                if($array === $user_friends){
                    array_push($friend_names_to_display_in_ui, $name); //Pushes the name into the designated array
                    array_push($friend_profile_pics, $profile_pics); //Pushes the image paths into the designated array
                } else{
                    array_push($requestors_names_to_display_in_ui, $name); //Pushes the name into the designated array
                    array_push($requestors_profile_pics, $profile_pics); //Pushes the image paths into the designated array
                }

            } else { //Shows empty if nothing was returned
                // echo 'empty db';
            }
        }
    }

// ?LOGIC TO HANDLE SENDING FRIEND REQUESTS...............................................................
// ?LOGIC TO HANDLE SENDING FRIEND REQUESTS...............................................................
// ?LOGIC TO HANDLE SENDING FRIEND REQUESTS...............................................................
// ?LOGIC TO HANDLE SENDING FRIEND REQUESTS...............................................................
// ?LOGIC TO HANDLE SENDING FRIEND REQUESTS...............................................................
// ?LOGIC TO HANDLE SENDING FRIEND REQUESTS...............................................................
if (isset($_POST['sendRequest']) && $_POST['friendEmail'] !== $useremail) {
    $friend_email_to_save = strtolower($_POST['friendEmail']); // Makes the inputed friend request email lowercase
    // echo $friend_email_to_save; 
    if(!in_array($friend_email_to_save, $user_friends) && !in_array($friend_email_to_save,$friend_requests)){
        // MYSQL Query to perform / gets info of friend email to save
        $sql = "SELECT * FROM $tableName WHERE useremail = '$friend_email_to_save'";
        // Executes the SQL query above
        $result = $conn->query($sql);
        // Checks if the friend request was sent to an existing account
        if ($result->num_rows > 0) {
            // echo 'not empty - user exists';
            // turns json result from DB into a php associative array
            $row = $result->fetch_assoc();
            // decodes the requests json column into a php array and places into variable
            $requests = json_decode($row['requests']);

            // Checks if the current user email already exists in the requests of the friends - if it does, it means a request was previously sent
            if (in_array($useremail, $requests)) {
                $make_request_message_to_user = 'Request already sent';
            } else {
                //pushes the email of the current user (sender) into the requests of the friend (receiver) so they can accept or decline
                array_push($requests, $useremail);
                
                $ready_requests = json_encode($requests); //json encodes the array so it can be sent to DB
                
                // updates the data; putting the current user email in the requests of the friend
                $sql2 = "UPDATE calendar_data
                SET requests = '$ready_requests'
                WHERE useremail = '$friend_email_to_save';";

                // Performs a query on the database
                mysqli_query($conn, $sql2);

                $make_request_message_to_user = 'Friend request sent!';
            }
        } else {
            // echo 'empty - user does not exist';
            $make_request_message_to_user = 'User not found!';
        }
    } else {
        $make_request_message_to_user = 'User already in your friend or request list';
    }
}

     // ?LOGIC TO HANDLE ACCEPTING FRIEND REQUESTS...............................................................
     // ?LOGIC TO HANDLE ACCEPTING FRIEND REQUESTS...............................................................
     // ?LOGIC TO HANDLE ACCEPTING FRIEND REQUESTS...............................................................
     // ?LOGIC TO HANDLE ACCEPTING FRIEND REQUESTS...............................................................
     // ?LOGIC TO HANDLE ACCEPTING FRIEND REQUESTS...............................................................
     // ?LOGIC TO HANDLE ACCEPTING FRIEND REQUESTS...............................................................
     // ?LOGIC TO HANDLE ACCEPTING FRIEND REQUESTS...............................................................
     // ?LOGIC TO HANDLE ACCEPTING FRIEND REQUESTS...............................................................
     
     if(isset($_POST['accept'])){
        $requestor_email = $_POST['requestor_friend_email']; //Email of person making the friend request to the user
        // MYSQL Query to get the data of the user and the person making the friend request
        $sql = "SELECT * FROM $tableName WHERE useremail = '$useremail' OR useremail = '$requestor_email'";
        // This line executes the SQL query and stores result
        $result = $conn->query($sql);
        // echo "<br>" . $result->num_rows;
        // Checks that 2 rows were returned
        if ($result->num_rows > 1) {
            // echo 'not empty' . "<br>";
            // Fetches all the rows returned from DB and stores them
            $data = $result->fetch_all();
            $user_friend_requests = json_decode($data[0][3]); // turns returned JSON php value
            // removes the requestor email from the user's requests array 
            $array_without_requestor = array_diff($user_friend_requests, array($requestor_email));
            //json encodes the user's requests array so it can be sent to DB
            $ready_json_requests = json_encode(array_values($array_without_requestor)); 
        
            //  !Logic that places the new accepted friend into the user's friend data/array
            $user_friend_list = json_decode($data[0][2]); //Decodes user's friends JSON array into PHP array
            array_push($user_friend_list, $requestor_email); //Pushes accepted friend email into user's friends array
            $ready_friends_json_array = json_encode($user_friend_list); //Encodes the user's friend array to JSON to send to DB
            // Query to update user data (friends and requests)
             $sql2 = "UPDATE calendar_data
             SET friends = '$ready_friends_json_array', requests = '$ready_json_requests'
             WHERE useremail = '$useremail';";
            //  Performs query on the database
            if (mysqli_query($conn, $sql2)) {
                // echo "Query 1 executed successfully<br>";
            } else {
                // echo "Error executing Query 1: " . mysqli_error($conn) . "<br>";
            }
         
            // !LOGIC FOR PUTTING THE USER EMAIL INTO THE ACCEPTED FRIEND'S/REQUESTOR DB
            // !LOGIC FOR PUTTING THE USER EMAIL INTO THE ACCEPTED FRIEND'S/REQUESTOR DB
            $requestor_friend_db_space = json_decode($data[1][2]); //Gets ACCEPTED FRIEND'S/REQUESTOR friends array & decodes it
            array_push($requestor_friend_db_space, $useremail); // Pushes the user's email into the ACCEPTED FRIEND'S/REQUESTOR friends array
            $ready_friend_db_space = json_encode($requestor_friend_db_space); //Encodes the ACCEPTED FRIEND'S/REQUESTOR friends array to JSON to send to DB
            
            // Query to update ACCEPTED FRIEND'S/REQUESTOR data
            $sql3 = "UPDATE calendar_data
             SET friends = '$ready_friend_db_space'
             WHERE useremail = '$requestor_email';";

            //  Performs a query on the database
            if (mysqli_query($conn, $sql3)) {
                // echo "Query 2 executed successfully<br>";
            } else {
                // echo "Error executing Query 2: " . mysqli_error($conn) . "<br>";
            }

            // Refreshes page
             header("Refresh:0");
          
        } else {
            // echo 'empty db';
        }
        
    }

    // ?LOGIC TO HANDLE DECLINING FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE DECLINING FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE DECLINING FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE DECLINING FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE DECLINING FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE DECLINING FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE DECLINING FRIEND REQUESTS...............................................................
    // ?LOGIC TO HANDLE DECLINING FRIEND REQUESTS...............................................................
    if(isset($_POST['decline'])){
        $requestor_email = $_POST['requestor_friend_email'];
        // MYSQL Query to perform
        $sql = "SELECT * FROM $tableName WHERE useremail = '$useremail'";
        // This line executes the SQL query stored in $sql against the database using the previously established database connection ($conn). 
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // echo 'not empty' . "<br>";
            // This line fetches the first row of data from the result set $result and stores it in the associative array
            $row = $result->fetch_assoc();
             //json decodes to a php array so it can we worked with
            $user_friend_requests = json_decode($row['requests']);
            // removes the requestor email from the array and creates a new array without it
            $array_without_requestor = array_diff($user_friend_requests, array($requestor_email));
              //json encodes the array so it can be sent to DB
            $ready_json_requests = json_encode(array_values($array_without_requestor));
            // updates the data; putting the current user email in the requests of the friend
            $sql2 = "UPDATE calendar_data
            SET requests = '$ready_json_requests'
            WHERE useremail = '$useremail';";

           //  Performs a query on the database
            mysqli_query($conn, $sql2);
            // refreshes the page
            header("Refresh:0");
         
        }
    }

    // ?LOGIC TO HANDLE DELETING FRIENDS...............................................................
    // ?LOGIC TO HANDLE DELETING FRIENDS...............................................................
    // ?LOGIC TO HANDLE DELETING FRIENDS...............................................................
    // ?LOGIC TO HANDLE DELETING FRIENDS...............................................................
    // ?LOGIC TO HANDLE DELETING FRIENDS...............................................................
    // ?LOGIC TO HANDLE DELETING FRIENDS...............................................................
    // ?LOGIC TO HANDLE DELETING FRIENDS...............................................................
    // ?LOGIC TO HANDLE DELETING FRIENDS...............................................................

    if(isset($_POST['delete'])){
        $friend_to_delete_email = $_POST['requestor_friend_email_delete'];
        echo $friend_to_delete_email;
        // MYSQL Query to perform
        $sql = "SELECT * FROM $tableName WHERE useremail = '$useremail' OR useremail = '$friend_to_delete_email'";

        // This line executes the SQL query stored in $sql against the database using the previously established database connection ($conn). 
        $result = $conn->query($sql);
      
        if ($result->num_rows > 1) {
            // echo 'not empty' . "<br>";
             // This line fetches the first row of data and stores it in PHP associative array
             $data = $result->fetch_all();
            //  print_r($data[0][0]);
            //  $bah = json_decode($data[0][1]) . "<br>";
            //  echo "hehe" . $bah[2] . "<br>";
            //  print_r(json_decode($data[0][1]));
             if($data[0][0] === $useremail){
                // echo json_decode($data[0][0]) . "<br>";
                // echo "working";
                $friends_of_user = json_decode($data[0][2]); // turns the returned requests JSON into a php value
                $deleted_friend_friends = json_decode($data[1][2]);
             } else {
                $friends_of_user = json_decode($data[1][2]); // turns the returned requests JSON into a php value
                $deleted_friend_friends = json_decode($data[0][2]);
             }
            //  !USER DATA.....
            //  $friends_of_user = json_decode($data[0][2]); // turns the returned requests JSON into a php value
            //  echo 'number 1 user';
            //  print_r($friends_of_user);
            //  echo "<br>";
             // removes the friend email to delete from the user's friend array
             $array_without_deleted_friend_user = array_diff($friends_of_user, array($friend_to_delete_email));
             //json encodes the array to JSON so it can be sent to DB
             $ready_json_user_friends = json_encode(array_values($array_without_deleted_friend_user)); 
            //  !FRIEND DATA.....
            //  echo 'number 2 friend';
            //  print_r($deleted_friend_friends);
            //  echo "<br>";
            // removes the user from the friend array belonging to the friend being deleted
            $array_without_deleted_friend_friend = array_diff($deleted_friend_friends, array($useremail));
            $ready_deleted_friend_friends = json_encode(array_values($array_without_deleted_friend_friend)); // Makes the array JSON ready


            // Query to update user data of the user
            $sql2 = "UPDATE calendar_data
            SET friends = '$ready_json_user_friends'
            WHERE useremail = '$useremail';";

           //  Performs a query on the database
           if (mysqli_query($conn, $sql2)) {
            //    echo "Query 1 executed successfully<br>";
           } else {
            //    echo "Error executing Query 1: " . mysqli_error($conn) . "<br>";
           }
 
              // !LOGIC FOR REMOVING THE USER FROM THE PROFILE OF THE FRIENDS BEING DELETED.
              // !LOGIC FOR REMOVING THE USER FROM THE PROFILE OF THE FRIENDS BEING DELETED.
              // !LOGIC FOR REMOVING THE USER FROM THE PROFILE OF THE FRIENDS BEING DELETED.
            
             // Query to update data of friend being deleted
             $sql3 = "UPDATE calendar_data
             SET friends = '$ready_deleted_friend_friends'
             WHERE useremail = '$friend_to_delete_email';";

            //  Performs a query on the database
            if (mysqli_query($conn, $sql3)) {
                // echo "Query 2 executed successfully<br>";
            } else {
                // echo "Error executing Query 2: " . mysqli_error($conn) . "<br>";
            }

            // Refreshes page
             header("Refresh:0");
        } else{
            // echo 'no more than 1 row returned';
        }
    }


     // Closes a previously opened database connection.
     mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <script src="https://kit.fontawesome.com/cd8254595d.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="calendar_main.css" />
        <title>SocialKaledar</title>
    </head>

    <body>
        <!-- APP HEADER AND NAV.............. -->
        <header>
            <nav>
                <div class="left_side padding5">
                    <div class="logo">
                        <img src="images/logo.png" alt=""/>
                    </div>
                    <div class="change_months_dropdown" id="today_element">
                        <h5 class="liteweightfont">Today</h5>
                    </div>
                    <div class="change_month_arrow">
                        <i class="fa-solid fa-chevron-left" id="previous_month"></i>
                        <i class="fa-solid fa-chevron-right" id="next_month"></i>
                    </div>
                    <div class="month_year_display">
                        <h5 class="liteweightfont" id="nav_curr_date_display">May 2022</h5>
                    </div>
                </div>
                <div class="mid padding5">
                    <p class="showing-friend hide">NOW VIEWING FRIEND CALENDAR</p>
                    <p class="showing-friend"><?php echo strtoupper($upload_profile_pic_status); ?></p>
                    <button class="back-to-calendar-btn hide">BACK TO MY CALENDAR</button>
                </div>

                <div class="right_side padding5">
                    <div class="profile-pic-container"><img src="<?php echo $user_profile_pic ?>" alt="" class="profile-pic"></div>
                    <div class="hide" id="dropdown-container">
                        <ul class="dropdown">
                            <li>
                                <form action="upload_profilepic.php" method="post" enctype="multipart/form-data">
                                    Select an image to upload:
                                    <input type="file" name="image" id="image">
                                    <input type="submit" value="Upload" name="submit_pic">
                                </form>
                            </li>
                            <li><a class="logout" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <!-- MAIN APP SPACE........................ -->
        <main>
            <!-- left side of app that has all the friend functionality -->
            <div class="left_sidebar">
                <div class="friends_list">
                    <h4>My Friends</h4>
                    <div class="friendListDiv" id="friendListDiv">
                        <ul class="friends-ul">
                            <?php
                                if($friend_names_to_display_in_ui){
                                    if(count($friend_names_to_display_in_ui) > 0){
                                        for($x = 0; $x < count($friend_names_to_display_in_ui); $x++){ ?>
                                         <li class="friends-li" data-email="<?php echo $user_friends[$x] ?>"><span class="li-span"><img id="friend-profile-trigger" src="<?php echo $friend_profile_pics[$x]; ?>" alt="" class="li-img"></span><p><?php echo $friend_names_to_display_in_ui[$x]; ?></p><span><form action="calendar_main.php" method="POST">
                                                    <input type="hidden" name="requestor_friend_email_delete" value="<?php echo $user_friends[$x]; ?>">
                                                    <input class="delete-btn" type="submit" name="delete" value="Unfriend">
                                                 </form>
                                            </span>
                                         </li>
                                    <?php }
                                    }
                                }
                            ?>
                        </ul>
                    </div>
                </div>

                <div class="friend_requests">
                    <h4>Friend Requests</h4>
                    <div class="friendListDiv" id="friendListDiv2">
                        <?php
                            if($friend_requests){
                                    if(count($friend_requests) > 0){
                                        for($x = 0; $x < count($requestors_names_to_display_in_ui); $x++){ ?>
                                        <div class="indi-request-container">
                                            <div class="name-and-img-container">
                                                <div><img src="<?php echo ($requestors_profile_pics[$x] === NULL) ? "images/userDefault.png" : $requestors_profile_pics[$x]; ?>" alt=""></div>
                                                <p><?php echo $requestors_names_to_display_in_ui[$x]; ?></p>
                                            </div>
                                            <form action="calendar_main.php" method="POST">
                                                <input type="hidden" name="requestor_friend_email" value="<?php echo $friend_requests[$x]; ?>">
                                                <input type="submit" name="accept" value="Accept">
                                                <input type="submit" name="decline" value="Decline">
                                            </form>
                                        </div>
                                    <?php }
                                    }
                                }?>
                    </div>
                </div>
                        

                <div class="make_request_box">
                    <!-- friend finder box.................................................................................... -->
                    <h4>Add Friends</h4>
                    <p id="requestAlreadySent">
                        <?php echo $make_request_message_to_user; ?>
                    </p>
                    <form method="POST" action="calendar_main.php" class="input_and_button">
                        <input id="friend_username_input" type="text" name="friendEmail" placeholder="type user email" />
                        <input id="send_request_btn" name="sendRequest" type="submit" value="Send" />
                    </form>
                </div>
            </div>

            <!-- middle of app that contains all the dates -->
            <div class="calendar_space"></div>
        </main>

        <!-- CALENDAR CONTENT BOX SECTION......................................... -->
        <div class="overlay hide"></div>

        <div class="calendar_content_box hide">
            <div class="images_container"></div>
            <div class="content_box_inner_container">
                <p id="editable_paragraph" contenteditable="true" aria-multiline="true" data-placeholder="Type something..."></p>
            </div>
            <div class="content_box_navigation">
                <!-- <div class="add_pic_button"><i class="fa-solid fa-plus"></i></div> -->
                <label class="add_pic_button" for="uploadpic"><i id="image_icon" class="fa-solid fa-image"></i></label>
                <input type="file" id="uploadpic" name="photo" />
                <!-- accept="image/png, image/jpeg, pdf"> -->

                <p id="day_editable_content_box">l</p>
                <i id="close_box" class="fa-solid fa-square-xmark"></i>
            </div>
        </div>

        <footer>Made by Daniel Rasch</footer>

        <script type="module" src="calendar_main.js"></script>
    </body>
</html>
