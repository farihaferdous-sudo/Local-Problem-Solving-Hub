<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="utf-8"/>
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <meta name="description" content="About the site"/>
      <meta name="author" content="Author name"/>
      <title> Local Problem Solving Hub </title>
   
      <!-- core CSS -->
      <link href="css/bootstrap.min.css" rel="stylesheet"/>
      <link href="css/font-awesome.min.css" rel="stylesheet"/>
      <link href="css/animate.min.css" rel="stylesheet"/>
      <link href="css/main.css" rel="stylesheet"/>
  </head>
  <style>
    .success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 15px;
    border: 1px solid #c3e6cb;
    border-radius: 6px;
    margin: 20px auto;
    width: 90%;
    max-width: 600px;
    text-align: center;
    font-weight: bold;
  }

  </style>

  <body>
    <!-- following section is used for creating the menubar in the webpage -->
    <?php
    session_start();
    require_once('DBconnect.php');
    
    // Check if user is logged in
    if(!isset($_SESSION['email'])) {
        header("Location: index.php");
        exit;
    }
    
    // Get user class (admin or local)
    $email = $_SESSION['email'];
    $user_query = "SELECT * FROM users WHERE email = '$email'";
    $user_result = mysqli_query($conn, $user_query);
    $user = mysqli_fetch_assoc($user_result);
    $user_class = $user['class'];
    ?>
    
    <section id="header">
      <div class="row">  
        <div class="col-md-2" style="font-size: 30px;color:#ffeba7;"> Local Problem Solving Hub </div>
        <div class="col-md-10" style="text-align: right">
          <a href="home.php"> Home </a>
          <a href="feed.php" style="margin-left: 20px;"> Feed </a>
          <?php if($user_class == 'admin'): ?>
          <a href="admin_dashboard.php" style="margin-left: 20px;"> Admin Dashboard </a>
          <?php endif; ?>
          <a href="meetings.php" style="margin-left: 20px;"> Meetings </a>
          <a href="progress_report.php" style="margin-left: 20px;"> Progress Report </a>
          <a href="logout.php" style="margin-left: 20px;"> Logout </a>
        </div>
      </div>
    </section>
   
    <section id="section1">
      <div class="title">Report New Problem</div>
      <form action="report_issue.php" method="post" class="form_design">  
        <input type="text" name="title" placeholder="Problem Title" required>
        <input type="text" name="category" placeholder="Problem Category" required>
        <textarea name="description" placeholder="Description of the Problem" required></textarea>
        <button type="submit"> Submit </button>
      </form>
    </section>
   
    <!-- Footer -->
    <section id="footer">
    </section>
      <script src="js/jquery.js"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="js/jquery.isotope.min.js"></script>
      <script src="js/wow.min.js"></script>
  </body>

  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="success-message">✅ Problem reported successfully!</div>
  <?php endif; ?>

</html>