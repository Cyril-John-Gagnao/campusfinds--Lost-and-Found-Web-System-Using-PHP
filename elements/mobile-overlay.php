<!-- Mobile Navigation Overlay -->
<div id="mobileNavOverlay" class="fixed inset-0 bg-black/50 z-40 hidden sm:hidden"></div>

<!-- Mobile Navigation Drawer -->
<nav id="mobileNav" class="fixed top-0 left-0 h-full w-64 bg-gray-800 text-white -translate-x-full transition-transform duration-300 ease-in-out z-50 sm:hidden">
    <div class="p-4">
        <div class="flex items-center justify-between mb-6">
            <span class="text-lg font-bold">CampusFinds</span>
            <button id="closeMobileNav" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex flex-col space-y-2">
            <a href="../adminFolder/adminDashboard.php" class="hover:bg-gray-700 px-3 py-2 rounded">Lost Items</a>
            <a href="../claimFolder/displayClaims.php" class="hover:bg-gray-700 px-3 py-2 rounded">Claims</a>
            <a href="../userFolder/displayUser.php" class="hover:bg-gray-700 px-3 py-2 rounded">Display User</a>
            <a href="../adminFolder/displayAdmin.php" class="hover:bg-gray-700 px-3 py-2 rounded">Display Admin</a>
            <a href="../pendingItemFolder/displayUserPendingLostItem.php" class="hover:bg-gray-700 px-3 py-2 rounded flex items-center">
                Pending
                <span class="ml-1 bg-red-600 text-white text-xs rounded-full px-2 py-0.5"><?= $pendingCount ?></span>
            </a>

            <!-- Mobile Notifications Dropdown -->
            <div class="relative">
                <button id="mobileNotifBtn" class="hover:bg-gray-700 px-3 py-2 rounded flex items-center justify-between w-full text-left">
                    <div class="flex items-center">
                        <span>🔔 Notifications</span>
                        <?php if ($unreadCount > 0): ?>
                            <span id="mobileNotifCounter" class="ml-1 bg-red-600 text-white text-xs rounded-full px-2 py-0.5">(<?= $unreadCount ?>)</span>
                        <?php else: ?>
                            <span id="mobileNotifCounter" class="ml-1"></span>
                        <?php endif; ?>
                    </div>
                    <svg id="mobileNotifIcon" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="mobileNotifPanel" class="hidden mt-2 ml-4 space-y-1 bg-gray-700 rounded-lg overflow-hidden transition-all duration-300 ease-in-out">
                    <div id="mobileNotifContent" class="max-h-64 overflow-y-auto divide-y divide-gray-600"></div>
                    <div class="p-3 text-center border-t border-gray-600">
                        <a href="../notifFolder/markNotificationsRead.php" class="text-blue-400 font-semibold hover:underline text-sm">Mark all as read</a>
                    </div>
                </div>
            </div>

            <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm px-4 py-2.5 transition duration-200 ease-in-out transform hover:scale-105 focus:ring-2 focus:ring-red-300 dark:focus:ring-red-800">
                Sign Out
            </a>
        </div>
    </div>
</nav>

<script>
document.addEventListener("DOMContentLoaded", function () {
    function loadMobileNotifications() {
        fetch('../notifFolder/fetchNotifications.php') // Adjust path if needed
            .then(response => response.json())
            .then(data => {
                const notifContent = document.getElementById('mobileNotifContent');
                notifContent.innerHTML = ''; // Clear previous content

                if (data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        const notifItem = document.createElement('div');
                        notifItem.classList.add('px-4', 'py-2', 'hover:bg-gray-600', 'cursor-pointer', 'text-sm');

                        // Style unread notifications
                        if (notification.is_read == 0) {
                            notifItem.classList.add('font-semibold', 'text-white');
                        } else {
                            notifItem.classList.add('text-gray-400');
                        }

                        notifItem.textContent = notification.message;
                        notifContent.appendChild(notifItem);
                    });
                } else {
                    notifContent.innerHTML = '<div class="text-gray-400 px-4 py-2 text-sm">No notifications</div>';
                }
            })
            .catch(err => {
                console.error('Failed to fetch notifications:', err);
            });
    }

    const mobileNotifBtn = document.getElementById('mobileNotifBtn');
    if (mobileNotifBtn) {
        mobileNotifBtn.addEventListener('click', function () {
            const notifPanel = document.getElementById('mobileNotifPanel');
            const notifIcon = document.getElementById('mobileNotifIcon');

            notifPanel.classList.toggle('hidden');
            notifIcon.classList.toggle('rotate-180');

            loadMobileNotifications();
        });
    }

    // Optionally, auto-refresh every 30 seconds
    setInterval(loadMobileNotifications, 30000);
});
</script>
