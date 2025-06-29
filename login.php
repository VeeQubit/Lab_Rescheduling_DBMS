<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lab Reschedule Management System</title>
  <link rel="stylesheet" href="login.css" />
</head>
<body>

  <!-- ✅ Fixed Header Message (Login page only) -->
  <div class="page-description">
    Developed to automate the workflow of lab session rescheduling and attendance management, reducing manual effort and ensuring timely updates.
  </div>

  <!-- ✅ Main Content Area -->
  <div class="container">

    <!-- Page 1: Initial Screen -->
    <section class="page1" id="page1">
      <div class="branding">
        <img src="logo.png" alt="Logo" class="logo" />
        <div class="institution">
          <h2>Faculty of Engineering</h2>
          <h3>University of Jaffna</h3>
        </div>
      </div>
      <h1 class="heading">Lab Reschedule Management System</h1>
      <button id="login-btn">LOGIN</button>
    </section>

    <!-- Page 2: Login Screen -->
    <section class="page2" id="page2" aria-hidden="true">
      <div class="left-half">
        <h1 class="heading-left">Lab Reschedule Management System</h1>
      </div>
      <div class="right-half">
        <form action="verify_login.php" method="POST" autocomplete="off" novalidate>
          <img src="form-logo.png" class="form-logo" />
          <h2 class="form-heading">Login</h2>

          <label for="user_id">Enter Email or University ID</label>
          <input type="text" id="user_id" name="user_id" required />

          <label for="password">Enter Password</label>
          <div class="password-wrapper">
            <input type="password" id="password" name="password" required />
            <button type="button" id="toggle-password" aria-label="Toggle password visibility">Show</button>
          </div>

          <button type="submit">Login</button>
        </form>
      </div>
    </section>

  </div>

  <!-- ✅ Fixed Footer Message (All pages) -->
  <footer>
    Designed and Developed by Varnaja Uthayaraj – Dept. of Computer Engineering, University of Jaffna | © 2025
  </footer>

  <script src="login.js"></script>
</body>
</html>
