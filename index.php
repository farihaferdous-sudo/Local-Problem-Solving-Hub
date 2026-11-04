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
  <body>
    <!-- following section is used for creating the menubar in the webpage -->
    <section id="header">
        <div class="row">  
            <div class="col-md-2" style="font-size: 30px;color:#ffeba7;"> Local Problem Solving Hub </div>
            <div class="col-md-10" style="text-align: right">
                <a href="#"> Home </a>
                <a href="#" style="margin-left: 20px;"> Locals </a>
                <a href="#" style="margin-left: 20px;"> Admins </a>
            </div>
        </div>
    </section>
   
    <section id="section1">
        <div class="title"> SIGN IN </div>
        
        <?php
        // Show registration success message if redirected from registration
        if(isset($_GET['registered']) && $_GET['registered'] == 'true') {
            echo '<div class="success_message">Registration successful! Please sign in.</div>';
        }
        ?>
       
        <form action="signin.php" class="form_design" method="post">
            Email: <input type="text" name="fname"> <br/>
            Password: <input type="password" name="pass"> <br/> <br/>
            <input type="submit" value="Sign In">
        </form>
        
        <div class="register_link">
            Don't have an account? <a href="register.php">Register Now</a>
        </div>
    </section>
   
    <!-- Footer -->
    <section id="footer">
    </section>
    
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.isotope.min.js"></script>
    <script src="js/wow.min.js"></script>
  </body>
</html>