<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Check if user exists
    $sql = "SELECT * FROM faculty WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 2. Set Session Variables
        $_SESSION['user_id'] = $row['faculty_id'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['dept_id'] = $row['dept_id'];

        // 3. Redirect based on Role
        if ($row['role'] == 'admin') {
            header("Location: welcome.php");
        } elseif ($row['role'] == 'hod') {
            header("Location: welcome.php");
        } else {
			
			header("Location: staff_dashboard.php");
        }
        exit(); // Stop script here
    } else {
        $error = "Invalid Email or Password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Timetable Login</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>

<div class="login-box">
    <h2 style="text-align:center;">Login</h2>
    <?php if(isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>