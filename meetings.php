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

// Include and run the meeting generator
include_once('generate_meetings.php');
generateRecurringMeetings();

// Get all meetings
$meetings_query = "SELECT m.*, u.name, u.selected_area 
                   FROM offline_meetings m 
                   JOIN users u ON m.arranged_by = u.UserID 
                   WHERE m.meeting_date >= CURDATE()
                   ORDER BY m.meeting_date ASC";
$meetings_result = mysqli_query($conn, $meetings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="Meetings"/>
    <title>Offline Meetings - Local Problem Solving Hub</title>
    
    <!-- core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet"/>
    <link href="css/font-awesome.min.css" rel="stylesheet"/>
    <link href="css/animate.min.css" rel="stylesheet"/>
    <link href="css/main.css" rel="stylesheet"/>
</head>
<body>
<style>
    body {
        color: black !important;
        background-color: white !important;
    }
    .meeting_card {
        border: 1px solid #ddd;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        background-color: #fdfdfd;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: box-shadow 0.3s ease;
    }

    .meeting_card:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    }

    .recurring_badge {
        background-color: #ffeb3b;
        color: #000;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 8px;
        display: inline-block;
        margin-bottom: 10px;
    }

    .attendance_btn {
        border: none;
        padding: 8px 16px;
        margin-right: 10px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        background-color: #e0e0e0;
        transition: background-color 0.3s ease;
    }

    .attendance_btn:hover {
        background-color: #ccc;
    }

    .attendance_btn.active,
    .attendance_btn.selected {
        background-color: #4caf50;
        color: white;
    }

    .cancel_btn {
        background-color: #f44336 !important;
        color: white;
    }

    
    body {
        color: black !important;
        background-color: white !important;
    }

    .create_meeting_section {
        background-color: #f9f9f9;
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .form_group {
        margin-bottom: 15px;
    }

    input[type="text"], input[type="date"], input[type="time"], textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
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
        <div class="title">Offline Community Meetings</div>
        
        <div class="recurring_meeting_info">
            <h3>Regular Monthly Meetings</h3>
            <p>Our community holds regular meetings on the <strong>2nd Saturday of every month at 7:00 PM</strong>. These meetings provide an opportunity for community members and admins to discuss local issues and track progress on resolved problems.</p>
            <p>Please indicate your attendance for upcoming meetings below.</p>
        </div>
        
        <?php if($user_class == 'admin'): ?>
        <!-- Admin can create meetings -->
        <div class="create_meeting_section">
            <h3>Create Special Meeting</h3>
            <form action="meeting_process.php" method="post" class="meeting_form">
                <div class="form_group">
                    <label for="meeting_title">Meeting Title:</label>
                    <input type="text" id="meeting_title" name="meeting_title" required>
                </div>
                <div class="form_group">
                    <label for="meeting_date">Date:</label>
                    <input type="date" id="meeting_date" name="meeting_date" required>
                </div>
                <div class="form_group">
                    <label for="meeting_time">Time:</label>
                    <input type="time" id="meeting_time" name="meeting_time" required>
                </div>
                <div class="form_group">
                    <label for="meeting_location">Location:</label>
                    <input type="text" id="meeting_location" name="meeting_location" required>
                </div>
                <div class="form_group">
                    <label for="meeting_description">Description:</label>
                    <textarea id="meeting_description" name="meeting_description" required></textarea>
                </div>
                <div class="form_group">
                    <button type="submit">Create Meeting</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <div class="meetings_container">
            <h3>Upcoming Meetings</h3>
            
            <?php 
            if(mysqli_num_rows($meetings_result) > 0) {
                while($meeting = mysqli_fetch_assoc($meetings_result)) {
                    $meeting_id = $meeting['Meeting_ID'];
                    $is_recurring = $meeting['is_recurring'];
                    
                    // Check if user has voted for attendance
                    $attendance_query = "SELECT attendance FROM meeting_attendees WHERE meeting_id = $meeting_id AND user_id = $user_id";
                    $attendance_result = mysqli_query($conn, $attendance_query);
                    $attendance = "";
                    if(mysqli_num_rows($attendance_result) > 0) {
                        $attendance = mysqli_fetch_assoc($attendance_result)['attendance'];
                    }
                    
                    // Get attendance counts
                    $going_query = "SELECT COUNT(*) as count FROM meeting_attendees WHERE meeting_id = $meeting_id AND attendance = 'going'";
                    $going_result = mysqli_query($conn, $going_query);
                    $going_count = mysqli_fetch_assoc($going_result)['count'];
                    
                    $not_going_query = "SELECT COUNT(*) as count FROM meeting_attendees WHERE meeting_id = $meeting_id AND attendance = 'not_going'";
                    $not_going_result = mysqli_query($conn, $not_going_query);
                    $not_going_count = mysqli_fetch_assoc($not_going_result)['count'];
            ?>
                <div class="meeting_card <?php echo $is_recurring ? 'recurring' : ''; ?>">
                    <?php if($is_recurring): ?>
                    <div class="recurring_badge">Monthly Recurring Meeting</div>
                    <?php endif; ?>
                    
                    <div class="meeting_info">

                        <h4><?php echo $meeting['meeting_title']; ?></h4>
                        <p><strong>📅 Date:</strong> <?php echo date('l, F j, Y', strtotime($meeting['meeting_date'])); ?></p>
                        <p><strong>🕖 Time:</strong> <?php echo date('g:i A', strtotime($meeting['meeting_time'])); ?></p>
                        <p><strong>📍 Location:</strong> <?php echo $meeting['meeting_location']; ?></p>
                        <p><strong>🧑 Arranged by:</strong> <?php echo $meeting['name']; ?> (<?php echo $meeting['selected_area']; ?>)</p>
                        <p><strong>📝 Description:</strong><br> <?php echo nl2br($meeting['meeting_description']); ?></p>

                        
                    </div>
                    
                    <div class="meeting_attendance">
                        <p><strong>Attendance:</strong> <?php echo $going_count; ?> going, <?php echo $not_going_count; ?> not going</p>
                        
                        <form action="meeting_attendance.php" method="post" class="attendance_form">
                            <input type="hidden" name="meeting_id" value="<?php echo $meeting_id; ?>">
                            <button type="submit" name="attendance" value="going" class="attendance_btn <?php if($attendance == 'going') echo 'active'; ?>">
                                Going
                            </button>
                            <button type="submit" name="attendance" value="not_going" class="attendance_btn <?php if($attendance == 'not_going') echo 'active'; ?>">
                                Not Going
                            </button>
                        </form>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p class='no_meetings'>No upcoming meetings scheduled.</p>";
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