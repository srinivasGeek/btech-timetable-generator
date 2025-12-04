<?php
session_start();

// 1. SECURITY: Only HODs allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

$my_dept_id = $_SESSION['dept_id'];
$selected_faculty_id = isset($_GET['fac_id']) ? intval($_GET['fac_id']) : 0;

$faculty_list = [];
$schedule = [];
$total_workload = 0;
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$timeslots = [
    '09:20-10:10', '10:10-11:00', '11:20-12:10', '12:10-01:00', 
    '01:50-02:40', '02:40-03:30', '03:30-04:20'
];

// HELPER: GENERATE SHORT FORM
function getAbbreviation($name) {
    // Remove content in parentheses and any trailing/leading space
    $name = preg_replace('/\s*\(.*?\)\s*/', '', $name); 
    $words = explode(' ', $name);
    $acronym = "";
    $ignore = ['and', 'of', 'the', '&', 'in', 'lab', 'laboratory', 'for', 'to'];
    foreach ($words as $w) {
        $w = trim($w);
        if (!empty($w) && !in_array(strtolower($w), $ignore)) {
            $acronym .= strtoupper($w[0]);
        }
    }
    // Fallback if acronym is too short
    if (strlen($acronym) < 2) return strtoupper(substr($name, 0, 3));
    return $acronym;
}


// A. Fetch all faculty in the department for the dropdown filter
$fac_list_sql = "SELECT faculty_id, name, designation FROM faculty WHERE dept_id = '$my_dept_id' ORDER BY role DESC, name ASC";
$fac_list_res = $conn->query($fac_list_sql);
while($row = $fac_list_res->fetch_assoc()) {
    $faculty_list[] = $row;
}

// B. Fetch schedule and workload if a faculty member is selected
if ($selected_faculty_id > 0) {
    // 1. Get schedule data
    $sql = "SELECT t.*, s.subject_name, s.subject_code, r.room_no, r.room_type 
            FROM timetable t 
            JOIN subjects s ON t.subject_id = s.subject_id 
            JOIN rooms r ON t.room_id = r.room_id 
            WHERE t.faculty_id = '$selected_faculty_id'";

    $result = $conn->query($sql);

    // 2. Build schedule grid and calculate workload
    while($row = $result->fetch_assoc()) {
        $day = $row['day'];
        $time = $row['time_slot'];
        
        // --- NEW LOGIC: Use Short Name and Section ---
        $short_sub = getAbbreviation($row['subject_name']);
        $section = $row['section'];
        $room_no = $row['room_no'];

        $color = ($row['room_type'] == 'lab') ? '#e74c3c' : '#2980b9'; 
        
        // Display Short Subject Name and Section
        $info = "<div style='color:$color; font-weight:bold; font-size:1.1em; line-height: 1.2;'>$short_sub $section</div>";
        // Display Room No below
        $info .= "<small style='color:#555; font-size: 0.8em;'>Room: $room_no</small>";
        // ------------------------------------------

        $schedule[$day][$time] = $info;
        
        // Count total slots booked
        $total_workload++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Individual Timetable</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        .active { background-color: #1abc9c; color: white; }
        
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        /* Report Styles */
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; vertical-align: middle; }
        th { background-color: #34495e; color: white; height: 30px; font-size: 0.9em; }
        td { height: 60px; font-size: 0.9em; }
        
        .break-col { writing-mode: vertical-rl; text-orientation: mixed; font-weight: bold; color: #777; background-color: #eee; width: 30px; vertical-align: middle; font-size: 0.8em; }
        .workload-card { background-color: #ecf0f1; padding: 15px; border-radius: 5px; font-weight: bold; margin-bottom: 20px; text-align: center; }

        @media print {
            .sidebar, .filter-select { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>HOD Panel</h2>
        <a href="hod_dashboard.php">Dashboard</a>
        <a href="hod_subjects.php">Manage Subjects</a>
        <a href="hod_staff.php">Manage Staff</a>
        <a href="hod_allotment.php">Course Allotment</a>
        <a href="generate_timetable.php">Generate Timetable</a>
        <a href="view_timetable.php">View Timetable</a>
        <a href="workload.php">Workload Report</a>
        <a href="faculty_timetable.php" class="active">Individual Timetable</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <div class="header-section">
            <h1>Individual Faculty Schedule</h1>
            <button onclick="window.print()" style="background:#6c757d; color:white; border:none; padding:8px 15px; border-radius:4px;">🖨️ Print</button>
        </div>
        
        <div class="container">
            <form method="get" class="filter-select">
                <label for="fac_select">Select Faculty:</label>
                <select name="fac_id" id="fac_select" onchange="this.form.submit()" required>
                    <option value="">-- Select Faculty Member --</option>
                    <?php foreach($faculty_list as $fac) { 
                        $selected = ($fac['faculty_id'] == $selected_faculty_id) ? 'selected' : '';
                        echo "<option value='{$fac['faculty_id']}' $selected>{$fac['name']} ({$fac['designation']})</option>";
                    } ?>
                </select>
            </form>

            <?php if ($selected_faculty_id > 0): 
                $current_fac = array_filter($faculty_list, function($f) use ($selected_faculty_id) {
                    return $f['faculty_id'] == $selected_faculty_id;
                });
                $current_fac = array_values($current_fac)[0];
            ?>
                <h3 style="margin-top: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
                    Schedule for: <?php echo $current_fac['name']; ?>
                </h3>
                
                <div class="workload-card">
                    Total Contact Hours (Periods): **<?php echo $total_workload; ?>**
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>09:20<br>10:10</th>
                            <th>10:10<br>11:00</th>
                            <th class="break-col">Break</th>
                            <th>11:20<br>12:10</th>
                            <th>12:10<br>01:00</th>
                            <th class="break-col">Lunch</th>
                            <th>01:50<br>02:40</th>
                            <th>02:40<br>03:30</th>
                            <th>03:30<br>04:20</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($days as $day): ?>
                        <tr>
                            <td style="font-weight:bold; background:#f9f9f9;"><?php echo $day; ?></td>
                            
                            <td><?php echo isset($schedule[$day]['09:20-10:10']) ? $schedule[$day]['09:20-10:10'] : '-'; ?></td>
                            <td><?php echo isset($schedule[$day]['10:10-11:00']) ? $schedule[$day]['10:10-11:00'] : '-'; ?></td>
                            <td class="break-col">B</td>
                            <td><?php echo isset($schedule[$day]['11:20-12:10']) ? $schedule[$day]['11:20-12:10'] : '-'; ?></td>
                            <td><?php echo isset($schedule[$day]['12:10-01:00']) ? $schedule[$day]['12:10-01:00'] : '-'; ?></td>
                            <td class="break-col">L</td>
                            <td><?php echo isset($schedule[$day]['01:50-02:40']) ? $schedule[$day]['01:50-02:40'] : '-'; ?></td>
                            <td><?php echo isset($schedule[$day]['02:40-03:30']) ? $schedule[$day]['02:40-03:30'] : '-'; ?></td>
                            <td><?php echo isset($schedule[$day]['03:30-04:20']) ? $schedule[$day]['03:30-04:20'] : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center; margin-top:30px;">Please select a faculty member from the dropdown above to view their weekly schedule.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>