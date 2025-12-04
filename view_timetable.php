<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include 'includes/db_connect.php';

$my_dept_id = $_SESSION['dept_id'];
$role = $_SESSION['role'];

// 1. GET DEPT NAME
$dept_sql = "SELECT dept_name FROM departments WHERE dept_id = '$my_dept_id'";
$dept_res = $conn->query($dept_sql);
$dept_full_name = ($dept_res->num_rows > 0) ? $dept_res->fetch_assoc()['dept_name'] : "Engineering";

// ---------------------------------------------------------
// NEW ABBREVIATION LOGIC
// ---------------------------------------------------------
function getAbbreviation($name) {
    // 1. Clean brackets content (e.g. "Java (Practical)" -> "Java")
    $name = preg_replace('/\s*\(.*?\)\s*/', '', $name);
    
    // 2. Check if it's a Lab
    $is_lab_string = false;
    // Check case-insensitive for 'Lab' or 'Laboratory'
    if (stripos($name, 'Lab') !== false || stripos($name, 'Laboratory') !== false) {
        $is_lab_string = true;
        // Remove 'Lab' word so we don't acronymize it into 'L'
        $name = str_ireplace(['Laboratory', 'Lab'], '', $name);
    }

    // 3. Generate Acronym from remaining words
    $words = explode(' ', $name);
    $acronym = "";
    $ignore = ['and', 'of', 'the', '&', 'in', '-', 'for', 'to'];
    
    foreach ($words as $w) {
        $w = trim($w);
        // Take first letter of significant words
        if (!empty($w) && !in_array(strtolower($w), $ignore)) {
            $acronym .= strtoupper($w[0]);
        }
    }

    // Fallback: If acronym is empty or too short (1 char), take first 3 chars
    if (strlen($acronym) < 2) {
        $acronym = strtoupper(substr(trim($name), 0, 3));
    }

    // 4. Append ' LAB' if it was a lab
    if ($is_lab_string) {
        return $acronym . " LAB";
    }

    return $acronym;
}
// ---------------------------------------------------------

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$semesters = ["I-I", "I-II", "II-I", "II-II", "III-I", "III-II", "IV-I", "IV-II"];
$sections = ["A", "B", "C"];

$selected_sem = isset($_GET['sem_filter']) ? $_GET['sem_filter'] : "I-I";
$selected_sec = isset($_GET['sec_filter']) ? $_GET['sec_filter'] : 'A';

// FETCH DATA
$sql = "SELECT t.*, s.subject_name, s.subject_code, f.name as faculty_name, r.room_no, r.room_type 
        FROM timetable t 
        JOIN subjects s ON t.subject_id = s.subject_id 
        JOIN faculty f ON t.faculty_id = f.faculty_id 
        JOIN rooms r ON t.room_id = r.room_id 
        WHERE s.dept_id = '$my_dept_id' AND s.semester = '$selected_sem' AND t.section = '$selected_sec'";
$result = $conn->query($sql);

$schedule = [];
$legend_data = [];

