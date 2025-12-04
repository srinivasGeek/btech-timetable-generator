<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';

$branches = ["CSE", "CSE(AIML)", "CSE(DS)", "CSE(CYBER)", "ECE", "MECH", "S&H"];
$semesters = ["I-I", "I-II", "II-I", "II-II", "III-I", "III-II", "IV-I", "IV-II"];
$sections = ["A", "B", "C", "D", "E"];

$selected_dept = $_GET['dept'] ?? 'CSE';
$selected_sem  = $_GET['sem'] ?? 'III-I'; // Default to 3-1

function generateAcronym($string) {
    if(strlen($string) <= 4) return strtoupper($string);
    $words = explode(" ", $string); $acronym = "";
    foreach ($words as $w) { if (!empty($w)) $acronym .= $w[0]; }
    return strtoupper($acronym);
}
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
?>

<!DOCTYPE html>
<html>
<head><title>View Timetable</title></head>
<body>
<div class="container">
    <div class="filter-bar">
        <form method="get">
            <label>Dept:</label>
            <select name="dept">
                <?php foreach($branches as $b) { 
                    $sel = ($b == $selected_dept) ? 'selected' : ''; echo "<option value='$b' $sel>$b</option>"; 
                } ?>
            </select>
            
            <label>Semester:</label>
            <select name="sem">
                <?php foreach($semesters as $s) { 
                    $sel = ($s == $selected_sem) ? 'selected' : ''; echo "<option value='$s' $sel>$s</option>"; 
                } ?>
            </select>

            <button type="submit" class="btn btn-blue">View</button>
        </form>
    </div>

    <?php foreach ($sections as $sec): ?>
        <?php
        // Filter by Dept + Section + SEMESTER
        $check_sql = "SELECT count(*) as c FROM generated_timetable g 
                      JOIN subjects s ON g.subject_code = s.subject_code
                      WHERE s.department = '$selected_dept' AND g.section = '$sec' AND s.semester = '$selected_sem'";
        $exists = $conn->query($check_sql)->fetch_assoc()['c'];
        if ($exists == 0) continue; 
        ?>
        
        <div class="table-container">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid #007bff; margin-bottom:15px;">
                <h2 style="margin:0;"><?php echo "$selected_dept ($selected_sem) - Section $sec"; ?></h2>
                <div style="display:flex; gap:10px;">
                    <a href="download_pdf.php?dept=<?php echo $selected_dept; ?>&sec=<?php echo $sec; ?>&sem=<?php echo $selected_sem; ?>" target="_blank" class="btn btn-red" style="padding:8px 15px;">⬇️ PDF</a>
                    <a href="export_excel.php?dept=<?php echo $selected_dept; ?>&sec=<?php echo $sec; ?>&sem=<?php echo $selected_sem; ?>" target="_blank" class="btn btn-green" style="padding:8px 15px;">📊 Excel</a>
                </div>
            </div>

            <table class="grid-table">
                <thead>
                    <tr><th>Day</th><th>09:20-10:10</th><th>10:10-11:00</th><th class="break-col">Brk</th><th>11:20-12:10</th><th>12:10-01:00</th><th class="break-col">Lnh</th><th>01:50-02:40</th><th>02:40-03:30</th><th>03:30-04:20</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($days as $day): ?>
                    <tr>
                        <td style="font-weight:bold; background:#f9f9f9;"><?php echo $day; ?></td>
                        <?php 
                        for ($i = 1; $i <= 7; $i++) {
                            $sql = "SELECT t.subject_code, s.subject_name, f.faculty_name, s.course_type 
                                    FROM generated_timetable t
                                    JOIN faculty f ON t.faculty_id = f.faculty_id 
                                    JOIN subjects s ON t.subject_code = s.subject_code
                                    WHERE t.day='$day' AND t.slot_id='$i' 
                                    AND s.department='$selected_dept' AND t.section='$sec' AND s.semester='$selected_sem'";
                            $res = $conn->query($sql);
                            
                            if ($res->num_rows > 0) {
                                $data = $res->fetch_assoc();
                                $class = ($data['course_type'] == 'LAB') ? 'lab-slot' : '';
                                $short = generateAcronym($data['subject_name']);
                                echo "<td class='$class'><span style='font-weight:bold;'>$short</span><br><small>{$data['faculty_name']}</small></td>";
                            } else { echo "<td>-</td>"; }
                            if ($i == 2) echo "<td class='break-col' rowspan='1'></td>";
                            if ($i == 4) echo "<td class='break-col' rowspan='1'></td>";
                        }
                        ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>