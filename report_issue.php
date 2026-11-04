<?php
    include("DBconnect.php");
    session_start();

    if (isset($_POST["title"]) && isset($_POST["description"]) && isset($_POST["category"])) {
        $sql_fetch = "SELECT * FROM users WHERE email = '{$_SESSION["email"]}'";
        $result_fetch = mysqli_query($conn, $sql_fetch);

        if(mysqli_num_rows($result_fetch) == 1) {
            $row = mysqli_fetch_assoc($result_fetch);
            
            $location = $row["selected_area"];
            $id = $row["UserID"];
            
            
            $sql_push = "INSERT INTO issues (description, title, category, location, user_id,status) VALUES ('{$_POST['description']}', '{$_POST['title']}', '{$_POST['category']}', '{$location}', '{$id}','Pending')";
            $result_push = mysqli_query($conn, $sql_push);

            if ($result_push) {
                header("Location: home.php?success=1");
                exit();
            } else {
                header("Location: home.php?success=0");
                exit();
            }
        }
    }


?>