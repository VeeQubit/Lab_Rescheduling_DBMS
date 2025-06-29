<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "labreschedulemanager";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = trim($_POST['user_id']);
    $password = $_POST['password'];

    if (empty($user_id) || empty($password)) {
        echo "<script>alert('Please enter ID/email and password.'); window.history.back();</script>";
        exit();
    }

    // Check student first
    $sql_student = "SELECT Student_ID, Hashed_Password, Name FROM student WHERE Student_ID = ? OR Email = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("ss", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        if (password_verify($password, $student['Hashed_Password'])) {
            $_SESSION['user_id'] = $student['Student_ID'];
            $_SESSION['user_name'] = $student['Name'];
            $_SESSION['role'] = 'student';

            echo <<<HTML
            <html>
            <head>
                <title>Logging in...</title>
                <style>
                    body {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        font-family: Arial, sans-serif;
                        animation: fadeout 1s ease-in forwards;
                        background: #fff;
                    }
                    .msg {
                        font-size: 20px;
                        color: rgb(97, 40, 167);
                    }
                    @keyframes fadeout {
                        0% { opacity: 1; }
                        100% { opacity: 0; }
                    }
                </style>
                <script>
                    setTimeout(() => {
                        window.location.href = "student_dashboard.php";
                    }, 1000);
                </script>
            </head>
            <body>
                <div class="msg">Logging in as student... Please wait</div>
            </body>
            </html>
            HTML;
            exit();
        } else {
            echo "<script>alert('Incorrect password for student.'); window.history.back();</script>";
            exit();
        }
    }

    // Check staff if not student
    $sql_staff = "SELECT Staff_ID, HashedPassword, Name, Role FROM staff WHERE Staff_ID = ? OR Email = ?";
    $stmt = $conn->prepare($sql_staff);
    $stmt->bind_param("ss", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $staff = $result->fetch_assoc();
        if (password_verify($password, $staff['HashedPassword'])) {
            $_SESSION['user_id'] = $staff['Staff_ID'];
            $_SESSION['user_name'] = $staff['Name'];
            $_SESSION['role'] = strtolower($staff['Role']);

            if (strtolower($staff['Role']) === 'coordinator') {
                header("Location: coordinator_dashboard.php");
            } elseif (strtolower($staff['Role']) === 'instructor') {
                header("Location: instructor/instructor_dashboard.php");
            } else {
                echo "<script>alert('Invalid staff role.'); window.history.back();</script>";
            }
            exit();
        } else {
            echo "<script>alert('Incorrect password for staff.'); window.history.back();</script>";
            exit();
        }
    }

    echo "<script>alert('Invalid ID/email or password.'); window.history.back();</script>";
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>
