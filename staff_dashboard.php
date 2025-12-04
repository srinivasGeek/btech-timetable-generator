<?php
session_start();

// 1. SECURITY: Check if user is logged in AND is Staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// 2. GET USER & DEPARTMENT INFO
$my_id = $_SESSION['user_id'];
$my_name = $_SESSION['name'];
$my_dept_id = $_SESSION['dept_id'];

// Get Department Info
$dept_query = $conn->query("SELECT dept_name, dept_code FROM departments WHERE dept_id = '$my_dept_id'");
$dept_row = $dept_query->fetch_assoc();
$dept_name = $dept_row['dept_name'];
$dept_code = $dept_row['dept_code'];

// 3. CALCULATE PERSONAL WORKLOAD
$workload_sql = "SELECT SUM(s.hours_per_week) as total_hours
                 FROM course_allotment ca
                 JOIN subjects s ON ca.subject_id = s.subject_id
                 WHERE ca.faculty_id = '$my_id'";

$workload_result = $conn->query($workload_sql);
$total_load = $workload_result->fetch_assoc()['total_hours'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; font-size: 1.2rem;}
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        .sidebar a.active { background-color: #1abc9c; color: white; } 
        
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .welcome-banner { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); margin-bottom: 30px; }
        
        .cards-container { display: flex; gap: 20px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; text-align: center; border-top: 4px solid #1abc9c; }
        .card h3 { margin: 0; color: #7f8c8d; font-size: 0.9rem; text-transform: uppercase; }
        .card p { margin: 10px 0 0; font-size: 2.5rem; font-weight: bold; color: #2c3e50; }
        .card small { color: #888; font-size: 0.9rem; }

        .action-links { margin-top: 20px; display: flex; flex-direction: column; gap: 10px; }
        .action-links a { background: #3498db; color: white; padding: 12px; text-align: center; border-radius: 5px; text-decoration: none; transition: 0.3s; }
        .action-links a:hover { background: #2980b9; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2><?php echo $dept_code; ?> Staff</h2>
        <a href="staff_dashboard.php" class="active">Dashboard</a>
        <a href="faculty_timetable.php?fac_id=<?php echo $my_id; ?>">My Timetable</a>
        <a href="staff_view_faculty.php">Department Faculty</a>
        <a href="workload.php">Workload Report</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        
        <div class="welcome-banner">
            <h1 style="margin:0;">Welcome, <?php echo $my_name; ?></h1>
            <p style="margin:5px 0 0; color: #7f8c8d;">Department: <b><?php echo $dept_name; ?></b></p>
        </div>

        <div class="cards-container">
            <div class="card">
                <h3>Personal Workload</h3>
                <p style="color:#2980b9;"><?php echo $total_load; ?></p>
                <small>Assigned Hours/Week</small>
            </div>
            
            <div class="card">
                <h3>Current Status</h3>
                <p style="color:#27ae60;">Active</p>
                <small>System Online</small>
            </div>
        </div>
        
        <div class="action-links">
            <a href="faculty_timetable.php?fac_id=<?php echo $my_id; ?>">View My Detailed Timetable</a>
            <a href="workload.php">View Overall Workload Report</a>
            <a href="staff_view_faculty.php">View Department Faculty List</a>
        </div>
    </div>

</body>
</html>