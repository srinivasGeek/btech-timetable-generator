<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: login.php");
    exit();
}

// DEFINING PATHS
// Ensure these folders exist: timetable/syllabus/r22 and timetable/syllabus/r25
$r22_path = "syllabus/r22/";
$r25_path = "syllabus/r25/";

// FUNCTION TO GET FILES
function getFiles($dir) {
    $files = [];
    if (is_dir($dir)) {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item != "." && $item != "..") {
                $files[] = $item;
            }
        }
    }
    return $files;
}

$r22_files = getFiles($r22_path);
$r25_files = getFiles($r25_path);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Syllabus View</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        
        /* Sidebar */
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        .active { background-color: #1abc9c; color: white; }
        
        /* Main Content */
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        
        /* Section Styles */
        .section-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 30px; }
        h3 { border-bottom: 2px solid #3498db; padding-bottom: 10px; color: #2c3e50; }
        
        /* File List */
        ul { list-style: none; padding: 0; }
        li { padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        li:last-child { border-bottom: none; }
        li:hover { background-color: #f9f9f9; }
        
        .file-name { font-weight: 500; color: #333; display: flex; align-items: center; gap: 10px; }
        .actions a { text-decoration: none; padding: 5px 10px; border-radius: 4px; font-size: 0.9em; margin-left: 5px; }
        .btn-view { background-color: #3498db; color: white; }
        .btn-down { background-color: #27ae60; color: white; }
        
        .empty-msg { color: #777; font-style: italic; padding: 10px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>HOD Panel</h2>
        <a href="hod_dashboard.php">Dashboard</a>
        <a href="hod_subjects.php">Manage Subjects</a>
        <a href="hod_staff.php">Manage Staff</a>
        <a href="hod_allotment.php">Course Allotment</a>
        <a href="generate_timetable.php">Generate Timetable</a>
        <a href="view_timetable.php">View Timetable</a>
        <a href="hod_syllabus.php" class="active">Syllabus Files</a> <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Syllabus Repository</h1>

        <div class="section-box">
            <h3>R22 Regulation Files</h3>
            <?php if (count($r22_files) > 0): ?>
                <ul>
                    <?php foreach ($r22_files as $file): ?>
                        <li>
                            <span class="file-name">📄 <?php echo $file; ?></span>
                            <span class="actions">
                                <a href="<?php echo $r22_path . $file; ?>" target="_blank" class="btn-view">👁 View</a>
                                <a href="<?php echo $r22_path . $file; ?>" download class="btn-down">⬇ Download</a>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-msg">No files found in syllabus/r22/ folder.</div>
            <?php endif; ?>
        </div>

        <div class="section-box">
            <h3>R25 Regulation Files</h3>
            <?php if (count($r25_files) > 0): ?>
                <ul>
                    <?php foreach ($r25_files as $file): ?>
                        <li>
                            <span class="file-name">📄 <?php echo $file; ?></span>
                            <span class="actions">
                                <a href="<?php echo $r25_path . $file; ?>" target="_blank" class="btn-view">👁 View</a>
                                <a href="<?php echo $r25_path . $file; ?>" download class="btn-down">⬇ Download</a>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-msg">No files found in syllabus/r25/ folder.</div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>