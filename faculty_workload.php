<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';

$branches = ["CSE", "CSE(AIML)", "CSE(DS)", "CSE(CYBER)", "ECE", "MECH", "S&H"];
$selected_dept = isset($_GET['dept']) ? $_GET['dept'] : 'ALL';

?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Workload Report</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #f8f9fa; color: #555; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .high-load { color: #d9534f; font-weight: bold; } /* Red for high load */
        .normal-load { color: #28a745; font-weight: bold; } /* Green for normal */
        
        .filter-box { margin-bottom: 20px; }
        select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        button { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>📊 Faculty Workload Report</h2>
        <button onclick="window.print()" style="background:#6c757d;">🖨️ Print Report</button>
    </div>

    <div class="filter-box">
        <form method="get">
            <label><strong>Filter by Faculty Department:</strong></label>
            <select name="dept">
                <option value="ALL">-- ALL DEPARTMENTS --</option>
                <?php foreach($branches as $b) { 
                    $sel = ($b == $selected_dept) ? 'selected' : '';
                    echo "<option value='$b' $sel>$b</option>"; 
                } ?>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Faculty Name</th>
                <th>Department</th>
                <th>Theory Classes<br><small>(1 Unit/hr)</small></th>
                <th>Lab Sessions<br><small>(1.5 Units/session)</small></th>
                <th>Total Workload</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 1. Get all faculty (filtered)
            $sql_fac = "SELECT * FROM faculty";
            if($selected_dept != 'ALL') {
                $sql_fac .= " WHERE department = '$selected_dept'";
            }
            $sql_fac .= " ORDER BY department, faculty_name";
            
            $res_fac = $conn->query($sql_fac);

            if ($res_fac->num_rows > 0) {
                while($fac = $res_fac->fetch_assoc()) {
                    $fid = $fac['faculty_id'];

                    // 2. Count THEORY slots (1 hour = 1 slot)
                    // We join 'subjects' to ensure we only count THEORY types
                    $sql_theory = "SELECT COUNT(*) as c FROM generated_timetable g 
                                   JOIN subjects s ON g.subject_code = s.subject_code
                                   WHERE g.faculty_id = '$fid' AND s.course_type = 'THEORY'";
                    $theory_count = $conn->query($sql_theory)->fetch_assoc()['c'];

                    // 3. Count LAB slots
                    // Labs occupy 3 slots per session. 
                    $sql_lab = "SELECT COUNT(*) as c FROM generated_timetable g 
                                JOIN subjects s ON g.subject_code = s.subject_code
                                WHERE g.faculty_id = '$fid' AND s.course_type = 'LAB'";
                    $lab_slots = $conn->query($sql_lab)->fetch_assoc()['c'];
                    
                    // Convert Lab Slots to Lab Sessions (Divide by 3)
                    $lab_sessions = $lab_slots / 3;

                    // 4. Calculate Total Workload
                    // Formula: (Theory * 1) + (Lab Sessions * 1.5)
                    $total_load = ($theory_count * 1) + ($lab_sessions * 1.5);

                    // Skip faculty with 0 load (Optional: Remove this 'if' if you want to see everyone)
                    if ($total_load == 0) continue;

                    // Visual Styling for High Load (e.g., > 12)
                    $load_class = ($total_load > 15) ? 'high-load' : 'normal-load';

                    echo "<tr>";
                    echo "<td style='text-align:left; font-weight:bold;'>{$fac['faculty_name']}</td>";
                    echo "<td>{$fac['department']}</td>";
                    echo "<td>$theory_count</td>";
                    echo "<td>$lab_sessions</td>";
                    echo "<td class='$load_class'>$total_load</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No faculty found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <p style="margin-top:20px; color:#666; font-size:14px;">
        <strong>Note:</strong> Workload is calculated across ALL departments the faculty teaches in. <br>
        <em>Formula: (Theory Count × 1) + (Lab Sessions × 1.5)</em>
    </p>

</div>

</body>
</html>