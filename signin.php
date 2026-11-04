<?php
//database connection
require_once('DBconnect.php');

//to check if the input in the form text fields are not empty
if(isset($_POST['fname']) && isset($_POST['pass'])){
	//to check if this email and password exists in the database
	$e = $_POST['fname'];
	$p = $_POST['pass'];
	$sql = "select * from users where email = '$e' and password = '$p'";
	
	//to execute the query
	$result = mysqli_query($conn, $sql);
	
	//to check if it returns an empty set
	if(mysqli_num_rows($result) != 0){
		//echo "LET HIM ENTER";
		session_start();
		$_SESSION["email"] = $_POST["fname"];
		header("Location: home.php");
	}
	else{
		//echo "Email or Password is wrong";
		header("Location: index.php");
	}
	
}


?>