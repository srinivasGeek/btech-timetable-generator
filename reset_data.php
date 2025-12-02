<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';

$msg = "";

if (isset($_POST['confirm_reset'])) {
    // 1. Disable Foreign Key Checks (To allow truncation)
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // 2. Truncate (Empty) All Tables
    $conn->query("TRUNCATE TABLE generated_timetable");
    $conn->query("TRUNCATE TABLE course_allotment");
    $conn->query("TRUNCATE TABLE subjects");
    $conn->query("TRUNCATE TABLE faculty");

    // 3. Re-enable Foreign Key Checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    $msg = "<div style='color: green; font-weight: bold;'>✅ System Reset Complete. All data has been wiped.</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset System</title>
    <style>
        .warning-box {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h2 { color: #856404; margin-top: 0; }
        p { color: #856404; font-size: 18px; }
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn-danger:hover { background-color: #c82333; }
    </style>
</head>
<body>

    <div class="warning-box">
        <h2>⚠️ Danger Zone: Reset System</h2>
        
        <?php if ($msg) { echo $msg; } else { ?>
            
            <p>This action will delete <strong>ALL</strong> data, including:</p>
            <ul style="text-align:left; display:inline-block; color:#856404;">
                <li>All Faculty profiles</li>
                <li>All Subjects</li>
                <li>All Course Allocations</li>
                <li>The Generated Timetable</li>
            </ul>
            <p>Are you sure you want to start fresh?</p>

            <form method="post">
                <button type="submit" name="confirm_reset" class="btn-danger">
                    Yes, Delete Everything & Start Fresh
                </button>
            </form>
        
        <?php } ?>
    </div>

</body>
</html>