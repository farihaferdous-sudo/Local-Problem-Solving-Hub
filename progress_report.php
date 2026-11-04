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

// Determine which month to display (default to current month)
$current_month = date('m');
$current_year = date('Y');

if(isset($_GET['month']) && isset($_GET['year'])) {
    $month = (int) $_GET['month'];
    $year = $_GET['year'];
} else {
    $month = (int) $current_month;
    $year = $current_year;
}

// Get all months with issues for the dropdown
$months_query = "SELECT DISTINCT YEAR(created_at) as year, MONTH(created_at) as month 
                FROM issues 
                ORDER BY year DESC, month DESC";
$months_result = mysqli_query($conn, $months_query);

// Get statistics for selected month
if($user_class == 'admin') {
    // For admins, only show stats from their area
    $pending_query = "SELECT COUNT(*) as count FROM issues 
                     WHERE MONTH(created_at) = $month 
                     AND YEAR(created_at) = $year 
                     AND status = 'Pending'
                     AND location LIKE '%$user_area%'";
    
    $in_progress_query = "SELECT COUNT(*) as count FROM issues 
                         WHERE MONTH(created_at) = $month 
                         AND YEAR(created_at) = $year 
                         AND status = 'In Progress'
                         AND location LIKE '%$user_area%'";
    
    $resolved_query = "SELECT COUNT(*) as count FROM issues 
                      WHERE MONTH(created_at) = $month 
                      AND YEAR(created_at) = $year 
                      AND status = 'Resolved'
                      AND location LIKE '%$user_area%'";
    
    // Get recently resolved issues
    $resolved_issues_query = "SELECT i.*, u.name, pr.resolved_date, admin.name as admin_name
                             FROM issues i
                             JOIN users u ON i.user_id = u.UserID
                             JOIN progress_report pr ON DATE(i.created_at) = pr.issue_date
                             JOIN users admin ON pr.updated_by = admin.UserID
                             WHERE MONTH(i.created_at) = $month
                             AND YEAR(i.created_at) = $year
                             AND i.status = 'Resolved'
                             AND i.location LIKE '%$user_area%'
                             ORDER BY pr.resolved_date DESC
                             LIMIT 10";
} else {
    // For regular users, only show stats from their area
    $pending_query = "SELECT COUNT(*) as count FROM issues 
                     WHERE MONTH(created_at) = $month 
                     AND YEAR(created_at) = $year 
                     AND status = 'Pending'
                     AND location LIKE '%$user_area%'";
    
    $in_progress_query = "SELECT COUNT(*) as count FROM issues 
                         WHERE MONTH(created_at) = $month 
                         AND YEAR(created_at) = $year 
                         AND status = 'In Progress'
                         AND location LIKE '%$user_area%'";
    
    $resolved_query = "SELECT COUNT(*) as count FROM issues 
                      WHERE MONTH(created_at) = $month 
                      AND YEAR(created_at) = $year 
                      AND status = 'Resolved'
                      AND location LIKE '%$user_area%'";
    
    // Get recently resolved issues
    $resolved_issues_query = "SELECT i.*, u.name, pr.resolved_date, admin.name as admin_name
                             FROM issues i
                             JOIN users u ON i.user_id = u.UserID
                             JOIN progress_report pr ON DATE(i.created_at) = pr.issue_date
                             JOIN users admin ON pr.updated_by = admin.UserID
                             WHERE MONTH(i.created_at) = $month
                             AND YEAR(i.created_at) = $year
                             AND i.status = 'Resolved'
                             AND i.location LIKE '%$user_area%'
                             ORDER BY pr.resolved_date DESC
                             LIMIT 10";
}

$pending_result = mysqli_query($conn, $pending_query);
$in_progress_result = mysqli_query($conn, $in_progress_query);
$resolved_result = mysqli_query($conn, $resolved_query);
$resolved_issues_result = mysqli_query($conn, $resolved_issues_query);

$pending_count = mysqli_fetch_assoc($pending_result)['count'];
$in_progress_count = mysqli_fetch_assoc($in_progress_result)['count'];
$resolved_count = mysqli_fetch_assoc($resolved_result)['count'];
$total_count = $pending_count + $in_progress_count + $resolved_count;

// Calculate resolution rate
$resolution_rate = $total_count > 0 ? round(($resolved_count / $total_count) * 100) : 0;

// Get average resolution time for resolved issues
$avg_time_query = "SELECT AVG(TIMESTAMPDIFF(DAY, DATE(i.created_at), pr.resolved_date)) as avg_days
                   FROM issues i
                   JOIN progress_report pr ON DATE(i.created_at) = pr.issue_date
                   WHERE MONTH(i.created_at) = $month
                   AND YEAR(i.created_at) = $year
                   AND i.status = 'Resolved'
                   AND i.location LIKE '%$user_area%'";
$avg_time_result = mysqli_query($conn, $avg_time_query);
$avg_days = mysqli_fetch_assoc($avg_time_result)['avg_days'];
$avg_days = $avg_days ? round($avg_days, 1) : 'N/A';

// Get category distribution
$category_query = "SELECT category, COUNT(*) as count
                  FROM issues
                  WHERE MONTH(created_at) = $month
                  AND YEAR(created_at) = $year
                  AND location LIKE '%$user_area%'
                  GROUP BY category
                  ORDER BY count DESC";
