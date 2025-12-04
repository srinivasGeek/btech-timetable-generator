<?php
session_start();

// 1. SECURITY: Check if Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// 2. HANDLE FORM SUBMISSION (Add New Dept)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_dept'])) {
    $dept_name = $conn->real_escape_string($_POST['dept_name']);
    $dept_code = $conn->real_escape_string($_POST['dept_code']);

    $sql = "INSERT INTO departments (dept_name, dept_code) VALUES ('$dept_name', '$dept_code')";
    
    if ($conn->query($sql) === TRUE) {
        $success = "Department added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// 3. HANDLE DELETION
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM departments WHERE dept_id=$id");
    header("Location: manage_departments.php"); // Refresh page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments</title>
    <style>
        /* Reuse Dashboard CSS for consistency */
        body { margin: 0; font-family: sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #1a252f; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #adb5bd; text-decoration: none; border-bottom: 1px solid #34495e; }
        .sidebar a:hover { background-color: #34495e; color: white; }
        
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        
        /* Table & Form Styles */
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2c3e50; color: white; }
        
        input[type="text"] { padding: 10px; width: 300px; margin-right: 10px; }
        button { padding: 10px 15px; background: #27ae60; color: white; border: none; cursor: pointer; }
        .delete-btn { background: #c0392b; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.9em; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Timetable Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_departments.php" style="background-color: #2980b9; color: white;">Manage Departments</a>
        <a href="manage_faculty.php">Manage Faculty (HODs)</a>
        <a href="manage_rooms.php">Manage Rooms</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Manage Departments</h1>

        <div class="container">
            <h3>Add New Department</h3>
            <?php 
                if(isset($success)) echo "<p style='color:green'>$success</p>"; 
                if(isset($error)) echo "<p style='color:red'>$error</p>"; 
            ?>
            <form method="POST">
                <input type="text" name="dept_name" placeholder="Department Name (e.g. Mechanical)" required>
                <input type="text" name="dept_code" placeholder="Code (e.g. MECH)" required>
                <button type="submit" name="add_dept">Add Department</button>
            </form>
        </div>

        <div class="container" style="margin-top: 20px;">
            <h3>Existing Departments</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department Name</th>
                        <th>Code</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM departments");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['dept_id'] . "</td>";
                        echo "<td>" . $row['dept_name'] . "</td>";
                        echo "<td>" . $row['dept_code'] . "</td>";
                        echo "<td><a href='manage_departments.php?delete=" . $row['dept_id'] . "' class='delete-btn' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>