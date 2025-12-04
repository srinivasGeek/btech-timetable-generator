<?php
session_start();
// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// 1. FETCH DEPARTMENTS
$dept_query = $conn->query("SELECT * FROM departments");
$departments = [];
while($row = $dept_query->fetch_assoc()) {
    $departments[] = $row;
}

// 2. HANDLE FILTER
if ($_SESSION['role'] == 'hod') {
    $selected_dept_id = $_SESSION['dept_id'];
} else {
    $selected_dept_id = isset($_GET['dept']) ? $_GET['dept'] : 'ALL';
}

$min_workload = 20; 

// Arrays for Chart
$chart_names = [];
$chart_theory = [];
$chart_lab = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Workload Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background-color: #f4f4f9; }
        
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; height: 100vh; position: fixed;}
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; display: block; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        .sidebar a.active { background-color: #1abc9c; color: white; } 
        
        .main-content { margin-left: 250px; padding: 20px; width: 100%; }

        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 10px; color: #333; }
        
        .filter-box { background: #eee; padding: 15px; border-radius: 5px; display: flex; gap: 10px; align-items: center; margin-bottom: 20px; }
        select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; min-width: 200px; }
        button { padding: 8px 15px; color: white; border: none; border-radius: 4px; cursor: pointer; }
        
        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #f8f9fa; color: #555; }
        tr:nth-child(even) { background-color: #f9f9f9; }

        .status-low { color: #c0392b; background-color: #fadbd8; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .status-ok { color: #27ae60; background-color: #d5f5e3; padding: 4px 8px; border-radius: 4px; font-weight: bold; }

        .chart-box { height: 400px; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 40px; }
        
        @media print {
            .sidebar, .filter-box, .no-print { display: none; }
            .main-content { margin-left: 0; }
            .chart-box { height: 300px; page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <?php if($_SESSION['role'] == 'admin'): ?>
            <h2>Timetable Admin</h2>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_departments.php">Manage Departments</a>
            <a href="manage_faculty.php">Manage Faculty</a>
            <a href="manage_rooms.php">Manage Rooms</a>
            <a href="workload.php" class="active">Workload Report</a>
        <?php else: ?>
            <h2>HOD Panel</h2>
            <a href="hod_dashboard.php">Dashboard</a>
            <a href="hod_subjects.php">Manage Subjects</a>
            <a href="hod_staff.php">Manage Staff</a>
            <a href="hod_allotment.php">Course Allotment</a>
            <a href="generate_timetable.php">Generate Timetable</a>
            <a href="view_timetable.php">View Timetable</a>
            <a href="workload.php" class="active">Workload Report</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <div class="container">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>📊 Faculty Workload Report</h2>
                <div class="no-print">
                    <button onclick="exportToExcel()" style="background:#27ae60; margin-right:10px;">📥 Export Excel</button>
                    <button onclick="window.print()" style="background:#6c757d;">🖨️ Print</button>
                </div>
            </div>

            <div class="filter-box">
                <form method="get" style="display:flex; gap:10px; align-items:center; width:100%;">
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <label><strong>Department:</strong></label>
                        <select name="dept">
                            <option value="ALL">-- ALL DEPARTMENTS --</option>
                            <?php foreach($departments as $dept) { 
                                $sel = ($dept['dept_id'] == $selected_dept_id) ? 'selected' : '';
                                echo "<option value='".$dept['dept_id']."' $sel>".$dept['dept_name']."</option>"; 
                            } ?>
                        </select>
                        <button type="submit" style="background:#007bff;">Filter</button>
                    <?php else: ?>
                        <p><strong>Department:</strong> Showing data for your department.</p>
                    <?php endif; ?>
                </form>
                <div style="margin-left:auto; font-size:14px; color:#555;">
                    Target: <strong><?php echo $min_workload; ?>+ Hours/Week</strong>
                </div>
            </div>

            <div class="chart-box">
                <canvas id="workloadChart"></canvas>
            </div>

            <table id="workloadTable">
                <thead>
                    <tr>
                        <th style="text-align:left;">Faculty Name</th>
                        <th>Dept</th>
                        <th>Theory Hrs</th>
                        <th>Lab Hrs</th>
                        <th>Total Load</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_fac = "SELECT f.*, d.dept_name, d.dept_code 
                                FROM faculty f 
                                JOIN departments d ON f.dept_id = d.dept_id";
                    
                    if($selected_dept_id != 'ALL') {
                        $sql_fac .= " WHERE f.dept_id = '$selected_dept_id'";
                    }
                    $sql_fac .= " ORDER BY f.dept_id, f.name";
                    
                    $res_fac = $conn->query($sql_fac);

                    if ($res_fac->num_rows > 0) {
                        while($fac = $res_fac->fetch_assoc()) {
                            $fid = $fac['faculty_id'];
                            
                            $sql_load = "SELECT s.is_lab, s.hours_per_week 
                                         FROM course_allotment ca
                                         JOIN subjects s ON ca.subject_id = s.subject_id
                                         WHERE ca.faculty_id = '$fid'";
                            
                            $res_load = $conn->query($sql_load);
                            $theory_hours = 0;
                            $lab_hours = 0;

                            while($sub = $res_load->fetch_assoc()) {
                                if ($sub['is_lab'] == 1) $lab_hours += $sub['hours_per_week'];
                                else $theory_hours += $sub['hours_per_week'];
                            }
                            $total_load = $theory_hours + $lab_hours;

                            if($total_load > 0) {
                                $chart_names[] = $fac['name'];
                                $chart_theory[] = $theory_hours;
                                $chart_lab[] = $lab_hours;
                            }

                            if ($total_load >= $min_workload) {
                                $status = "<span class='status-ok'>OK</span>";
                            } else {
                                $diff = $min_workload - $total_load;
                                $status = "<span class='status-low'>Low (-$diff)</span>";
                            }

                            echo "<tr>";
                            echo "<td style='text-align:left;'><b>{$fac['name']}</b><br><small style='color:#777'>{$fac['designation']}</small></td>";
                            echo "<td>{$fac['dept_code']}</td>";
                            echo "<td>$theory_hours</td>";
                            echo "<td>$lab_hours</td>";
                            echo "<td><strong>$total_load</strong></td>";
                            echo "<td>$status</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No faculty found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const names = <?php echo json_encode($chart_names); ?>;
        const theoryData = <?php echo json_encode($chart_theory); ?>;
        const labData = <?php echo json_encode($chart_lab); ?>;
        const ctx = document.getElementById('workloadChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: names,
                datasets: [
                    { label: 'Theory', data: theoryData, backgroundColor: '#3498db', stack: 'Stack 0' },
                    { label: 'Lab', data: labData, backgroundColor: '#e74c3c', stack: 'Stack 0' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
            }
        });

        // EXPORT TO EXCEL FUNCTION
        function exportToExcel() {
            var uri = 'data:application/vnd.ms-excel;base64,';
            var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head></head><body><table>{table}</table></body></html>'; 
            var base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) };
            var format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) };

            var table = document.getElementById("workloadTable");
            var ctx = {worksheet: 'Workload Report', table: table.innerHTML};
            
            var link = document.createElement("a");
            link.download = "Faculty_Workload.xls";
            link.href = uri + base64(format(template, ctx));
            link.click();
        }
    </script>
</body>
</html>