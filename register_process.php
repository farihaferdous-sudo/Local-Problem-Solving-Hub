<?php
// Database connection
require_once('DBconnect.php');

// Check if form is submitted
if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm_password']) && isset($_POST['area']) && isset($_POST['class'])) {
    
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $area = $_POST['area'];
    $class = $_POST['class'];
    
    // Validate passwords match
    if($password != $confirm_password) {
        echo "Passwords do not match!";
        exit;
    }
    
    // Check if email already exists
    $check_sql = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_result) > 0) {
        echo "Email already registered!";
        exit;
    }
    
    // Insert new user
    $insert_sql = "INSERT INTO users (name, email, password, selected_area, class, reputation) 
                  VALUES ('$name', '$email', '$password', '$area', '$class', 0)";
    
    if(mysqli_query($conn, $insert_sql)) {
        // Registration successful, redirect to login page
        header("Location: index.php?registered=true");
    } else {
        echo "Error: " . $insert_sql . "<br>" . mysqli_error($conn);
    }
} else {
    echo "All fields are required!";
}
?>