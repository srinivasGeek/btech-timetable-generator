<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$branches = ["CSE", "CSE(AIML)", "CSE(DS)", "CSE(CYBER)", "ECE", "MECH", "S&H"];
$sections = ["A", "B", "C", "D", "E"];

$selected_dept = isset($_GET['dept']) ? $_GET['dept'] : 'CSE';

// --- HELPER FUNCTION: GENERATE ACRONYM (e.g. Computer Network -> CN) ---
function generateAcronym($string) {
    // If it's a short word (less than 4 chars), just show it (e.g. "C++")
    if(strlen($string) <= 4) return strtoupper($string);
    
    // Otherwise, take first letter of each word
    $words = explode(" ", $string);
    $acronym = "";
    foreach ($words as $w) {
        // Skip brackets like (Lab) if you want, or include them.
        // We will include everything alphanumeric
        if (!empty($w)) {
            $acronym .= $w[0];
        }
    }
    return strtoupper($acronym);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Timetable</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f9; padding: 20px; }
        .filter-bar { 
            background: white; padding: 15px; border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 30px; 
            display: flex; align-items: center; gap: 10px;
        }
        .table-container { 
            background: white; padding: 20px; border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 40px; 
            page-break-inside: avoid;
        }
        h2 { margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        
        /* GRID STYLES */
        .grid-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        .grid-table th, .grid-table td { border: 1px solid #ccc; padding: 10px; text-align: center; font-size: 14px; }
        .grid-table th { background-color: #007bff; color: white; }
        .lab-slot { background-color: #e6f7ff; color: #004085; }
        
        /* Subject Acronym Style */
        .sub-name { font-size: 18px; font-weight: bold; color: #333; display: block; }
        .fac-name { font-size: 11px; color: #666; }

        .break-col { background-color: #f3f3f3; writing-mode: vertical-rl; text-orientation: mixed; color: #777; font-size: 12px; }
        
        /* DETAILS TABLE */
        .details-table { width: 100%; border-collapse: collapse; font-size: 13px; border: 1px solid #ddd; }
        .details-table th { background-color: #f8f9fa; text-align: left; padding: 8px; border-bottom: 2px solid #ddd; color: #555; }
        .details-table td { padding: 6px 8px; border-bottom: 1px solid #eee; color: #333; }
        .print-btn { float: right; padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>

    <div class="filter-bar">
        <form method="get">
            <label><strong>Select Department:</strong></label>
            <select name="dept" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                <?php foreach($branches as $b) { 
                    $sel = ($b == $selected_dept) ? 'selected' : '';
                    echo "<option value='$b' $sel>$b</option>"; 
                } ?>
            </select>
            <button type="submit" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Show Timetables</button>
        </form>
        <button onclick="window.print()" class="print-btn">🖨️ Print Page</button>
    </div>

    <?php foreach ($sections as $sec): ?>
        
        <?php
        // Check if data exists
        $check_sql = "SELECT count(*) as c FROM generated_timetable g 
                      JOIN subjects s ON g.subject_code = s.subject_code
                      WHERE s.department = '$selected_dept' AND g.section = '$sec'";
        $exists = $conn->query($check_sql)->fetch_assoc()['c'];

        if ($exists == 0) continue; 
        ?>

        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid #007bff; margin-bottom:15px;">
    <h2 style="margin:0;"><?php echo "$selected_dept - Section $sec"; ?></h2>
    
    <div style="display:flex; gap:10px;">
        <a href="download_pdf.php?dept=<?php echo $selected_dept; ?>&sec=<?php echo $sec; ?>" 
           target="_blank"
           style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
           ⬇️ PDF
        </a>

        <a href="export_excel.php?dept=<?php echo $selected_dept; ?>&sec=<?php echo $sec; ?>" 
           target="_blank"
           style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
           📊 Excel
        </a>
    </div>
</div>
    </div>
    </div> 
            <table class="grid-table">
                <thead>
                    <tr>
                        <th width="80">Day</th>
                        <th>09:20 - 10:10</th>
                        <th>10:10 - 11:00</th>
                        <th width="30" class="break-col">Break</th>
                        <th>11:20 - 12:10</th>
                        <th>12:10 - 13:00</th>
                        <th width="30" class="break-col">Lunch</th>
                        <th>13:50 - 14:40</th>
                        <th>14:40 - 15:30</th>
                        <th>15:30 - 16:20</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($days as $day): ?>
                    <tr>
                        <td style="font-weight:bold; background:#f9f9f9;"><?php echo $day; ?></td>
                        <?php 
                        for ($i = 1; $i <= 7; $i++) {
                            // Now fetching 'subject_name' as well
                            $sql = "SELECT t.subject_code, s.subject_name, f.faculty_name, s.course_type 
                                    FROM generated_timetable t
                                    JOIN faculty f ON t.faculty_id = f.faculty_id 
                                    JOIN subjects s ON t.subject_code = s.subject_code
                                    WHERE t.day='$day' AND t.slot_id='$i' 
                                    AND s.department='$selected_dept' AND t.section='$sec'";
                            $res = $conn->query($sql);
                            
                            if ($res->num_rows > 0) {
                                $data = $res->fetch_assoc();
                                $class = ($data['course_type'] == 'LAB') ? 'lab-slot' : '';
                                
                                // Generate Acronym (e.g., Computer Networks -> CN)
                                $short_name = generateAcronym($data['subject_name']);

                                echo "<td class='$class'>";
                                echo "<span class='sub-name'>$short_name</span>";
                                echo "<span class='fac-name'>{$data['faculty_name']}</span>";
                                echo "</td>";
                            } else {
                                echo "<td>-</td>";
                            }
                            if ($i == 2) echo "<td class='break-col' rowspan='1'></td>";
                            if ($i == 4) echo "<td class='break-col' rowspan='1'></td>";
                        }
                        ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h4 style="margin-bottom: 5px; color:#555;">Legend (Subject Details)</h4>
            <table class="details-table">
                <thead>
                    <tr>
                        <th width="10%">Short Name</th>
                        <th width="40%">Full Subject Name</th>
                        <th width="35%">Faculty</th>
                        <th width="15%">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $details_sql = "SELECT s.subject_code, s.subject_name, s.course_type, f.faculty_name 
                                    FROM course_allotment ca
                                    JOIN subjects s ON ca.subject_code = s.subject_code
                                    JOIN faculty f ON ca.faculty_id = f.faculty_id
                                    WHERE s.department = '$selected_dept' AND ca.section = '$sec'
                                    ORDER BY s.course_type DESC, s.subject_name ASC";
                    
                    $details_res = $conn->query($details_sql);
                    while($row = $details_res->fetch_assoc()) {
                        $short_name = generateAcronym($row['subject_name']);
                        $type_color = ($row['course_type'] == 'LAB') ? '#e6f7ff' : '#fff';
                        
                        echo "<tr style='background-color:$type_color'>";
                        echo "<td><strong>$short_name</strong></td>";
                        echo "<td>{$row['subject_name']}</td>";
                        echo "<td>{$row['faculty_name']}</td>";
                        echo "<td>{$row['course_type']}</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

        </div>

    <?php endforeach; ?>

</body>
</html>