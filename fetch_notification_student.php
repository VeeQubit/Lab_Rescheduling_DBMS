<?php
session_start();
header('Content-Type: application/json');

// --- Ensure student is logged in ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}

require 'db/db_connect.php';

$student_id = $_SESSION['user_id'];

$sql = "
SELECT n.id AS id, n.message, n.sender_type, n.created_at, nr.is_read
FROM Notification n
JOIN NotificationRecipient nr ON n.id = nr.notification_id
WHERE nr.recipient_id = ? AND nr.recipient_type = 'student'
ORDER BY n.created_at DESC
LIMIT 50
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'sender_type' => $row['sender_type'],  // 'staff' in your case
        'created_at' => $row['created_at'],
        'is_read' => (bool)$row['is_read']
    ];
}

echo json_encode($notifications);
$conn->close();
