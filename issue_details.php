<?php
session_start();
require_once('DBconnect.php');

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Check if issue ID is provided
if(!isset($_GET['id'])) {
    header("Location: feed.php");
    exit;
}

$issue_id = $_GET['id'];

// Get user information
$email = $_SESSION['email'];
$user_query = "SELECT * FROM users WHERE email = '$email'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$user_id = $user['UserID'];
$user_area = $user['selected_area'];
$user_class = $user['class'];

// Get issue details
$issue_query = "SELECT i.*, u.name FROM issues i JOIN users u ON i.user_id = u.UserID WHERE i.issue_id = $issue_id";
$issue_result = mysqli_query($conn, $issue_query);

// Check if issue exists
if(mysqli_num_rows($issue_result) == 0) {
    header("Location: feed.php");
    exit;
}

$issue = mysqli_fetch_assoc($issue_result);

// If user is not admin, check if they have access to this issue (area-based)
if($user_class != 'admin' && strpos($issue['location'], $user_area) === false) {
    header("Location: feed.php");
    exit;
}

// Get admin who is handling this issue (if any)
$handler_query = "SELECT u.name, u.UserID FROM issue_update iu JOIN users u ON iu.solved_by = u.UserID WHERE iu.issue_id = $issue_id";
$handler_result = mysqli_query($conn, $handler_query);
$handler = mysqli_num_rows($handler_result) > 0 ? mysqli_fetch_assoc($handler_result) : null;

// Get comments for this issue
$comments_query = "SELECT c.*, u.name FROM comment c 
                   JOIN posts p ON c.Comment_Id = p.comment_id 
                   JOIN users u ON p.user_ID = u.UserID 
                   WHERE p.issue_ID = $issue_id 
                   ORDER BY c.timestamp DESC";
$comments_result = mysqli_query($conn, $comments_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="Issue Details"/>
    <title>Issue Details - Local Problem Solving Hub</title>
    
    <!-- core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet"/>
    <link href="css/font-awesome.min.css" rel="stylesheet"/>
    <link href="css/animate.min.css" rel="stylesheet"/>
    <link href="css/main.css" rel="stylesheet"/>

    <style>
    body {
        background: url('img/bg1.jpg') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Segoe UI', sans-serif;
    }

    #section1 {
        max-width: 900px;
        margin: 50px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .title {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #343a40;
    }

    .issue_details h2 {
        font-size: 24px;
        color: #007bff;
    }

    .issue_header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .status {
        padding: 6px 12px;
        border-radius: 5px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 14px;
        color: white;
    }

    .status.pending {
        background-color: #ffc107;
    }

    .status.resolved {
        background-color: #28a745;
    }

    .status.in\ progress {
        background-color: #17a2b8;
    }

    .issue_meta p {
        margin: 5px 0;
        font-size: 15px;
    }

    .issue_description, .admin_actions, .comments_section {
        margin-top: 30px;
    }

    .issue_description h3,
    .admin_actions h3,
    .comments_section h3 {
        margin-bottom: 15px;
        color: #343a40;
        font-size: 20px;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 5px;
    }

    .comment {
        background: #f1f3f5;
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .comment_header {
        font-weight: bold;
        margin-bottom: 5px;
        color: #495057;
    }

    .comment_time {
        font-size: 12px;
        color: #868e96;
        margin-left: 10px;
    }

    .comment_form textarea {
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        margin-bottom: 10px;
        border: 1px solid #ced4da;
        border-radius: 5px;
        resize: vertical;
    }

    .comment_form button, .status_form button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .comment_form button:hover,
    .status_form button:hover {
        background-color: #0056b3;
    }

    .form_group {
        margin-bottom: 10px;
    }

    .form_group label {
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
    }

    .form_group select {
        padding: 6px;
        width: 100%;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }

    .back_link a {
        display: inline-block;
        margin-top: 20px;
        color: #007bff;
        text-decoration: none;
    }

    .back_link a:hover {
        text-decoration: underline;
    }

    .no_comments {
        color: #6c757d;
        font-style: italic;
    }
</style>



</head>
<body>
    <!-- Menubar -->
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
        <div class="title">Issue Details</div>
        
        <div class="issue_details">
            <div class="issue_header">
                <h2><?php echo $issue['title']; ?></h2>
                <span class="status <?php echo strtolower($issue['status']); ?>"><?php echo $issue['status']; ?></span>
            </div>
            
            <div class="issue_meta">
                <p><strong>Category:</strong> <?php echo $issue['category']; ?></p>
                <p><strong>Location:</strong> <?php echo $issue['location']; ?></p>
                <p><strong>Reported by:</strong> <?php echo $issue['name']; ?></p>
                <p><strong>Date Reported:</strong> <?php echo date('F j, Y g:i a', strtotime($issue['created_at'])); ?></p>
                <?php if($handler): ?>
                <p><strong>Being handled by:</strong> <?php echo $handler['name']; ?></p>
                <?php endif; ?>
            </div>
            
            <div class="issue_description">
                <h3>Description</h3>
                <p><?php echo $issue['description']; ?></p>
            </div>
            
            <?php if($user_class == 'admin' && $user_area == $issue['location']): ?>
            <div class="admin_actions">
                <h3>Admin Actions</h3>
                <form action="update_status.php" method="post" class="status_form">
                    <input type="hidden" name="issue_id" value="<?php echo $issue_id; ?>">
                    <div class="form_group">
                        <label for="new_status">Update Status:</label>
                        <select name="new_status" id="new_status">
                            <option value="Pending" <?php if($issue['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="In Progress" <?php if($issue['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                            <option value="Resolved" <?php if($issue['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
                        </select>
                    </div>
                    <button type="submit">Update Status</button>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="comments_section">
                <h3>Comments and Suggestions</h3>
                
                <?php
                if(mysqli_num_rows($comments_result) > 0) {
                    while($comment = mysqli_fetch_assoc($comments_result)) {
                ?>
                <div class="comment">
                    <div class="comment_header">
                        <strong><?php echo $comment['name']; ?></strong>
                        <span class="comment_time"><?php echo date('F j, Y, g:i a', strtotime($comment['timestamp'])); ?></span>
                    </div>
                    <p><?php echo $comment['Comment_text']; ?></p>
                </div>
                <?php
                    }
                } else {
                    echo "<p class='no_comments'>No comments or suggestions yet.</p>";
                }
                ?>
                
                <!-- Add Comment Form -->
                <form action="comment_process.php" method="post" class="comment_form">
                    <input type="hidden" name="issue_id" value="<?php echo $issue_id; ?>">
                    <textarea name="comment_text" placeholder="Add your comment or suggestion" required></textarea>
                    <button type="submit">Post Comment</button>
                </form>
            </div>
            
            <div class="back_link">
                <a href="<?php echo $user_class == 'admin' ? 'admin_dashboard.php' : 'feed.php'; ?>">
                    &larr; Back to <?php echo $user_class == 'admin' ? 'Admin Dashboard' : 'Feed'; ?>
                </a>
            </div>
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