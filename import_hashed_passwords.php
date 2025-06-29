<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "LabRescheduleManager"; 

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected to database<br>";

// === 1. Process students_raw.csv ===
$studentCSV = fopen("students_raw.csv", "r");
if (!$studentCSV) {
    die("Could not open students_raw.csv");
}
fgetcsv($studentCSV); // Skip header: Student_ID,RawPassword

while (($row = fgetcsv($studentCSV)) !== false) {
    $student_id = trim($row[0]);       // Student_ID
    $RawPassword = trim($row[1]);      // RawPassword

    if (!empty($student_id) && !empty($RawPassword)) {
        $hashed = password_hash($RawPassword, PASSWORD_DEFAULT);

        $update = "UPDATE student SET Hashed_Password = '$hashed' WHERE Student_ID = '$student_id'";
        if ($conn->query($update)) {
            echo "Student $student_id password hashed<br>";
        } else {
            echo "Error updating student $student_id: " . $conn->error . "<br>";
        }
    }
}
fclose($studentCSV);

// === 2. Process staff_raw.csv ===
$staffCSV = fopen("staff_raw.csv", "r");
if (!$staffCSV) {
    die("Could not open staff_raw.csv");
}
fgetcsv($staffCSV); // Skip header: Staff_ID,RawPassword

while (($row = fgetcsv($staffCSV)) !== false) {
    $staff_id = trim($row[0]);         // Staff_ID
    $RawPassword = trim($row[1]);      // RawPassword

    if (!empty($staff_id) && !empty($RawPassword)) {
        $hashed = password_hash($RawPassword, PASSWORD_DEFAULT);

        $update = "UPDATE staff SET HashedPassword = '$hashed' WHERE Staff_ID = '$staff_id'";
        if ($conn->query($update)) {
            echo "Staff $staff_id password hashed<br>";
        } else {
            echo "Error updating staff $staff_id: " . $conn->error . "<br>";
        }
    }
}
fclose($staffCSV);

$conn->close();
?>