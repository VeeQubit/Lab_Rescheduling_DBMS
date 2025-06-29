<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$student_name = $_SESSION['user_name'];
$student_id = $_SESSION['user_id'];
 

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="dashboard.css" />
</head>
<body>
  <div class="main-wrapper">

    <!-- Header -->
    <div class="header">
      <h2>Welcome, <span id="student-name"><?php echo htmlspecialchars($student_name); ?>!</span></h2>

      <div class="header-right">
        <button id="notif-btn" title="Notifications">🔔</button>
        <button id="logout-btn" title="Logout">Logout</button>
      </div>
    </div>

    <!-- Notification Panel -->
    <div id="notif-panel" class="notif-panel hidden">
      <h3>Notifications</h3>
      <div id="notif-list">Loading...</div>
      <button id="notif-close-btn">Close</button>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab-btn" data-tab="new">New Request</button>
      <button class="tab-btn" data-tab="progress">Progress</button>
      <button class="tab-btn" data-tab="processed">Processed</button>
    </div>

    <!-- Default Message -->
    <div class="default-message" id="default-message">
      <p class="blinking">Click here to request</p>
      <button id="goto-new-request" class="center-button">Request Reschedule</button>
    </div>

    <!-- Tab: New Request -->
    <div class="tab-content" id="tab-new" hidden>
      <form id="request-form">
        <label for="medical-check">Do you have approved medical?</label>
        <select id="medical-check" required>
          <option value="">-- Select --</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
        </select>

        <div id="form-fields" hidden>
          <input type="text" name="student_id" id="student-id" readonly value="<?php echo htmlspecialchars($student_id); ?>" />

          <label for="course_id">Course ID</label>
          <input type="text" name="course_id" id="course_id" required />

          <label for="lab_id">Lab ID</label>
          <input type="text" name="lab_id" id="lab_id" required />

          <label for="batch">Batch</label>
          <input type="text" name="batch" id="batch" required />

          <label for="date">Date</label>
          <input type="date" name="date" id="date" required />

          <button type="submit">Request</button>
        </div>
      </form>
      <div id="request-message"></div>
    </div>

    <!-- Tab: Progress -->
    <div class="tab-content" id="tab-progress" hidden>
      <h3>Your Pending Requests</h3>
      <div id="progress-list">Loading...</div>
    </div>

    <!-- Tab: Processed -->
    <div class="tab-content" id="tab-processed" hidden>
      <h3>Your Processed Requests</h3>
      <div id="processed-list">Loading...</div>
    </div>

  </div> <!-- end of .main-wrapper -->

  <script src="dashboard.js"></script>
  <?php include 'footer.php'; ?> 
</body>

</html>
