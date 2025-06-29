
// --- DOM references ---
const notifBtn = document.getElementById('notif-btn');
const notifPanel = document.getElementById('notif-panel');
const notifCloseBtn = document.getElementById('notif-close-btn');
const logoutBtn = document.getElementById('logout-btn');

const tabButtons = document.querySelectorAll('.tab-btn');
const defaultMessage = document.getElementById('default-message');
const tabNew = document.getElementById('tab-new');
const tabProgress = document.getElementById('tab-progress');
const tabProcessed = document.getElementById('tab-processed');

const gotoNewRequestBtn = document.getElementById('goto-new-request');
const medicalCheck = document.getElementById('medical-check');
const formFields = document.getElementById('form-fields');
const requestForm = document.getElementById('request-form');
const requestMessage = document.getElementById('request-message');

const progressList = document.getElementById('progress-list');
const processedList = document.getElementById('processed-list');

// --- Helper to switch tabs ---
function switchTab(tabName) {
  // Hide default message
  defaultMessage.style.display = 'none';

  // Hide all tab contents
  tabNew.hidden = true;
  tabProgress.hidden = true;
  tabProcessed.hidden = true;

  // Remove active class from all tab buttons
  tabButtons.forEach(btn => btn.classList.remove('active'));

  // Show selected tab content & highlight tab button
  if (tabName === 'new') {
    tabNew.hidden = false;
  } else if (tabName === 'progress') {
    tabProgress.hidden = false;
    fetchProgressRequests();
  } else if (tabName === 'processed') {
    tabProcessed.hidden = false;
    fetchProcessedRequests();
  }
  document.querySelector(`.tab-btn[data-tab="${tabName}"]`).classList.add('active');
}

// --- Notification panel toggle ---
notifBtn.addEventListener('click', () => {
  notifPanel.classList.toggle('visible');
  if (notifPanel.classList.contains('visible')) {
    loadNotifications();
  }
});

notifCloseBtn.addEventListener('click', () => {
  notifPanel.classList.remove('visible');
});

// --- Logout button ---
logoutBtn.addEventListener('click', () => {
  // Add fade out animation, then logout
  document.body.style.transition = 'opacity 0.6s ease';
  document.body.style.opacity = 0;
  setTimeout(() => {
    window.location.href = 'logout.php';
  }, 600);
});

// --- Default message button ---
gotoNewRequestBtn.addEventListener('click', () => {
  switchTab('new');
});

// --- Tabs click event ---
tabButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    switchTab(btn.dataset.tab);
  });
});

// --- Medical check select ---
medicalCheck.addEventListener('change', () => {
  if (medicalCheck.value === 'yes') {
    formFields.hidden = false;
    requestMessage.textContent = '';
  } else if (medicalCheck.value === 'no') {
    formFields.hidden = true;
    requestMessage.textContent = "You can't reschedule a lab without a medical approval.";
    requestMessage.style.color = 'red';
  } else {
    formFields.hidden = true;
    requestMessage.textContent = '';
  }
});

// --- Submit new request ---
requestForm.addEventListener('submit', e => {
  e.preventDefault();

  const formData = new FormData(requestForm);

  fetch('submit_request.php', {
    method: 'POST',
    body: formData,
  })
    .then(resp => {
      // Check if response is JSON
      const contentType = resp.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) {
        throw new Error('Server did not return JSON');
      }
      return resp.json();
    })
    .then(data => {
      if (data.success) {
        requestMessage.style.color = 'green';
        requestMessage.textContent = 'Request submitted successfully.';
        requestForm.reset();
        formFields.hidden = true;
        medicalCheck.value = '';
        if (!tabProgress.hidden) fetchProgressRequests();
      } else {
        requestMessage.style.color = 'red';
        requestMessage.textContent = data.message || 'Error submitting request.';
      }
    })
    .catch(err => {
      console.error('Fetch error:', err);
      requestMessage.style.color = 'red';
      requestMessage.textContent = 'Network error.';
    });
});


// --- Fetch progress requests ---
function fetchProgressRequests() {
  progressList.textContent = 'Loading...';
  fetch('fetch_progress.php')
    .then(res => res.json())
    .then(data => {
      if (data.length === 0) {
        progressList.textContent = 'No pending requests.';
        return;
      }
      progressList.innerHTML = '';
      data.forEach(req => {
        const div = document.createElement('div');
        div.classList.add('request-item');
        div.innerHTML = `
          <p><b>Course:</b> ${req.course_id} | <b>Lab:</b> ${req.lab_id} | <b>Date:</b> ${req.date} | <b>Batch:</b> ${req.batch}</p>
          <button data-requestid="${req.request_id}" class="cancel-request-btn">Cancel Request</button>
          <hr/>
        `;
        progressList.appendChild(div);
      });

      // Add event listeners for cancel buttons
      document.querySelectorAll('.cancel-request-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          cancelRequest(btn.dataset.requestid);
        });
      });
    })
    .catch(() => {
      progressList.textContent = 'Failed to load requests.';
    });
}
function loadNotifications() {
  const notifList = document.getElementById('notif-list');
  notifList.textContent = 'Loading...';

  fetch('fetch_notification_student.php')
    .then(res => res.json())
    .then(data => {
      if (data.length === 0) {
        notifList.textContent = 'No notifications yet.';
        return;
      }

      notifList.innerHTML = '';
      data.forEach(n => {
        const div = document.createElement('div');
        div.classList.add('notif-item');
        if (!n.is_read) div.classList.add('unread');
        div.innerHTML = `
          <p>${n.message}</p>
          <small>From: ${n.sender_type}, at ${n.created_at}</small>
        `;
        notifList.appendChild(div);
      });
    })
    .catch(err => {
      console.error('Notification fetch error:', err);
      notifList.textContent = 'Failed to load notifications.';
    });
}


// --- Cancel request ---
function cancelRequest(requestId) {
  if (!confirm('Are you sure you want to cancel this request?')) return;

  fetch('cancel_request.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `request_id=${encodeURIComponent(requestId)}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('Request cancelled successfully.');
        fetchProgressRequests();
      } else {
        alert(data.message || 'Failed to cancel request.');
      }
    })
    .catch(() => alert('Network error.'));
}

// --- Fetch processed requests ---
function fetchProcessedRequests() {
  processedList.textContent = 'Loading...';
  fetch('fetch_processed.php')
    .then(res => res.json())
    .then(data => {
      if (data.length === 0) {
        processedList.textContent = 'No processed requests.';
        return;
      }
      processedList.innerHTML = '';
      data.forEach(req => {
        const div = document.createElement('div');
        div.classList.add('request-item');
        div.innerHTML = `
          <p><b>Course:</b> ${req.course_id} | <b>Lab:</b> ${req.lab_id} | <b>Date:</b> ${req.date} | <b>Batch:</b> ${req.batch} | <b>Status:</b> ${req.status} | <b>By:</b> ${req.coordinator}</p>
          <hr/>
        `;
        processedList.appendChild(div);
      });
    })
    .catch(() => {
      processedList.textContent = 'Failed to load processed requests.';
    });
}

// --- Initial setup ---
// Show default message and no tab active on page load
defaultMessage.style.display = 'block';
tabNew.hidden = true;
tabProgress.hidden = true;
tabProcessed.hidden = true;
tabButtons.forEach(btn => btn.classList.remove('active'));
formFields.hidden = true;
requestMessage.textContent = '';

