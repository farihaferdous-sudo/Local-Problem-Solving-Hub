<?php
session_start();
require_once('DBconnect.php');

// Check if user is logged in and is an admin
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Get user information
$email = $_SESSION['email'];
$user_query = "SELECT * FROM users WHERE email = '$email'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Verify user is admin
if($user['class'] != 'admin') {
    header("Location: home.php");
    exit;
}

$user_id = $user['UserID'];
$admin_area = $user['selected_area'];

// Get issues from admin's assigned area
$issues_query = "SELECT i.*, u.name 
                FROM issues i 
                JOIN users u ON i.user_id = u.UserID 
                WHERE i.location LIKE '%$admin_area%' 
                ORDER BY 
                    CASE 
                        WHEN i.status = 'Pending' THEN 1
                        WHEN i.status = 'In Progress' THEN 2
                        WHEN i.status = 'Resolved' THEN 3
                    END,
                    i.created_at DESC";
$issues_result = mysqli_query($conn, $issues_query);

// Get counts for dashboard statistics
$pending_query = "SELECT COUNT(*) as count FROM issues WHERE location LIKE '%$admin_area%' AND status = 'Pending'";
$in_progress_query = "SELECT COUNT(*) as count FROM issues WHERE location LIKE '%$admin_area%' AND status = 'In Progress'";
$resolved_query = "SELECT COUNT(*) as count FROM issues WHERE location LIKE '%$admin_area%' AND status = 'Resolved'";

$pending_result = mysqli_query($conn, $pending_query);
$in_progress_result = mysqli_query($conn, $in_progress_query);
$resolved_result = mysqli_query($conn, $resolved_query);

$pending_count = mysqli_fetch_assoc($pending_result)['count'];
$in_progress_count = mysqli_fetch_assoc($in_progress_result)['count'];
$resolved_count = mysqli_fetch_assoc($resolved_result)['count'];
$total_count = $pending_count + $in_progress_count + $resolved_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="Admin Dashboard"/>
    <title>Admin Dashboard - Local Problem Solving Hub</title>
    
    <!-- core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet"/>
    <link href="css/font-awesome.min.css" rel="stylesheet"/>
    <link href="css/animate.min.css" rel="stylesheet"/>
    <link href="css/main.css" rel="stylesheet"/>
