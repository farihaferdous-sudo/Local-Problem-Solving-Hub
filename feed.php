<?php
session_start();
require_once('DBconnect.php');

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Get user information
$email = $_SESSION['email'];
$user_query = "SELECT * FROM users WHERE email = '$email'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$user_id = $user['UserID'];
$user_area = $user['selected_area'];
$user_class = $user['class'];

// Get issues from the user's area
if($user_class == 'admin') {
    // Admins can see all issues
    $issues_query = "SELECT i.*, u.name FROM issues i JOIN users u ON i.user_id = u.UserID ORDER BY i.created_at DESC";
} else {
    // Locals can only see issues from their area
    $issues_query = "SELECT i.*, u.name FROM issues i JOIN users u ON i.user_id = u.UserID WHERE i.location LIKE '%$user_area%' ORDER BY i.created_at DESC";
}
$issues_result = mysqli_query($conn, $issues_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="Feed"/>
    <title>Issue Feed - Local Problem Solving Hub</title>
    
    <!-- core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet"/>
    <link href="css/font-awesome.min.css" rel="stylesheet"/>
    <link href="css/animate.min.css" rel="stylesheet"/>
    <link href="css/main.css" rel="stylesheet"/>

    <style>
        body {
            background: url('your-background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .issues_container {
            display: flex;
            flex-direction: column;
            gap: 30px;
            padding: 40px;
            align-items: center;
        }

        .issue_card {
            width: 90%;
            max-width: 800px;
            padding: 25px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            color: #000;
            transition: transform 0.2s ease;
        }

        .issue_card:hover {
            transform: translateY(-5px);
        }

        .issue_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .issue_header h3 {
            margin: 0;
            font-size: 22px;
        }

        .status {
            font-size: 14px;
            padding: 4px 10px;
            border-radius: 12px;
            background: #f2f2f2;
            color: #555;
        }

        .issue_meta span {
            display: inline-block;
            margin-right: 15px;
            font-size: 14px;
        }

        .issue_description p {
            margin: 10px 0;
        }

        .arrow_vote_btn {
            font-size: 20px;
            background: none;
            border: none;
            cursor: pointer;
            margin: 5px;
        }

        .upvote {
            color: green;
        }

        .downvote {
            color: red;
        }

        .comment {
            padding: 10px;
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .comment_header {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
        }

        .comment_time {
            font-style: italic;
            color: #666;
        }

        textarea[name="comment_text"] {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
            resize: vertical;
        }

        .comment_form button,
        .status_form button {
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .comment_form button:hover,
        .status_form button:hover {
            background-color: #45a049;
        }

        .no_issues, .no_comments {
            text-align: center;
            color: #333;
            font-size: 18px;
            padding: 20px;
        }
    </style>

</head>
<body>
    <style>
    body {
        color: black !important;
        background-color: white !important;
    }
    </style>

    <!-- Menubar -->
    <section id="header">
        <div class="row">  
            <div class="col-md-2" style="font-size: 30px;color:#ffeba7;"> Local Problem Solving Hub </div>
            <div class="col-md-10" style="text-align: right">
                <a href="home.php"> Home </a>
                <a href="feed.php" style="margin-left: 20px;"> Feed </a>
                <a href="meetings.php" style="margin-left: 20px;"> Meetings </a>
                <a href="logout.php" style="margin-left: 20px;"> Logout </a>
            </div>
        </div>
    </section>
    
    <section id="section1">
    <?php if (isset($_GET['error']) && $_GET['error'] == 'unauthorized'): ?>
        <div class="alert alert-danger text-center" style="margin-top: 20px;">
            ⚠️ You are not authorized to update issues outside your assigned area.
        </div>
    <?php endif; ?>

        <div class="title">Issues in <?php echo ($user_class == 'admin') ? 'All Areas' : $user_area; ?></div>
        
        
        <div class="issues_container">
            <?php 
            if(mysqli_num_rows($issues_result) > 0) {
                while($issue = mysqli_fetch_assoc($issues_result)) {
                    // Get comment count for this issue
                    $issue_id = $issue['issue_id'];
                    $comments_query = "SELECT COUNT(*) as count FROM posts WHERE issue_ID = $issue_id";
                    $comments_result = mysqli_query($conn, $comments_query);
                    $comments_count = mysqli_fetch_assoc($comments_result)['count'];
                    
                    // Get votes for this issue
                    $upvotes_query = "SELECT COUNT(*) as count FROM votes WHERE issue_id = $issue_id AND vote_type = 'upvote'";
                    $upvotes_result = mysqli_query($conn, $upvotes_query);
                    $upvotes_count = mysqli_fetch_assoc($upvotes_result)['count'];
                    
                    $downvotes_query = "SELECT COUNT(*) as count FROM votes WHERE issue_id = $issue_id AND vote_type = 'downvote'";
                    $downvotes_result = mysqli_query($conn, $downvotes_query);
                    $downvotes_count = mysqli_fetch_assoc($downvotes_result)['count'];
                    
                    // Check if user has already voted
                    $user_vote_query = "SELECT vote_type FROM votes WHERE issue_id = $issue_id AND user_id = $user_id";
                    $user_vote_result = mysqli_query($conn, $user_vote_query);
                    $user_vote = "";
                    if(mysqli_num_rows($user_vote_result) > 0) {
                        $user_vote = mysqli_fetch_assoc($user_vote_result)['vote_type'];
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
                    </div>
                    <div class="issue_description">
                        <p><?php echo $issue['description']; ?></p>
                    </div>
                    <div class="issue_actions">
                        
                        
                        <div class="votes">
                            <!--  Upvote -->
                            <form action="vote_process.php" method="post" class="vote_form">
                                <input type="hidden" name="issue_id" value="<?php echo $issue_id; ?>">
                                <input type="hidden" name="vote_type" value="upvote">
                                <button type="submit" class="arrow_vote_btn upvote <?php if($user_vote == 'upvote') echo 'active'; ?>">
                                    ⬆️ <?php echo $upvotes_count; ?>
                                </button>
                            </form>

                            <!-- Downvote -->
                            <form action="vote_process.php" method="post" class="vote_form">
                                <input type="hidden" name="issue_id" value="<?php echo $issue_id; ?>">
                                <input type="hidden" name="vote_type" value="downvote">
                                <button type="submit" class="arrow_vote_btn downvote <?php if($user_vote == 'downvote') echo 'active'; ?>">
                                    ⬇️ <?php echo $downvotes_count; ?>
                                </button>
                            </form>
                        </div>



                        <?php if($user_class == 'admin' && $issue['status'] != 'Resolved'): ?>
                        <form action="update_status.php" method="post" class="status_form">
                            <input type="hidden" name="issue_id" value="<?php echo $issue_id; ?>">
                            <select name="new_status">
                                <option value="Pending" <?php if($issue['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="In Progress" <?php if($issue['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                <option value="Resolved" <?php if($issue['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
                            </select>
                            <button type="submit">Update Status</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Comments Section -->
                    <div class="comments_section">
                        <h4>Comments (<?php echo $comments_count; ?>)</h4>
                        
                        <?php
                        // Get comments for this issue
                        $get_comments_query = "SELECT c.Comment_text, c.timestamp, u.name 
                                             FROM comment c 
                                             JOIN posts p ON c.Comment_Id = p.comment_id 
                                             JOIN users u ON p.user_ID = u.UserID 
                                             WHERE p.issue_ID = $issue_id 
                                             ORDER BY c.timestamp DESC";
                        $get_comments_result = mysqli_query($conn, $get_comments_query);
                        
                        if(mysqli_num_rows($get_comments_result) > 0) {
                            while($comment = mysqli_fetch_assoc($get_comments_result)) {
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
                            echo "<p class='no_comments'>No comments yet.</p>";
                        }
                        ?>
                        
                        <!-- Add Comment Form -->
                        <form action="comment_process.php" method="post" class="comment_form">
                            <input type="hidden" name="issue_id" value="<?php echo $issue_id; ?>">
                            <textarea name="comment_text" placeholder="Add your comment or suggestion" required></textarea>
                            <button type="submit">Post Comment</button>
                        </form>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p class='no_issues'>No issues found in your area.</p>";
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