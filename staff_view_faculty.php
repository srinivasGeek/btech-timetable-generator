<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

$my_dept_id = $_SESSION['dept_id'];
$my_id = $_SESSION['user_id'];
$my_name = $_SESSION['name'];

// Fetch all faculty members (HODs and Staff) in the current department
$sql = "SELECT f.name, f.designation, f.role, f.email, f.availability 
        FROM faculty f 
        WHERE f.dept_id = '$my_dept_id' 
        ORDER BY f.role DESC, f.name ASC"; // HODs listed first

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Department Faculty List</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #34495e; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }

        .badge-hod { background-color: #e67e22; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; }
        .badge-staff { background-color: #7f8c8d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; }
        .self-highlight { background-color: #d5f5e3; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Staff Panel</h2>
        <a href="staff_dashboard.php">Dashboard</a>
        <a href="faculty_timetable.php?fac_id=<?php echo $my_id; ?>">My Timetable</a>
        <a href="staff_view_faculty.php" class="active" style="background-color: #1abc9c; color: white;">Department Faculty</a>
        <a href="workload.php">Workload Report</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Department Faculty List</h1>
        <div class="container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Designation</th>
                        <th>Availability</th>
                        <th>Email (Login)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        $is_me = ($row['name'] == $my_name) ? 'self-highlight' : '';
                        $role_badge = ($row['role'] == 'hod') 
                            ? "<span class='badge-hod'>HOD</span>" 
                            : "<span class='badge-staff'>Staff</span>";

                        echo "<tr class='$is_me'>";
                        echo "<td>" . $row['name'] . "</td>";
                        echo "<td>" . $role_badge . "</td>";
                        echo "<td>" . $row['designation'] . "</td>";
                        echo "<td>" . str_replace('_', ' ', $row['availability']) . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>