<?php
session_start();

// Check login and role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinator') {
    header("Location: ../login.php");
    exit();
}

// You must have stored coordinator name in session at login. If not, fetch it here.
$coordinatorName = $_SESSION['user_name'] ?? 'Coordinator';


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Coordinator Dashboard</title>
  <link rel="stylesheet" href="coordinator.css" />
</head>
<body>
  <div class="main-wrapper">
    <header class="header">
      <h2>Welcome, <span id="coordinator-name"><?= htmlspecialchars($coordinatorName) ?></span> ❗</h2>
      <button id="notif-btn" title="Notifications" aria-label="Notifications">🔔</button>
    </header>

    <nav class="tabs">
      <button class="tab-btn active" data-tab="request">Request</button>
      <button class="tab-btn" data-tab="approval">Approved / Rejected</button>
      <button class="tab-btn" data-tab="review">Review</button>
    </nav>

    <section id="prompt-section">
      <button id="check-request-btn">Check Request</button>
      <div class="blinker" id="blinking-msg">Click here to approve new request</div>
    </section>

    <section id="tab-content"></section>

    <aside class="notif-panel" id="notif-panel" aria-label="Notifications" aria-hidden="true">
      <h3>Notifications</h3>
      <ul id="notif-list"></ul>
      <button id="notif-close-btn">Close</button>
    </aside>

    <button id="logout-btn" title="Logout">Logout</button>
  </div>

  <script src="coordinator.js"></script>
  <?php include 'footer.php'; ?>
</body>
</html>
