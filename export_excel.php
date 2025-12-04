<?php
include 'includes/db_connect.php';

// 1. GET INPUTS
if (!isset($_GET['dept']) || !isset($_GET['sec']) || !isset($_GET['sem'])) {
    die("Error: Department, Section, or Semester missing.");
}
$dept = $_GET['dept'];
$sec  = $_GET['sec'];
$sem  = $_GET['sem'];

$filename = "Timetable_" . $dept . "_" . $sem . "_" . $sec . ".xls";

// 2. HEADERS FOR EXCEL DOWNLOAD
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// 3. HELPER
function generateAcronym($string) {
    $string = trim(preg_replace('/\s+/', ' ', $string));
    if(strlen($string) <= 4) return strtoupper($string);
    $words = explode(" ", $string);
    $acronym = "";
    foreach ($words as $w) {
        if (!empty($w)) $acronym .= $w[0];
    }
    return strtoupper($acronym);
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
    th, td { border: 1px solid #000; padding: 10px; text-align: center; }
    th { background-color: #4F81BD; color: white; }
    .lab { background-color: #DCE6F1; font-weight: bold; }
    .header-row { font-size: 18px; font-weight: bold; text-align: center; background-color: #FFFF00; }
    .sub-header { background-color: #E0E0E0; font-weight: bold; }
</style>
</head>
<body>

    <table>
        <tr>
            <td colspan="10" class="header-row">
                Weekly Timetable: <?php echo "$dept ($sem) - Section $sec"; ?>
            </td>
        </tr>
        <tr></tr>

        <tr>
            <th>Day</th>
            <th>09:20 - 10:10</th>
            <th>10:10 - 11:00</th>
            <th>Break</th>
            <th>11:20 - 12:10</th>
            <th>12:10 - 01:00</th>
            <th>Lunch</th>
            <th>01:50 - 02:40</th>
            <th>02:40 - 03:30</th>
            <th>03:30 - 04:20</th>
        </tr>

        <?php foreach ($days as $day): ?>
        <tr>
            <td style="font-weight:bold; background-color:#F2F2F2;"><?php echo $day; ?></td>
            
            <?php 
            for ($i = 1; $i <= 7; $i++) {
                // Break Columns (Empty Grey Cells)
                if ($i == 3 || $i == 5) echo "<td style='background-color:#D9D9D9;'></td>";

                // Fetch Data (Filtered by Semester)
                $sql = "SELECT s.subject_name, s.course_type 
                        FROM generated_timetable t
                        JOIN subjects s ON t.subject_code = s.subject_code
                        WHERE t.day='$day' AND t.slot_id='$i' 
                        AND s.department='$dept' AND t.section='$sec' AND s.semester='$sem'";
                
                $res = $conn->query($sql);

                if ($res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $text = generateAcronym($row['subject_name']);
                    $class = ($row['course_type'] == 'LAB') ? 'class="lab"' : '';
                    echo "<td $class>$text</td>";
                } else {
                    echo "<td>-</td>";
                }
            }
            ?>
        </tr>
        <?php endforeach; ?>
        
        <tr></tr><tr></tr>

        <tr>
            <td colspan="5" class="sub-header" style="text-align:left;">Subject & Faculty Details:</td>
        </tr>
        <tr>
            <th style="background-color:#808080;">Code</th>
            <th style="background-color:#808080;">Short Name</th>
            <th style="background-color:#808080;" colspan="2">Full Subject Name</th>
            <th style="background-color:#808080;" colspan="2">Faculty Name</th>
        </tr>

        <?php
        $sql_legend = "SELECT s.subject_code, s.subject_name, s.course_type, f.faculty_name 
                       FROM course_allotment ca
                       JOIN subjects s ON ca.subject_code = s.subject_code
                       JOIN faculty f ON ca.faculty_id = f.faculty_id
                       WHERE s.department = '$dept' AND ca.section = '$sec' AND s.semester = '$sem'
                       ORDER BY s.course_type DESC, s.subject_name";

        $res_legend = $conn->query($sql_legend);
        while($row = $res_legend->fetch_assoc()) {
            $short = generateAcronym($row['subject_name']);
            echo "<tr>";
            echo "<td>{$row['subject_code']}</td>";
            echo "<td>$short</td>";
            echo "<td colspan='2'>{$row['subject_name']}</td>";
            echo "<td colspan='2'>{$row['faculty_name']}</td>";
            echo "</tr>";
        }
        ?>

    </table>
</body>
</html>