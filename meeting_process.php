<?php
session_start();
require_once('DBconnect.php');

// Check if user is logged in and is admin
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Get user info
$email = $_SESSION['email'];
$user_query = "SELECT * FROM users WHERE email = '$email'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$user_id = $user['UserID'];

if($user['class'] != 'admin') {
    header("Location: meetings.php");
    exit;
}

// Check if form is submitted
if(isset($_POST['meeting_title']) && isset($_POST['meeting_date']) && isset($_POST['meeting_time']) && 
   isset($_POST['meeting_location']) && isset($_POST['meeting_description'])) {
    
    // Get form data
    $title = $_POST['meeting_title'];
    $date = $_POST['meeting_date'];
    $time = $_POST['meeting_time'];
    $location = $_POST['meeting_location'];
    $description = $_POST['meeting_description'];
    
    // Insert meeting data (non-recurring by default)
    $insert_meeting = "INSERT INTO offline_meetings 
                      (meeting_title, meeting_date, meeting_time, meeting_location, 
                      meeting_description, Meeting_Vote, arranged_by, is_recurring, recurrence_pattern) 
                      VALUES 
                      ('$title', '$date', '$time', '$location', 
                      '$description', 0, $user_id, 0, NULL)";
    
    if(mysqli_query($conn, $insert_meeting)) {
        // Success, redirect back to meetings page
        header("Location: meetings.php");
    } else {
        echo "Error creating meeting: " . mysqli_error($conn);
    }
} else {
    echo "Missing required fields!";
}
?>