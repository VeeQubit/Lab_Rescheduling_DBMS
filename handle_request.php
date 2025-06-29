<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coordinator') {
  http_response_code(403);
  echo json_encode(["error" => "Access denied"]);
  exit;
}

require 'db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["error" => "Invalid request method"]);
  exit;
}

$request_id = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$request_id || !in_array($action, ['approve', 'reject'])) {
  echo json_encode(["error" => "Invalid input"]);
  exit;
}

// Fetch request details
$stmt = $conn->prepare("SELECT Student_ID, LabID FROM reschedulerequest WHERE RequestID = ?");
$stmt->bind_param("s", $request_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
  echo json_encode(["error" => "Request not found"]);
  exit;
}

$r = $res->fetch_assoc();
$student_id = $r['Student_ID'];
$lab_id = $r['LabID'];
$stmt->close();

$status = $action === 'approve' ? 'Approved' : 'Rejected';
$coord_id = $_SESSION['user_id'];
$sender_type = 'staff'; // Define once and use for all notifications

// ✅ Update request status
$upd = $conn->prepare("UPDATE reschedulerequest SET Status = ?, ApprovedBy = ? WHERE RequestID = ?");
$upd->bind_param("sss", $status, $coord_id, $request_id);

if (!$upd->execute()) {
  echo json_encode(["error" => "DB update failed"]);
  exit;
}
$upd->close();

// ✅ Notify student
$msgS = "Your lab reschedule request (Lab $lab_id) has been $status.";

$notif = $conn->prepare("INSERT INTO Notification (sender_id, sender_type, message) VALUES (?, ?, ?)");
$notif->bind_param("sss", $coord_id, $sender_type, $msgS);
$notif->execute();
$notif_id_s = $conn->insert_id;
$notif->close();

$rec = $conn->prepare("INSERT INTO NotificationRecipient (notification_id, recipient_id, recipient_type, is_read) VALUES (?, ?, 'student', 0)");
$rec->bind_param("ss", $notif_id_s, $student_id);
$rec->execute();
$rec->close();
// ✅ Notify instructor if approved
if ($status === 'Approved') {
    $stmt2 = $conn->prepare("SELECT Instructor_ID FROM lab_instructor WHERE LabID = ?");
    $stmt2->bind_param("s", $lab_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();

    if ($res2->num_rows === 0) {
        $stmt2->close();
        echo json_encode(["error" => "No instructor assigned to LabID: $lab_id"]);
        exit;
    }

    $instr_id = $res2->fetch_assoc()['Instructor_ID'];
    $stmt2->close();

    $msgI = "Lab $lab_id reschedule approved for student $student_id.";
    $sender_type = 'coordinator';

    // 🔔 Insert into Notification
    $notif2 = $conn->prepare("INSERT INTO Notification (sender_id, sender_type, message) VALUES (?, ?, ?)");
    $notif2->bind_param("sss", $coord_id, $sender_type, $msgI);
    
    if (!$notif2->execute()) {
        echo json_encode(["error" => "Notification insert failed: " . $notif2->error]);
        exit;
    }

    $notif_id_i = $conn->insert_id;
    $notif2->close();

    // 👤 Insert into NotificationRecipient
    $rec2 = $conn->prepare("INSERT INTO NotificationRecipient (notification_id, recipient_id, recipient_type, is_read) VALUES (?, ?, 'staff', 0)");
    $rec2->bind_param("ss", $notif_id_i, $instr_id);
    
    if (!$rec2->execute()) {
        echo json_encode(["error" => "Recipient insert failed: " . $rec2->error]);
        exit;
    }

    $rec2->close();

    // ✅ Set IsNotified = 1
    $flag = $conn->prepare("UPDATE reschedulerequest SET IsNotified = 1 WHERE RequestID = ?");
    $flag->bind_param("s", $request_id);
    
    if (!$flag->execute()) {
        echo json_encode(["error" => "Failed to update IsNotified flag: " . $flag->error]);
        exit;
    }

    $flag->close();
}

$conn->close();
echo json_encode(["success" => true, "message" => "Request successfully $status"]);
