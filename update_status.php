<?php
session_start();
require_once('DBconnect.php');

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Check user is admin
$email = $_SESSION['email'];
$user_query = "SELECT * FROM users WHERE email = '$email'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$user_id = $user['UserID'];
$user_area = $user['selected_area'];

if($user['class'] != 'admin') {
    header("Location: feed.php");
    exit;
}

// Check if form is submitted
if(isset($_POST['issue_id']) && isset($_POST['new_status'])) {
    
    // Get form data
    $issue_id = $_POST['issue_id'];
    $new_status = $_POST['new_status'];
    
    // Check if issue belongs to admin's area
    $issue_query = "SELECT location FROM issues WHERE issue_id = $issue_id";
    $issue_result = mysqli_query($conn, $issue_query);
    $issue_location = mysqli_fetch_assoc($issue_result)['location'];
    
    // Verify admin is assigned to this area
    if(strpos($issue_location, $user_area) === false) {
        header("Location: feed.php?error=unauthorized");
        exit;

    }
    
    // Update issue status
    $update_issue = "UPDATE issues SET status = '$new_status' WHERE issue_id = $issue_id";
    if(mysqli_query($conn, $update_issue)) {
        
        // Create status entry
        $insert_status = "INSERT INTO status (timestamp) VALUES (NOW())";
        if(mysqli_query($conn, $insert_status)) {
            $status_id = mysqli_insert_id($conn);
            
            // Check if there's already an entry in issue_update
            $check_update = "SELECT * FROM issue_update WHERE issue_id = $issue_id";
            $update_result = mysqli_query($conn, $check_update);
            
            if(mysqli_num_rows($update_result) > 0) {
                // Update existing record
                $update_record = "UPDATE issue_update SET status_id = $status_id WHERE issue_id = $issue_id";
                mysqli_query($conn, $update_record);
            } else {
                // Create new record
                $insert_update = "INSERT INTO issue_update (status_id, issue_id, solved_by) VALUES ($status_id, $issue_id, $user_id)";
                mysqli_query($conn, $insert_update);
            }
            
            // If status is "Resolved", add to progress report
            if($new_status == 'Resolved') {
                // Get issue creation date
                $issue_query = "SELECT created_at FROM issues WHERE issue_id = $issue_id";
                $issue_result = mysqli_query($conn, $issue_query);
                $issue_date = mysqli_fetch_assoc($issue_result)['created_at'];
                
                // Add to progress report
                $add_report = "INSERT INTO progress_report (issue_date, resolved_date, updated_by) 
                               VALUES ('$issue_date', NOW(), $user_id)";
                mysqli_query($conn, $add_report);
            }
        }
        
        // Redirect based on where the request came from
        if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin_dashboard.php') !== false) {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: feed.php");
        }
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
} else {
    echo "Missing required fields!";
}
?>