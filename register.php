<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="Registration"/>
    <title>Registration - Local Problem Solving Hub</title>
    
    <!-- core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet"/>
    <link href="css/font-awesome.min.css" rel="stylesheet"/>
    <link href="css/animate.min.css" rel="stylesheet"/>
    <link href="css/main.css" rel="stylesheet"/>
</head>
<body>
    <!-- Menubar -->
    <section id="header">
        <div class="row">  
            <div class="col-md-2" style="font-size: 30px;color:#ffeba7;"> Local Problem Solving Hub </div>
            <div class="col-md-10" style="text-align: right">
                <a href="index.php"> Home </a>
                <a href="#" style="margin-left: 20px;"> Locals </a>
                <a href="#" style="margin-left: 20px;"> Admins </a>
            </div>
        </div>
    </section>
    
    <section id="section1">
        <div class="title"> REGISTRATION </div>
        
        <form action="register_process.php" class="form_design" method="post">
            Full Name: <input type="text" name="name" required> <br/>
            Email: <input type="email" name="email" required> <br/>
            Password: <input type="password" name="password" required> <br/>
            Confirm Password: <input type="password" name="confirm_password" required> <br/>
            
            Area:
            <select name="area" required>
                <option value="">Select your area</option>
                <option value="Dhanmondi">Dhanmondi</option>
                <option value="Gulshan">Gulshan</option>
                <option value="Mirpur">Mirpur</option>
                <option value="Uttara">Uttara</option>
                <option value="Bashundhara">Bashundhara Residential Area</option>
                <option value="Mohammadpur">Mohammadpur</option>
                <option value="Banani">Banani</option>
                <option value="Mohakhali">Mohakhali</option>
                <option value="Shyamoli">Shyamoli</option>
                <option value="Tejgaon">Tejgaon</option>
                <option value="Motijheel">Motijheel</option>
                <option value="Badda">Badda</option>
            </select> <br/>
            
            User Type:
            <select name="class" required>
                <option value="local">Local Resident</option>
                <option value="admin">Admin</option>
            </select> <br/><br/>
            
            <input type="submit" value="Register">
        </form>
        
        <div class="signin_link">
            Already have an account? <a href="index.php">Sign In</a>
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