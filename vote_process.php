<?php
session_start();
require_once('DBconnect.php');

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Check if form is submitted
if(isset($_POST['issue_id']) && isset($_POST['vote_type'])) {
    
    // Get form data
    $issue_id = $_POST['issue_id'];
    $vote_type = $_POST['vote_type'];
    
    // Get user ID
    $email = $_SESSION['email'];
    $user_query = "SELECT UserID FROM users WHERE email = '$email'";
    $user_result = mysqli_query($conn, $user_query);
    $user_id = mysqli_fetch_assoc($user_result)['UserID'];
    
    // Check if user has already voted on this issue
    $check_vote = "SELECT * FROM votes WHERE user_id = $user_id AND issue_id = $issue_id";
    $vote_result = mysqli_query($conn, $check_vote);
    
    if(mysqli_num_rows($vote_result) > 0) {
        // User has already voted, update their vote
        $update_vote = "UPDATE votes SET vote_type = '$vote_type' WHERE user_id = $user_id AND issue_id = $issue_id";
        mysqli_query($conn, $update_vote);
    } else {
        // User has not voted, insert new vote
        $insert_vote = "INSERT INTO votes (user_id, issue_id, vote_type) VALUES ($user_id, $issue_id, '$vote_type')";
        mysqli_query($conn, $insert_vote);
    }
    
    // Update user's reputation
    if($vote_type == 'upvote') {
        // Get the issue owner
        $issue_query = "SELECT user_id FROM issues WHERE issue_id = $issue_id";
        $issue_result = mysqli_query($conn, $issue_query);
        $issue_owner_id = mysqli_fetch_assoc($issue_result)['user_id'];
        
        // Increase issue owner's reputation
        $update_rep = "UPDATE users SET reputation = reputation + 1 WHERE UserID = $issue_owner_id";
        mysqli_query($conn, $update_rep);
    }
    
    // Redirect back to feed
    header("Location: feed.php");
} else {
    echo "Missing required fields!";
}
?>