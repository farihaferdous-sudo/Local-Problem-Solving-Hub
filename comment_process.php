<?php
session_start();
require_once('DBconnect.php');

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Check if form is submitted
if(isset($_POST['issue_id']) && isset($_POST['comment_text'])) {
    
    // Get form data
    $issue_id = $_POST['issue_id'];
    $comment_text = mysqli_real_escape_string($conn, $_POST['comment_text']);
    
    // Get user ID
    $email = $_SESSION['email'];
    $user_query = "SELECT UserID FROM users WHERE email = '$email'";
    $user_result = mysqli_query($conn, $user_query);
    
    if(!$user_result) {
        die("Error fetching user: " . mysqli_error($conn));
    }
    
    $user_id = mysqli_fetch_assoc($user_result)['UserID'];
    
    // Begin transaction to ensure data consistency
    mysqli_begin_transaction($conn);
    
    try {
        // First insert the comment 
        $insert_comment = "INSERT INTO comment (Comment_text, timestamp) VALUES ('$comment_text', NOW())";
        
        if(!mysqli_query($conn, $insert_comment)) {
            throw new Exception("Error adding comment: " . mysqli_error($conn));
        }
        
        // Get the last inserted comment ID
        $comment_id = mysqli_insert_id($conn);
        
        // Link comment to issue and user in the posts table
        $insert_post = "INSERT INTO posts (comment_id, user_ID, issue_ID) VALUES ($comment_id, $user_id, $issue_id)";
        
        if(!mysqli_query($conn, $insert_post)) {
            throw new Exception("Error linking comment: " . mysqli_error($conn));
        }
        
        // Commit the transaction
        mysqli_commit($conn);
        
        // Determine where to redirect based on referer
        $redirect = 'feed.php';
        if(isset($_SERVER['HTTP_REFERER'])) {
            if(strpos($_SERVER['HTTP_REFERER'], 'issue_details.php') !== false) {
                $redirect = "issue_details.php?id=$issue_id";
            }
        }
        
        // Success, redirect back to original page
        header("Location: $redirect");
        exit;
        
    } catch (Exception $e) {
        // Roll back the transaction in case of error
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Missing required fields!";
}
?>