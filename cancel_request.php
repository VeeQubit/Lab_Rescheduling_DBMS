<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "labreschedulemanager");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if (!isset($_POST['request_id'])) {
    echo json_encode(['success' => false, 'message' => 'Request ID missing']);
    exit;
}

$request_id = $_POST['request_id'];

$stmt = $conn->prepare("DELETE FROM reschedulerequest WHERE RequestID = ?");
$stmt->bind_param("s", $request_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete request']);
}
?>
