<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coordinator') {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

require 'db/db_connect.php';

$staff_id = $_SESSION['user_id'];

$sql = "
SELECT n.id AS id, n.message, n.sender_type, n.created_at, nr.is_read
FROM Notification n
JOIN NotificationRecipient nr ON n.id = nr.notification_id
WHERE nr.recipient_id = ? AND nr.recipient_type = 'staff'
ORDER BY n.created_at DESC
LIMIT 50
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'type' => $row['sender_type'], // optional, if useful
        'created_at' => $row['created_at'],
        'is_read' => (bool)$row['is_read']
    ];
}

echo json_encode($notifications);
$conn->close();
