<?php
session_start();
require_once('DBconnect.php');

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Get user ID
$email = $_SESSION['email'];
$user_query = "SELECT UserID FROM users WHERE email = '$email'";
$user_result = mysqli_query($conn, $user_query);
$user_id = mysqli_fetch_assoc($user_result)['UserID'];

// Check if form is submitted
if(isset($_POST['meeting_id']) && isset($_POST['attendance'])) {
    
    // Get form data
    $meeting_id = $_POST['meeting_id'];
    $attendance = $_POST['attendance'];
    
    // Check if user has already marked attendance
    $check_query = "SELECT * FROM meeting_attendees WHERE meeting_id = $meeting_id AND user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        // Update existing attendance
        $update_query = "UPDATE meeting_attendees SET attendance = '$attendance' WHERE meeting_id = $meeting_id AND user_id = $user_id";
        mysqli_query($conn, $update_query);
    } else {
        // Insert new attendance
        $insert_query = "INSERT INTO meeting_attendees (meeting_id, user_id, attendance) VALUES ($meeting_id, $user_id, '$attendance')";
        mysqli_query($conn, $insert_query);
    }
    
    // Update meeting vote count in offline_meetings table
    // This should be a trigger or stored procedure in a production environment
    $update_vote = "UPDATE offline_meetings SET Meeting_Vote = (
                    SELECT COUNT(*) FROM meeting_attendees 
                    WHERE meeting_id = $meeting_id AND attendance = 'going'
                    ) WHERE Meeting_ID = $meeting_id";
    mysqli_query($conn, $update_vote);
    
    // Redirect back to meetings page
    header("Location: meetings.php");
} else {
    echo "Missing required fields!";
}
?>