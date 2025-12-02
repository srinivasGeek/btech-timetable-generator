<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';

// Fetch stats
$fac_count = $conn->query("SELECT COUNT(*) as c FROM faculty")->fetch_assoc()['c'];
$sub_count = $conn->query("SELECT COUNT(*) as c FROM subjects")->fetch_assoc()['c'];
$allot_count = $conn->query("SELECT COUNT(*) as c FROM course_allotment")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html>
<head><title>Dashboard</title></head>
<body>

<div class="container">
    <div class="box" style="text-align:center; padding: 40px;">
        <h1 style="font-size: 2.5em; color: #007bff;">🎓 University Timetable System</h1>
        <p style="color: #666; font-size: 1.1em;">Manage Faculty, Subjects, and Generate Schedules efficiently.</p>
    </div>

    <div class="dashboard-grid">
        <div class="stat-card" style="border-color: #28a745;">
            <h3>Data Inputs</h3>
            <p>Faculty & Subjects</p>
            <div class="stat-number"><?php echo $fac_count + $sub_count; ?></div>
            <a href="input_data.php" class="btn btn-green">Manage Data</a>
        </div>

        <div class="stat-card" style="border-color: #fd7e14;">
            <h3>Allocations</h3>
            <p>Active Links</p>
            <div class="stat-number"><?php echo $allot_count; ?></div>
            <a href="input_data.php" class="btn btn-orange">Edit Allocations</a>
        </div>

        <div class="stat-card" style="border-color: #007bff;">
            <h3>Generator</h3>
            <p>Algorithm Status</p>
            <div style="font-size: 30px; margin: 10px 0;">⚙️</div>
            <a href="generate.php" class="btn btn-blue">Run Algorithm</a>
        </div>

        <div class="stat-card" style="border-color: #17a2b8;">
            <h3>Output</h3>
            <p>Final View</p>
            <div style="font-size: 30px; margin: 10px 0;">📅</div>
            <a href="view_timetable.php" class="btn btn-grey">View Tables</a>
        </div>
    </div>
</div>

</body>
</html>