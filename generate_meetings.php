<?php
require_once('DBconnect.php');

/**
 * Generate recurring meetings for the 2nd Saturday of each month
 * This can be run via cron job or on demand
 */
function generateRecurringMeetings() {
    global $conn;
    
    // Get system admin ID (for meeting creation)
    $admin_query = "SELECT UserID FROM users WHERE class = 'admin' ORDER BY UserID LIMIT 1";
    $admin_result = mysqli_query($conn, $admin_query);
    
    if(mysqli_num_rows($admin_result) == 0) {
        return "No admin found to create recurring meetings";
    }
    
    $admin_id = mysqli_fetch_assoc($admin_result)['UserID'];
    
    // Calculate the next 3 months of 2nd Saturday dates
    $dates = [];
    $currentMonth = date('n');
    $currentYear = date('Y');
    
    for($i = 0; $i < 3; $i++) {
        $month = ($currentMonth + $i) % 12;
        $month = $month == 0 ? 12 : $month; // Convert 0 to 12 for December
        $year = $currentYear + floor(($currentMonth + $i) / 12);
        
        // Find the first Saturday
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $firstSaturday = 1 + (6 - date('w', $firstDay)) % 7;
        
        // Second Saturday is the first Saturday + 7 days
        $secondSaturday = $firstSaturday + 7;
        $meetingDate = date('Y-m-d', mktime(0, 0, 0, $month, $secondSaturday, $year));
        
        $dates[] = $meetingDate;
    }
    
    // Check if meetings already exist for these dates and create if not
    $created = 0;
    foreach($dates as $date) {
        $check_query = "SELECT * FROM offline_meetings 
                        WHERE meeting_date = '$date' 
                        AND is_recurring = 1";
        $check_result = mysqli_query($conn, $check_query);
        
        if(mysqli_num_rows($check_result) == 0) {
            // Meeting doesn't exist, create it
            $meetingMonth = date('F', strtotime($date));
            $meetingYear = date('Y', strtotime($date));
            $title = "Monthly Community Meeting - $meetingMonth $meetingYear";
            $description = "Our regular monthly community meeting to discuss local issues and progress. This is automatically scheduled for the 2nd Saturday of every month.";
            $location = "Community Center Main Hall";
            
            $insert_query = "INSERT INTO offline_meetings 
                            (meeting_title, meeting_date, meeting_time, meeting_location, 
                             meeting_description, Meeting_Vote, arranged_by, is_recurring, recurrence_pattern) 
                            VALUES 
                            ('$title', '$date', '19:00:00', '$location', 
                             '$description', 0, $admin_id, 1, '2nd Saturday monthly')";
            
            if(mysqli_query($conn, $insert_query)) {
                $created++;
            }
        }
    }
    
    return "Created $created new recurring meetings";
}

// Execute the function and return result (useful for debugging)
echo generateRecurringMeetings();
?>