$category_result = mysqli_query($conn, $category_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="Monthly Progress Report"/>
    <title>Monthly Progress Report - Local Problem Solving Hub</title>
    
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
    
    body {
        color: #333;
        background-color: #f4f4f4;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .title {
        font-size: 32px;
        font-weight: bold;
        margin: 30px 0 20px 0;
        text-align: center;
    }

    .month_selector {
        text-align: center;
        margin-bottom: 30px;
    }

    .month_selector select,
    .month_selector button {
        padding: 10px 15px;
        font-size: 16px;
        margin: 0 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
    }

    .month_selector button {
        background-color: #007bff;
        color: white;
        border: none;
        transition: 0.3s ease;
    }

    .month_selector button:hover {
        background-color: #0056b3;
    }

    .report_summary {
        max-width: 900px;
        margin: auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .progress_stats {
        display: flex;
        justify-content: space-around;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .stat_box {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        width: 180px;
        margin: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .stat_box .stat_number {
        font-size: 28px;
        font-weight: bold;
    }

    .stat_box.pending { border-left: 5px solid #ffc107; }
    .stat_box.in_progress { border-left: 5px solid #17a2b8; }
    .stat_box.resolved { border-left: 5px solid #28a745; }
    .stat_box.total { border-left: 5px solid #6c757d; }

    .additional_stats {
        margin-top: 25px;
        font-size: 16px;
    }

    .additional_stats .stat_item {
        margin: 10px 0;
    }

    .category_stats {
        margin-top: 30px;
    }

    .category_table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .category_table th,
    .category_table td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: center;
    }

    .category_table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .resolved_issues {
        max-width: 900px;
        margin: 40px auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .resolved_issue_card {
        border-bottom: 1px solid #e0e0e0;
        padding: 15px 0;
    }

    .resolved_issue_card h4 {
        margin-bottom: 10px;
        color: #007bff;
    }

    .issue_meta span {
        display: inline-block;
        margin-right: 15px;
        font-size: 14px;
        color: #555;
    }

    .view_details_link {
        display: inline-block;
        margin-top: 10px;
        color: #007bff;
        text-decoration: underline;
        transition: color 0.2s ease-in-out;
    }

    .view_details_link:hover {
        color: #0056b3;
    }

    .no_issues {
        color: #888;
        text-align: center;
        font-style: italic;
    }
    

    </style>
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
        <div class="title">Monthly Progress Report</div>
        
        <div class="month_selector">
            <form action="progress_report.php" method="get">
                <label for="month_select">Select Month: </label>
                <select name="month" id="month_select">
                    <?php
                    // Create array of month names
                    $month_names = [
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                    
                    // Display options from database
                    if(mysqli_num_rows($months_result) > 0) {
                        while($month_row = mysqli_fetch_assoc($months_result)) {
                            $m = $month_row['month'];
                            $y = $month_row['year'];
                            $selected = ($m == $month && $y == $year) ? 'selected' : '';
                            echo "<option value=\"$m\" data-year=\"$y\" $selected>{$month_names[$m]} $y</option>";
                        }
                    } else {
                        // If no months in database, show current month
                        echo "<option value=\"$current_month\" data-year=\"$current_year\" selected>{$month_names[$current_month]} $current_year</option>";
                    }
                    ?>
                </select>
                <input type="hidden" name="year" id="year_input" value="<?php echo $year; ?>">
                <button type="submit">View Report</button>
            </form>
        </div>
        
        <div class="report_summary">
            <h3>Summary for <?php echo $month_names[$month] . ' ' . $year; ?> - <?php echo $user_area; ?> Area</h3>
            
            <div class="progress_stats">
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
            
            <div class="additional_stats">
                <div class="stat_item">
                    <strong>Resolution Rate:</strong> <?php echo $resolution_rate; ?>%
                </div>
                <div class="stat_item">
                    <strong>Average Resolution Time:</strong> <?php echo $avg_days; ?> days
                </div>
            </div>
            
            <div class="category_stats">
                <h4>Issues by Category</h4>
                <table class="category_table">
                    <tr>
                        <th>Category</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                    <?php
                    if(mysqli_num_rows($category_result) > 0) {
                        while($category = mysqli_fetch_assoc($category_result)) {
                            $percentage = $total_count > 0 ? round(($category['count'] / $total_count) * 100) : 0;
                            echo "<tr>";
                            echo "<td>{$category['category']}</td>";
                            echo "<td>{$category['count']}</td>";
                            echo "<td>{$percentage}%</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No data available</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
        
        <div class="resolved_issues">
            <h3>Recently Resolved Issues</h3>
            
            <?php
            if(mysqli_num_rows($resolved_issues_result) > 0) {
                while($issue = mysqli_fetch_assoc($resolved_issues_result)) {
                    $days_to_resolve = round((strtotime($issue['resolved_date']) - strtotime($issue['created_at'])) / (60 * 60 * 24));
            ?>
            <div class="resolved_issue_card">
                <h4><?php echo $issue['title']; ?></h4>
                <div class="issue_meta">
                    <span>Category: <?php echo $issue['category']; ?></span>
                    <span>Reported by: <?php echo $issue['name']; ?></span>
                    <span>Resolved by: <?php echo $issue['admin_name']; ?></span>
                    <span>Resolution Time: <?php echo $days_to_resolve; ?> days</span>
                </div>
                <p><?php echo $issue['description']; ?></p>
                <a href="issue_details.php?id=<?php echo $issue['issue_id']; ?>" class="view_details_link">View Details</a>
            </div>
            <?php
                }
            } else {
                echo "<p class='no_issues'>No resolved issues in the selected month.</p>";
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
    
    <script>
    // Update year input when month changes
    document.getElementById('month_select').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var year = selectedOption.getAttribute('data-year');
        document.getElementById('year_input').value = year;
    });
    </script>
</body>
</html>