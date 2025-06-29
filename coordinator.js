document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.tab-btn');
  const content = document.getElementById('tab-content');
  const checkBtn = document.getElementById('check-request-btn');
  const notifBtn = document.getElementById('notif-btn');
  const notifPanel = document.getElementById('notif-panel');
  const notifList = document.getElementById('notif-list');
  const notifCloseBtn = document.getElementById('notif-close-btn');
  const promptSection = document.getElementById('prompt-section');
  const logoutBtn = document.getElementById('logout-btn');

  // Track current active tab
  let activeTab = null;

  // Show blinking message initially (prompt section visible, content hidden)
  function showPrompt() {
    promptSection.style.display = 'block';
    content.style.display = 'none';
  }

  // Show tab content area (hide prompt)
  function showContent() {
    promptSection.style.display = 'none';
    content.style.display = 'block';
  }

  // Activate tab by name, load content via fetch
  function activateTab(tabName) {
    tabs.forEach(t => t.classList.remove('active'));
    const tab = Array.from(tabs).find(t => t.dataset.tab === tabName);
    if (tab) tab.classList.add('active');

    activeTab = tabName;
    showContent();

    fetch(`tabs/${tabName}.php`)
      .then(res => {
        if (!res.ok) throw new Error("Failed to load tab content");
        return res.text();
      })
      .then(html => {
        content.innerHTML = html;
        attachActionHandlers(); // Reattach approve/reject button handlers
      })
      .catch(err => {
        content.innerHTML = `<p style="color:red;">Error loading content: ${err.message}</p>`;
      });
  }

  // Load notifications from server and display in panel
  function loadNotifications() {
    fetch('fetch_notifications.php')
      .then(res => res.json())
      .then(data => {
        notifList.innerHTML = '';
        if (!data.length) {
          notifList.innerHTML = '<li>No notifications</li>';
          return;
        }
        data.forEach(notif => {
          const li = document.createElement('li');
          li.textContent = `${notif.message} (${new Date(notif.created_at).toLocaleString()})`;
          li.dataset.id = notif.id;
          li.dataset.type = notif.type || 'request'; // assume type property for routing
          if (!notif.is_read) li.classList.add('unread');
          li.tabIndex = 0; // keyboard accessible
          li.addEventListener('click', () => {
            markNotificationRead(notif.id);
            // Route click based on notification type
            if (notif.type === 'review') {
              activateTab('review');
            } else {
              activateTab('request');
            }
            hideNotifications();
          });
          notifList.appendChild(li);
        });
      })
      .catch(() => {
        notifList.innerHTML = '<li>Error loading notifications</li>';
      });
  }

  // Show notification panel
  function showNotifications() {
    notifPanel.classList.add('visible');
    notifPanel.setAttribute('aria-hidden', 'false');
    loadNotifications();
  }

  // Hide notification panel
  function hideNotifications() {
    notifPanel.classList.remove('visible');
    notifPanel.setAttribute('aria-hidden', 'true');
  }

  // Toggle notification panel
  function toggleNotifications() {
    if (notifPanel.classList.contains('visible')) {
      hideNotifications();
    } else {
      showNotifications();
    }
  }

  // Mark notification as read (POST)
  function markNotificationRead(id) {
    fetch('mark_notifications_read.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `notification_id=${encodeURIComponent(id)}`
    }).then(() => {
      // Refresh notifications to update styles
      loadNotifications();
    });
  }

  // Attach handlers for approve/reject buttons (called after loading tab content)
  function attachActionHandlers() {
    const approveBtns = content.querySelectorAll('button.approve-btn');
    const rejectBtns = content.querySelectorAll('button.reject-btn');

    approveBtns.forEach(btn => {
      btn.addEventListener('click', () => handleRequestAction(btn.dataset.requestId, 'approve'));
    });

    rejectBtns.forEach(btn => {
      btn.addEventListener('click', () => handleRequestAction(btn.dataset.requestId, 'reject'));
    });
  }

  // Handle approve/reject via AJAX
  function handleRequestAction(requestId, action) {
    if (!requestId || !action) return;
    if (!confirm(`Are you sure you want to ${action} this request?`)) return;

    fetch('handle_request.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `request_id=${encodeURIComponent(requestId)}&action=${encodeURIComponent(action)}`
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.message);
          // Reload Request tab to remove approved/rejected request
          if (activeTab === 'request') activateTab('request');
          // Also reload Approved/Rej tab to show updated list
          // (Optional: could preload or refresh that tab too)
          activateTab('Approved/Rejected');
        } else {
          alert(`Failed: ${data.error || 'Unknown error'}`);
        }
      })
      .catch(() => {
        alert('Server error. Try again later.');
      });
  }

  // Event listeners

  // Tab buttons click
  tabs.forEach(tabBtn => {
    tabBtn.addEventListener('click', () => {
      if (tabBtn.classList.contains('active')) return;
      activateTab(tabBtn.dataset.tab);
    });
  });

  // Check Request button
  checkBtn.addEventListener('click', () => {
    activateTab('request');
  });

  // Notification button
  notifBtn.addEventListener('click', toggleNotifications);

  // Close notif panel
  notifCloseBtn.addEventListener('click', hideNotifications);

  // Logout button
  logoutBtn.addEventListener('click', () => {
    window.location.href = 'logout.php';
  });

  // Initially show blinking prompt
  showPrompt();
});