</head>
<style>
    body {
        background: #f4f4f9;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 0;
        margin: 0;
    }

    #header {
        background-color:rgb(61, 88, 157, 0.9);
        padding: 15px 30px;
        color: #fff;
        font-size: 20px;
    }

    #header a {
        color:rgb(249, 248, 245);
        text-decoration: none;
        margin-left: 20px;
    }

    .title {
        text-align: center;
        margin: 30px 0 20px;
        font-size: 30px;
        font-weight: bold;
        color: #333;
    }

    .admin_stats {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat_box {
        background-color: #fff;
        border-radius: 10px;
        padding: 25px 40px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
        min-width: 180px;
    }

    .stat_box .stat_number {
        font-size: 36px;
        font-weight: bold;
        color: #007bff;
    }

    .stat_box .stat_label {
        font-size: 18px;
        color: #555;
        margin-top: 5px;
    }

    .issues_container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
    }

    .issue_card {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    }

    .issue_header h3 {
        margin: 0;
        font-size: 22px;
        color: #333;
    }

    .status {
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        margin-left: 10px;
        font-size: 14px;
    }

    .status.pending { background-color: #ffc107; color: #333; }
    .status.in\ progress { background-color: #17a2b8; color: white; }
    .status.resolved { background-color: #28a745; color: white; }

    .issue_meta span {
        display: block;
        color: #666;
        font-size: 14px;
        margin: 4px 0;
    }

    .issue_description {
        margin-top: 10px;
        color: #444;
    }

    .issue_actions {
        margin-top: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .issue_actions form select,
    .issue_actions form button {
        padding: 5px 10px;
        margin-right: 10px;
        font-size: 14px;
    }

    .view_details_btn {
        background-color: #007bff;
        color: #fff;
        padding: 6px 12px;
        border-radius: 5px;
        text-decoration: none;
    }

    .view_details_btn:hover {
        background-color: #0056b3;
    }

    .no_issues {
        text-align: center;
        color: #888;
        font-size: 16px;
    }
</style>

<body>
    <!-- Menubar -->
    <section id="header">
        <div class="row">  
            <div class="col-md-2" style="font-size: 30px;color:#ffeba7;"> Local Problem Solving Hub </div>
            <div class="col-md-10" style="text-align: right">
                <a href="home.php"> Home </a>
                <a href="feed.php" style="margin-left: 20px;"> Feed </a>
                <a href="admin_dashboard.php" style="margin-left: 20px;"> Admin Dashboard </a>
                <a href="meetings.php" style="margin-left: 20px;"> Meetings </a>
                <a href="progress_report.php" style="margin-left: 20px;"> Progress Report </a>
                <a href="logout.php" style="margin-left: 20px;"> Logout </a>
            </div>
        </div>
    </section>
    
    <section id="section1">
        <div class="title">Admin Dashboard - <?php echo $admin_area; ?> Area</div>
        
        <div class="admin_stats">
            <div class="stat_box pending">
                <div class="stat_number"><?php echo $pending_count; ?></div>
                <div class="stat_label">Pending</div>
            </div>
            <div class="stat_box in_progress">
                <div class="stat_number"><?php echo $in_progress_count; ?></div>
                <div class="stat_label">In Progress</div>
            </div>
            <div class="stat_box resolved">
                <div class="stat_number"><?php echo $resolved_count; ?></div>
                <div class="stat_label">Resolved</div>
            </div>
            <div class="stat_box total">
                <div class="stat_number"><?php echo $total_count; ?></div>
                <div class="stat_label">Total Issues</div>
            </div>
        </div>
        
        <div class="issues_container">
            <h3>Issues in <?php echo $admin_area; ?> (Your Assigned Area)</h3>
            
            <?php 
            if(mysqli_num_rows($issues_result) > 0) {
                while($issue = mysqli_fetch_assoc($issues_result)) {
                    $issue_id = $issue['issue_id'];
                    
                    // Get comment count for this issue
                    $comments_query = "SELECT COUNT(*) as count FROM posts WHERE issue_ID = $issue_id";
                    $comments_result = mysqli_query($conn, $comments_query);
                    $comments_count = mysqli_fetch_assoc($comments_result)['count'];
                    
                    // Get admin who is handling this issue (if any)
                    $handler_query = "SELECT u.name FROM issue_update iu JOIN users u ON iu.solved_by = u.UserID WHERE iu.issue_id = $issue_id";
                    $handler_result = mysqli_query($conn, $handler_query);
                    $handler_name = "";
                    if(mysqli_num_rows($handler_result) > 0) {
                        $handler_name = mysqli_fetch_assoc($handler_result)['name'];
                    }
            ?>
                <div class="issue_card">
                    <div class="issue_header">
                        <h3><?php echo $issue['title']; ?></h3>
                        <span class="status <?php echo strtolower($issue['status']); ?>"><?php echo $issue['status']; ?></span>
                    </div>
                    <div class="issue_meta">
                        <span>Category: <?php echo $issue['category']; ?></span>
                        <span>Location: <?php echo $issue['location']; ?></span>
                        <span>Reported by: <?php echo $issue['name']; ?></span>
                        <span>Date: <?php echo date('F j, Y', strtotime($issue['created_at'])); ?></span>
                        <?php if($handler_name): ?>
                        <span>Being handled by: <?php echo $handler_name; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="issue_description">
                        <p><?php echo $issue['description']; ?></p>
                    </div>
                    <div class="issue_actions">
                        <!-- Admin status update form -->
                        <form action="update_status.php" method="post" class="status_form">
                            <input type="hidden" name="issue_id" value="<?php echo $issue_id; ?>">
                            <select name="new_status">
                                <option value="Pending" <?php if($issue['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="In Progress" <?php if($issue['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                <option value="Resolved" <?php if($issue['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
                            </select>
                            <button type="submit">Update Status</button>
                        </form>
                        
                        <a href="issue_details.php?id=<?php echo $issue_id; ?>" class="view_details_btn">
                            View Details (<?php echo $comments_count; ?> comments)
                        </a>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p class='no_issues'>No issues found in your assigned area.</p>";
            }
            ?>
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