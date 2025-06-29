<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coordinator') {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit();
}

require 'db/db_connect.php';

$staff_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notification_id = $_POST['notification_id'] ?? null;

    if (!$notification_id) {
        echo json_encode(["error" => "Missing notification ID"]);
        exit();
    }

    $sql = "
    UPDATE NotificationRecipient
    SET is_read = 1, read_at = NOW()
    WHERE notification_id = ? AND recipient_id = ? AND recipient_type = 'staff'
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $notification_id, $staff_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Failed to update"]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}
