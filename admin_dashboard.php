<?php
session_start();

// 1. SECURITY: Check if user is logged in AND is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// 2. DATA FETCHING: Get counts for the dashboard cards
$dept_count_query = "SELECT COUNT(*) as total FROM departments";
$dept_result = $conn->query($dept_count_query);
$dept_count = $dept_result->fetch_assoc()['total'];

$faculty_count_query = "SELECT COUNT(*) as total FROM faculty";
$faculty_result = $conn->query($faculty_count_query);
$faculty_count = $faculty_result->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Simple CSS for Layout */
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        
        /* Sidebar Styles */
        .sidebar { width: 250px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #1a252f; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #adb5bd; text-decoration: none; border-bottom: 1px solid #34495e; transition: 0.3s; }
        .sidebar a:hover { background-color: #34495e; color: white; }
        .sidebar a.active { background-color: #2980b9; color: white; }
        .logout { margin-top: auto; background-color: #c0392b; color: white !important; }

        /* Main Content Styles */
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Dashboard Cards */
        .cards-container { display: flex; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; text-align: center; }
        .card h3 { margin: 0; color: #7f8c8d; font-size: 1rem; }
        .card p { margin: 10px 0 0; font-size: 2rem; font-weight: bold; color: #2c3e50; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Timetable Admin</h2>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="manage_departments.php">Manage Departments</a>
        <a href="manage_faculty.php">Manage Faculty (HODs)</a>
        <a href="manage_rooms.php">Manage Rooms</a>
		<a href="workload.php">Workload Report</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Welcome, <?php echo $_SESSION['name']; ?></h1>
        </div>

        <div class="cards-container">
            <div class="card">
                <h3>Total Departments</h3>
                <p><?php echo $dept_count; ?></p>
            </div>
            <div class="card">
                <h3>Total Faculty</h3>
                <p><?php echo $faculty_count; ?></p>
            </div>
            <div class="card">
                <h3>System Status</h3>
                <p style="color: #27ae60; font-size: 1.5rem;">Active</p>
            </div>
        </div>
        
        <div style="margin-top: 50px;">
            <h3>Quick Actions</h3>
            <p>Select an option from the sidebar to start managing the college data.</p>
        </div>
    </div>

</body>
</html>