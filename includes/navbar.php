<style>
    /* Simple, lightweight CSS for the Navbar */
    body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f9; }
    .navbar {
        background-color: #333;
        overflow: hidden;
        padding: 10px 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .navbar a {
        float: left;
        display: block;
        color: white;
        text-align: center;
        padding: 10px 20px;
        text-decoration: none;
        font-size: 16px;
        border-radius: 4px;
    }
    .navbar a:hover {
        background-color: #575757;
    }
    .navbar a.active {
        background-color: #007bff;
        color: white;
    }
    .nav-brand {
        float: right;
        color: #aaa;
        padding: 10px 0;
        font-size: 14px;
    }
</style>

<link rel="stylesheet" href="/timetable/css/style.css">

<div class="navbar">
    <a href="index.php">🏠 Dashboard</a>
    <a href="input_data.php">📝 Data Entry</a>
    <a href="generate.php">⚙️ Generator</a>
    <a href="view_timetable.php">📅 View Table</a>
    <a href="faculty_workload.php">📊 Workload</a>
    
    <a href="reset_data.php" style="background-color:#c82333; float:right;">⚠️ Reset</a>
    <span class="nav-brand">B.Tech Scheduler Pro</span>
</div>