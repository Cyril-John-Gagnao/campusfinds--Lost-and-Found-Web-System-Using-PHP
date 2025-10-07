
document.addEventListener('DOMContentLoaded', () => {
  const notifCountElem = document.getElementById('notifCount');
  const notifList = document.getElementById('notifList');
  const pendingCountElem = document.getElementById('pendingCount');//id sa navbar
  const notifToggle = document.getElementById('notifToggle');
  const notifDropdown = document.getElementById('notifDropdown');

  if (!notifCountElem || !notifList || !notifToggle || !notifDropdown) {
    console.warn('Notification elements missing from DOM');
    return;
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

    function deleteNotification(id) {
    fetch('../notifFolder/deleteNotification.php?id=' + id)
      .then(response => {
        if (response.ok) {
          const notifElement = document.getElementById('notif-' + id);
          if (notifElement) {
            notifElement.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
            notifElement.style.opacity = '0';
            notifElement.style.transform = 'translateX(100%)';
            setTimeout(() => {
              notifElement.remove();
              loadNotifications();
            }, 300);
          } else {
            loadNotifications();
          }
        } else {
          // Optional: silently fail or still reload notifications
          loadNotifications();
        }
      })
      .catch(() => {
        // Optional: silently fail or still reload notifications
        loadNotifications();
      });
  }


  function loadNotifications() {
    fetch('../notifFolder/fetchNotif.php?receiver_role=admin')
      .then(res => res.json())
      .then(data => {
        if (pendingCountElem) pendingCountElem.textContent = data.pendingCount;
        notifCountElem.textContent = data.unreadCount > 0 ? `(${data.unreadCount})` : '';

        if (data.notifications.length === 0) {
          notifList.innerHTML = '<p class="p-4 text-center text-gray-500">No notifications</p>';
        } else {
          notifList.innerHTML = data.notifications.map(notif => {
            const bg = notif.is_read == 0 ? '#eef' : '#fff';
            const truncatedMessage = notif.message.length > 50 ? notif.message.substring(0, 50) + '...' : notif.message;
            return `
              <div id="notif-${notif.id}" style="padding:15px; border-bottom:1px solid #eee; background:${bg}; color: #000; position: relative;">
                <small style="color:#555;">${notif.created_at}</small><br>
                <div>${notif.message}</div>
                <button class="delete-notif-btn" 
                        data-id="${notif.id}" 
                        data-message="${escapeHtml(notif.message)}"
                        style="position: absolute; top: 10px; right: 10px; color: red; cursor: pointer; background: none; border: none; font-size: 16px;">
                  ❌
                </button>
              </div>`;
          }).join('');
        }

        document.querySelectorAll('.delete-notif-btn').forEach(btn => {
        btn.onclick = (e) => {
          e.preventDefault();
          e.stopPropagation();

          const id = btn.getAttribute('data-id');
          deleteNotification(id);
        };
      });
      })
      .catch(err => console.error('Error fetching notifications:', err));
  }

  // Notification toggle logic
  notifToggle.addEventListener('click', () => {
    notifDropdown.classList.toggle('hidden');
    if (!notifDropdown.classList.contains('hidden')) {
      loadNotifications();
    }
  });

  // Close dropdown when clicking outside
  window.addEventListener('click', (e) => {
    if (!notifToggle.contains(e.target) && !notifDropdown.contains(e.target)) {
      notifDropdown.classList.add('hidden');
    }
  });

  // Mark all as read confirmation
  const markAllLink = document.querySelector('a[href="../notifFolder/markNotificationsRead.php"]');
  if (markAllLink) {
    const newMarkAllLink = markAllLink.cloneNode(true);
    markAllLink.parentNode.replaceChild(newMarkAllLink, markAllLink);

    newMarkAllLink.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();

      if (window.formSystem) {
        window.formSystem.showModal({
          title: 'Mark All as Read',
          message: 'Are you sure you want to mark all notifications as read?',
          icon: '📖',
          confirmText: 'Mark All Read',
          cancelText: 'Cancel',
          confirmClass: 'bg-blue-600 hover:bg-blue-700',
          onConfirm: () => {
            window.location.href = newMarkAllLink.href;
          }
        });
      } else {
        if (confirm('📖 Are you sure you want to mark all notifications as read?')) {
          window.location.href = newMarkAllLink.href;
        }
      }
    });
  }

  // Initial load
  loadNotifications();

  // Auto refresh every 2 seconds (optional, reduce to 15s if needed)
  setInterval(loadNotifications, 5000);
});

