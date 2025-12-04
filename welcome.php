<?php
session_start();
// Security check is essential even for a welcome screen
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}
$faculty_name = explode(' ', $_SESSION['name'])[0]; // Use first name for a friendly greeting
$greeting_text = "Welcome, {$faculty_name}. Let's build a conflict-free schedule.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <style>
        body {
            background-color: #f4f4f9;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .welcome-box {
            background: white;
            padding: 40px 60px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            opacity: 0; /* Starts hidden */
            animation: fadeIn 1s ease-out 0.5s forwards; /* Fades in after 0.5s */
        }

        .greeting {
            font-size: 2.5rem;
            color: #34495e;
            overflow: hidden; /* Ensures text is hidden before typing */
            border-right: .15em solid #1abc9c; /* The typing cursor */
            white-space: nowrap;
            margin: 0 auto;
            letter-spacing: .05em;
            animation: 
                typing 3s steps(40, end) 1.5s forwards, /* Starts typing after 1.5s */
                blink-caret .75s step-end infinite; /* Blinks cursor */
            width: 0; /* Starts with zero width */
        }

        .subtext {
            margin-top: 20px;
            font-size: 1.2rem;
            color: #7f8c8d;
            opacity: 0;
            animation: fadeIn 1s ease-out 4.5s forwards; /* Fades in after typing completes */
        }

        /* --- KEYFRAMES --- */

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        @keyframes typing {
            from { width: 0 }
            to { width: 100% } /* Full width reveals text */
        }

        @keyframes blink-caret {
            from, to { border-color: transparent }
            50% { border-color: #1abc9c; }
        }
    </style>
</head>
<body>

    <div class="welcome-box">
        <div class="greeting">
            Hello, <?php echo $faculty_name; ?>!
        </div>
        <div class="subtext">
            Let's build a conflict-free schedule. 🚀
        </div>
        <div class="subtext">
            <a href="<?php echo ($_SESSION['role'] == 'admin') ? 'admin_dashboard.php' : 'hod_dashboard.php'; ?>" 
               style="text-decoration:none; background:#1abc9c; color:white; padding:10px 20px; border-radius:5px; margin-top:30px; display:inline-block;">
               Continue to Dashboard
            </a>
        </div>
    </div>

</body>
</html>