while($row = $result->fetch_assoc()) {
    $day = $row['day'];
    $time = $row['time_slot'];
    
    // Use new function
    $short_sub = getAbbreviation($row['subject_name']);
    
    // CELL DISPLAY
    $color = ($row['room_type'] == 'lab') ? '#e74c3c' : '#2980b9'; 
    $info = "<div style='color:$color; font-weight:bold; font-size:1.1em;'>$short_sub</div>";
    
    $schedule[$day][$time] = $info;

    // LEGEND DATA
    $legend_data[$row['subject_code']] = [
        'short' => $short_sub,
        'full_sub' => $row['subject_name'],
        'full_fac' => $row['faculty_name'],
        'room' => $row['room_no'],
        'type' => ($row['room_type'] == 'lab') ? 'LAB' : 'THEORY'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Timetable</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        .active { background-color: #1abc9c; color: white; }
        
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        
        .print-header { display: none; text-align: center; margin-bottom: 10px; }
        .print-header img { width: 100%; max-height: 120px; object-fit: contain; }

        .filter-form { background: white; padding: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; gap: 10px; align-items: center;}
        
        /* Timetable Grid */
        table.timetable { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table.timetable th, table.timetable td { border: 1px solid #ddd; padding: 5px; text-align: center; vertical-align: middle; width: 10%; }
        table.timetable th { background-color: #34495e; color: white; height: 35px; font-size: 0.85em; }
        table.timetable td { height: 50px; } 
        
        .break-col { background-color: #f0f0f0; writing-mode: vertical-rl; text-orientation: mixed; font-weight: bold; color: #777; width: 30px; font-size: 0.8em;}

        /* Legend Grid */
        table.legend { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; font-size: 0.85em; }
        table.legend th, table.legend td { border: 1px solid #000; padding: 4px 8px; text-align: left; }
        table.legend th { background-color: #eee; text-align: center; font-weight: bold;}

        /* Print Styles */
        @media print {
            .sidebar, .filter-form, button { display: none !important; }
            body { display: block; background: white; margin: 0; padding: 0; height: auto; }
            .main-content { width: 100%; margin: 0; padding: 0; }
            .print-header { display: block; }
            table.timetable, table.legend { width: 100%; border: 1px solid #000; box-shadow: none; font-size: 9pt; }
            table.timetable th { background-color: #ddd !important; color: black !important; -webkit-print-color-adjust: exact; border: 1px solid #000; padding: 2px;}
            table.timetable td { border: 1px solid #000; padding: 2px; height: 40px; }
            h3, h4 { margin: 5px 0; text-align: center; }
            p { margin: 2px 0; }
            @page { margin: 0.5cm; size: landscape; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>HOD Panel</h2>
        <?php if($role == 'hod') { ?>
        <a href="hod_dashboard.php">Dashboard</a>
        <a href="hod_subjects.php">Manage Subjects</a>
        <a href="hod_staff.php">Manage Staff</a>
        <a href="hod_allotment.php">Course Allotment</a>
        <a href="generate_timetable.php">Generate Timetable</a>
        <?php } ?>
        <a href="view_timetable.php" class="active">View Timetable</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        
        <form method="GET" class="filter-form">
            <select name="sem_filter" style="padding:5px;">
                <option value="<?php echo $selected_sem; ?>"><?php echo $selected_sem; ?></option>
                <?php foreach($semesters as $s) { if($s!=$selected_sem) echo "<option value='$s'>$s</option>"; } ?>
            </select>
            <select name="sec_filter" style="padding:5px;">
                <option value="<?php echo $selected_sec; ?>">Section <?php echo $selected_sec; ?></option>
                <?php foreach($sections as $s) { if($s!=$selected_sec) echo "<option value='$s'>Section $s</option>"; } ?>
            </select>
            <button type="submit" style="padding:6px 15px; background:#2980b9; color:white; border:none; border-radius:3px; cursor:pointer;">View</button>
            
            <div style="margin-left: auto;">
                <button type="button" onclick="exportTimetable()" style="padding:6px 15px; background:#27ae60; color:white; border:none; border-radius:3px; cursor:pointer; margin-right:5px;">📥 Export Excel</button>
                <button type="button" onclick="window.print()" style="padding:6px 15px; background:#6c757d; color:white; border:none; border-radius:3px; cursor:pointer;">🖨️ Print</button>
            </div>
        </form>

        <div class="print-header">
            <img src="images/header.jpg" alt="College Header">
            <h3>Department of <?php echo $dept_full_name; ?></h3>
            <h4 style="text-decoration: underline;">Class Work Time Table</h4>
            <p><strong>Year/Sem:</strong> <?php echo $selected_sem; ?> &nbsp;|&nbsp; <strong>Section:</strong> <?php echo $selected_sec; ?> &nbsp;|&nbsp; <strong>A.Y:</strong> 2025-2026</p>
        </div>
        
        <table class="timetable" id="mainGrid">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>09:20<br>10:10</th>
                    <th>10:10<br>11:00</th>
                    <th style="width:30px; background:#ccc; color:#000;">11:00<br>11:20</th>
                    <th>11:20<br>12:10</th>
                    <th>12:10<br>01:00</th>
                    <th style="width:30px; background:#ccc; color:#000;">01:00<br>01:50</th>
                    <th>01:50<br>02:40</th>
                    <th>02:40<br>03:30</th>
                    <th>03:30<br>04:20</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($days as $day): ?>
                <tr>
                    <td style="font-weight:bold; background:#f9f9f9; vertical-align:middle;"><?php echo substr($day, 0, 3); ?></td>
                    <td><?php echo isset($schedule[$day]['09:20-10:10']) ? $schedule[$day]['09:20-10:10'] : '-'; ?></td>
                    <td><?php echo isset($schedule[$day]['10:10-11:00']) ? $schedule[$day]['10:10-11:00'] : '-'; ?></td>
                    <td class="break-col">BREAK</td>
                    <td><?php echo isset($schedule[$day]['11:20-12:10']) ? $schedule[$day]['11:20-12:10'] : '-'; ?></td>
                    <td><?php echo isset($schedule[$day]['12:10-01:00']) ? $schedule[$day]['12:10-01:00'] : '-'; ?></td>
                    <td class="break-col">LUNCH</td>
                    <td><?php echo isset($schedule[$day]['01:50-02:40']) ? $schedule[$day]['01:50-02:40'] : '-'; ?></td>
                    <td><?php echo isset($schedule[$day]['02:40-03:30']) ? $schedule[$day]['02:40-03:30'] : '-'; ?></td>
                    <td><?php echo isset($schedule[$day]['03:30-04:20']) ? $schedule[$day]['03:30-04:20'] : '-'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:10px;">
            <table class="legend" id="legendGrid">
                <thead>
                    <tr>
                        <th style="width:10%">Short</th>
                        <th style="width:35%">Full Subject Name</th>
                        <th style="width:35%">Faculty Name</th>
                        <th style="width:20%">Room / Lab</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($legend_data)) {
                        ksort($legend_data);
                        foreach($legend_data as $code => $data) {
                            echo "<tr>";
                            // Display the smart acronym here
                            echo "<td style='text-align:center'><b>{$data['short']}</b></td>";
                            echo "<td>{$data['full_sub']}</td>";
                            echo "<td>{$data['full_fac']}</td>";
                            echo "<td>{$data['room']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center'>No subjects scheduled.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <div style="margin-top:30px; display:flex; justify-content:space-between; padding:0 30px;">
                <div style="text-align:center;"><br><b>Time Table I/C</b></div>
                <div style="text-align:center;"><br><b>HOD</b></div>
                <div style="text-align:center;"><br><b>Principal</b></div>
            </div>
        </div>
    </div>

    <script>
    function exportTimetable() {
        var uri = 'data:application/vnd.ms-excel;base64,';
        var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head></head><body><table>{table}</table></body></html>'; 
        var base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) };
        var format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) };

        var gridHTML = document.getElementById("mainGrid").innerHTML;
        var legendHTML = document.getElementById("legendGrid").innerHTML;
        var headerRow = "<tr><td colspan='10' style='text-align:center; font-size:16px; font-weight:bold;'>Department of <?php echo $dept_full_name; ?> - Timetable (<?php echo $selected_sem.'-'.$selected_sec; ?>)</td></tr><tr><td></td></tr>";
        var spacerRow = "<tr><td></td></tr><tr><td colspan='10' style='font-weight:bold; background:#eee;'>Subject Details</td></tr>";

        var combinedHTML = headerRow + gridHTML + spacerRow + legendHTML;
        var ctx = {worksheet: 'Timetable', table: combinedHTML};
        
        var link = document.createElement("a");
        link.download = "Timetable_<?php echo $selected_sem; ?>_<?php echo $selected_sec; ?>.xls";
        link.href = uri + base64(format(template, ctx));
        link.click();
    }
    </script>
</body>
</html>