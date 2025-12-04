<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';

$branches = ["CSE", "CSE(AIML)", "CSE(DS)", "CSE(CYBER)", "ECE", "MECH", "S&H"];
$selected_dept = isset($_GET['dept']) ? $_GET['dept'] : 'ALL';
$min_workload = 20; 

// Arrays to hold data for the Chart
$chart_names = [];
$chart_theory = [];
$chart_lab = [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Workload Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #f8f9fa; color: #555; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .status-low { color: #dc3545; font-weight: bold; background-color: #ffeef0; }
        .status-ok { color: #28a745; font-weight: bold; background-color: #eaffea; }
        
        .filter-box { margin-bottom: 20px; display: flex; gap: 10px; align-items: center; background: #eee; padding: 10px; border-radius: 5px; }
        select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        button { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }

        /* CHART CONTAINER STYLES */
        .chart-box {
            width: 100%;
            height: 400px; /* Fixed height for the chart */
            margin-top: 30px;
            margin-bottom: 40px;
            padding: 20px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>📊 Faculty Workload Report</h2>
        <button onclick="window.print()" style="background:#6c757d;">🖨️ Print Report</button>
    </div>

    <div class="filter-box">
        <form method="get" style="display:flex; gap:10px; align-items:center; width:100%;">
            <label><strong>Filter Department:</strong></label>
            <select name="dept">
                <option value="ALL">-- ALL DEPARTMENTS --</option>
                <?php foreach($branches as $b) { 
                    $sel = ($b == $selected_dept) ? 'selected' : '';
                    echo "<option value='$b' $sel>$b</option>"; 
                } ?>
            </select>
            <button type="submit">Apply Filter</button>
            <div style="margin-left:auto; font-size:14px; color:#555;">Target: <strong><?php echo $min_workload; ?>+ Units</strong></div>
        </form>
    </div>

    <div class="chart-box">
        <canvas id="workloadChart"></canvas>
    </div>

    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Faculty Name</th>
                <th>Department</th>
                <th>Theory</th>
                <th>Lab Sessions</th>
                <th>Total Load</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql_fac = "SELECT * FROM faculty";
            if($selected_dept != 'ALL') $sql_fac .= " WHERE department = '$selected_dept'";
            $sql_fac .= " ORDER BY department, faculty_name";
            
            $res_fac = $conn->query($sql_fac);

            if ($res_fac->num_rows > 0) {
                while($fac = $res_fac->fetch_assoc()) {
                    $fid = $fac['faculty_id'];
                    
                    // Calculate Load
                    $sql_load = "SELECT s.course_type, s.lecture_hours_per_week 
                                 FROM course_allotment ca
                                 JOIN subjects s ON ca.subject_code = s.subject_code
                                 WHERE ca.faculty_id = '$fid'";
                    $res_load = $conn->query($sql_load);
                    
                    $theory_units = 0;
                    $lab_units = 0;
                    $lab_sessions = 0;

                    while($row = $res_load->fetch_assoc()) {
                        if ($row['course_type'] == 'LAB') {
                            $lab_sessions++;
                            $lab_units += 1.5;
                        } else {
                            $theory_units += $row['lecture_hours_per_week'];
                        }
                    }
                    $total_load = $theory_units + $lab_units;

                    // POPULATE CHART ARRAYS
                    // Only add to chart if they have some load (optional)
                    if($total_load > 0) {
                        $chart_names[] = $fac['faculty_name'];
                        $chart_theory[] = $theory_units;
                        $chart_lab[] = $lab_units;
                    }

                    // Status Logic
                    if ($total_load >= $min_workload) {
                        $status_text = "✅ Target Met";
                        $class = "status-ok";
                    } else {
                        $needed = $min_workload - $total_load;
                        $status_text = "⚠️ Low (-$needed)";
                        $class = "status-low";
                    }

                    echo "<tr>";
                    echo "<td style='text-align:left; font-weight:bold;'>{$fac['faculty_name']}</td>";
                    echo "<td>{$fac['department']}</td>";
                    echo "<td>$theory_units</td>";
                    echo "<td>$lab_sessions <small>($lab_units units)</small></td>";
                    echo "<td style='font-size:1.2em;'><strong>$total_load</strong></td>";
                    echo "<td class='$class'>$status_text</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No faculty found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    // Pass PHP Arrays to JavaScript
    const names = <?php echo json_encode($chart_names); ?>;
    const theoryData = <?php echo json_encode($chart_theory); ?>;
    const labData = <?php echo json_encode($chart_lab); ?>;

    const ctx = document.getElementById('workloadChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar', // Can be 'bar', 'line', 'pie', etc.
        data: {
            labels: names,
            datasets: [
                {
                    label: 'Theory Load',
                    data: theoryData,
                    backgroundColor: '#36a2eb', // Blue Color
                    stack: 'Stack 0',
                },
                {
                    label: 'Lab Load',
                    data: labData,
                    backgroundColor: '#ff6384', // Red Color
                    stack: 'Stack 0', // Same stack ID makes it stacked
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Faculty Workload Visualization (Stacked)',
                    font: { size: 18 }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: { 
                    stacked: true, 
                    ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 } // Rotate labels if too many names
                },
                y: { 
                    stacked: true,
                    beginAtZero: true,
                    title: { display: true, text: 'Workload Units' },
                    suggestedMax: 25 // Keeps chart height consistent
                }
            }
        }
    });
</script>

</body>
</html>