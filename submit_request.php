<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli("localhost", "root", "", "labreschedulemanager");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

$student_id = $_SESSION['user_id'] ?? '';

$course_id = $_POST['course_id'] ?? '';
$lab_id = $_POST['lab_id'] ?? '';
$batch = $_POST['batch'] ?? '';
$date = $_POST['date'] ?? '';

$request_id = uniqid("RQ");
$is_approved = 1;

// Validate
if (empty($student_id) || empty($course_id) || empty($lab_id) || empty($batch) || empty($date)) {
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit;
}

// 1. Insert into reschedulerequest
$stmt = $conn->prepare("INSERT INTO reschedulerequest (RequestID, Student_ID, LabID, Course_ID, Batch, Date, Status, IsMedicalApproved) 
                        VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)");
$stmt->bind_param("ssssssi", $request_id, $student_id, $lab_id, $course_id, $batch, $date, $is_approved);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Insert failed: " . $stmt->error]);
    exit;
}
$stmt->close();

// 2. Find coordinator for the course
$stmtCoord = $conn->prepare("SELECT ManageBy FROM course WHERE Course_ID = ?");
$stmtCoord->bind_param("s", $course_id);
$stmtCoord->execute();
$result = $stmtCoord->get_result();

$coordinator_id = null;
if ($row = $result->fetch_assoc()) {
    $coordinator_id = $row['ManageBy'];
}
$stmtCoord->close();

if (empty($coordinator_id)) {
    echo json_encode(["success" => false, "message" => "Coordinator not found for course."]);
    exit;
}

// 3. Create notification (sender_id is string)
$message = "Student ID $student_id submitted a reschedule request for course $course_id.";
$sender_type = 'student';

$stmtNotif = $conn->prepare("INSERT INTO Notification (sender_id, sender_type, message) VALUES (?, ?, ?)");
$stmtNotif->bind_param("sss", $student_id, $sender_type, $message);
$stmtNotif->execute();
$notification_id = $stmtNotif->insert_id;
$stmtNotif->close();

// 4. Add to NotificationRecipient (recipient_id is string)
$recipient_type = 'staff';
$is_read = 0;

$stmtRec = $conn->prepare("INSERT INTO NotificationRecipient (notification_id, recipient_id, recipient_type, is_read) 
                           VALUES (?, ?, ?, ?)");
$stmtRec->bind_param("issi", $notification_id, $coordinator_id, $recipient_type, $is_read);
$stmtRec->execute();
$stmtRec->close();

echo json_encode(["success" => true]);
$conn->close();
exit;
