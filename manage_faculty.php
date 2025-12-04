<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// 1. HANDLE ADD FACULTY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_faculty'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password']; 
    $dept_id = $_POST['dept_id'];
    $designation = $_POST['designation'];
    $availability = $_POST['availability']; // NEW FIELD

    $sql = "INSERT INTO faculty (name, email, password, role, dept_id, designation, availability) 
            VALUES ('$name', '$email', '$password', 'staff', '$dept_id', '$designation', '$availability')";

    if ($conn->query($sql) === TRUE) {
        $success = "Faculty added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// 2. HANDLE ACTIONS
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM faculty WHERE faculty_id=$id");
    }
    header("Location: manage_faculty.php");
    exit();
}

if (isset($_GET['make_hod'])) {
    $id = intval($_GET['make_hod']);
    $conn->query("UPDATE faculty SET role='hod' WHERE faculty_id=$id");
    header("Location: manage_faculty.php");
    exit();
}

if (isset($_GET['remove_hod'])) {
    $id = intval($_GET['remove_hod']);
    $conn->query("UPDATE faculty SET role='staff' WHERE faculty_id=$id");
    header("Location: manage_faculty.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Faculty</title>
    <style>
        body { margin: 0; font-family: sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #1a252f; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #adb5bd; text-decoration: none; border-bottom: 1px solid #34495e; }
        .sidebar a:hover { background-color: #34495e; color: white; }
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        input, select { padding: 10px; margin: 5px; width: 200px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 20px; background: #27ae60; color: white; border: none; cursor: pointer; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #34495e; color: white; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; color: white; }
        .badge-admin { background-color: #000; }
        .badge-hod { background-color: #e67e22; }
        .badge-staff { background-color: #7f8c8d; }
        .avail-morning { color: #d35400; font-weight: bold; }
        .avail-full { color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Timetable Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_departments.php">Manage Departments</a>
        <a href="manage_faculty.php" style="background-color: #2980b9; color: white;">Manage Faculty</a>
        <a href="manage_rooms.php">Manage Rooms</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Faculty Management</h1>

        <div class="container">
            <h3>Add New Faculty / HOD</h3>
            <?php if(isset($success)) echo "<p style='color:green'>$success</p>"; ?>
            
            <form method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="text" name="password" placeholder="Password" required>
                <input type="text" name="designation" placeholder="Designation" required>
                
                <select name="dept_id" required>
                    <option value="">Select Department</option>
                    <?php
                    $dept_res = $conn->query("SELECT * FROM departments");
                    while($d = $dept_res->fetch_assoc()) {
                        echo "<option value='".$d['dept_id']."'>".$d['dept_name']."</option>";
                    }
                    ?>
                </select>

                <select name="availability" required>
                    <option value="FULL_DAY">Full Day (9am - 4pm)</option>
                    <option value="MORNING">Morning Only (9am - 12pm)</option>
                </select>
                
                <button type="submit" name="add_faculty">Add User</button>
            </form>
        </div>

        <div class="container">
            <h3>Staff List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Dept</th>
                        <th>Role</th>
                        <th>Availability</th> <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT faculty.*, departments.dept_name FROM faculty 
                            LEFT JOIN departments ON faculty.dept_id = departments.dept_id 
                            ORDER BY dept_id ASC";
                    $result = $conn->query($sql);

                    while ($row = $result->fetch_assoc()) {
                        $badge = 'badge-staff';
                        if($row['role'] == 'admin') $badge = 'badge-admin';
                        if($row['role'] == 'hod') $badge = 'badge-hod';

                        $avail_class = ($row['availability'] == 'MORNING') ? 'avail-morning' : 'avail-full';

                        echo "<tr>";
                        echo "<td><b>" . $row['name'] . "</b><br><small>" . $row['designation'] . "</small></td>";
                        echo "<td>" . $row['dept_name'] . "</td>";
                        echo "<td><span class='badge $badge'>" . strtoupper($row['role']) . "</span></td>";
                        
                        // Display Availability
                        echo "<td class='$avail_class'>" . str_replace('_', ' ', $row['availability']) . "</td>";
                        
                        echo "<td>";
                        if($row['role'] != 'admin') {
                            if($row['role'] == 'staff') echo "<a href='manage_faculty.php?make_hod=" . $row['faculty_id'] . "' style='color:blue; margin-right:5px;'>Promote</a>";
                            else echo "<a href='manage_faculty.php?remove_hod=" . $row['faculty_id'] . "' style='color:orange; margin-right:5px;'>Demote</a>";
                            
                            echo "<a href='manage_faculty.php?delete=" . $row['faculty_id'] . "' style='color:red;' onclick='return confirm(\"Delete?\")'>Delete</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>