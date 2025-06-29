<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "labreschedulemanager");
if ($conn->connect_error) {
  echo json_encode([]);
  exit;
}

$student_id = $_SESSION['user_id'];
$sql = "SELECT RequestID, Course_ID, LabID, Date, Batch FROM reschedulerequest WHERE Student_ID = ? AND Status = 'Pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = [
    'request_id' => $row['RequestID'],
    'course_id' => $row['Course_ID'],
    'lab_id' => $row['LabID'],
    'date' => $row['Date'],
    'batch' => $row['Batch'],
  ];
}

echo json_encode($data);
?>
