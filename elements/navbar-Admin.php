<!-- NAVIGATION BAR -->
<header class="bg-gray-800 text-white px-4 sm:px-6 py-4">
    <div class="flex items-center justify-between flex-wrap">
        <!-- Hamburger button - visible only on mobile -->
        <button id="mobileMenuButton" class="sm:hidden mr-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Logo and Title - centered on mobile, left-aligned on larger screens -->
        <div class="flex items-center space-x-3 flex-shrink-0 mx-auto sm:mx-0">
            <img src="../images/logo_campus_2.png" alt="Logo" class="h-8 w-8 sm:h-10 sm:w-10 object-contain" />
            <span class="text-lg sm:text-xl font-bold">CampusFinds - Admin</span>
        </div>

        <!-- Desktop Navigation - hidden on mobile, shows on sm breakpoint -->
        <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
        <nav class="hidden sm:flex sm:items-center sm:w-auto w-full mt-4 sm:mt-0">
            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 md:space-x-4 text-sm sm:text-base">
                <a href="../adminFolder/adminDashboard.php"
                   class="hover:bg-gray-700 px-3 py-2 rounded transition duration-200 ease-in-out hover:scale-105 <?php if ($currentPage === 'adminDashboard.php') echo 'bg-gray-500 font-semibold'; ?>">
                    Lost Items
                </a>

                <a href="../claimFolder/displayClaims.php"
                   class="hover:bg-gray-700 px-3 py-2 rounded transition duration-200 ease-in-out hover:scale-105 <?php if ($currentPage === 'displayClaims.php') echo 'bg-gray-500 font-semibold'; ?>">
                    Claims
                </a>

                <a href="../userFolder/displayUser.php"
                   class="hover:bg-gray-700 px-3 py-2 rounded transition duration-200 ease-in-out hover:scale-105 <?php if ($currentPage === 'displayUser.php') echo 'bg-gray-500 font-semibold'; ?>">
                    Display User
                </a>

                <a href="../adminFolder/displayAdmin.php"
                   class="hover:bg-gray-700 px-3 py-2 rounded transition duration-200 ease-in-out hover:scale-105 <?php if ($currentPage === 'displayAdmin.php') echo 'bg-gray-500 font-semibold'; ?>">
                    Display Admin
                </a>

                <a href="../pendingItemFolder/displayUserPendingLostItem.php"
                   class="hover:bg-gray-700 px-3 py-2 rounded flex items-center transition duration-200 ease-in-out hover:scale-105 <?php if ($currentPage === 'displayUserPendingLostItem.php') echo 'bg-gray-500 font-semibold'; ?>">
                    Pending
                    <span id="pendingCount" class="ml-1 bg-red-600 text-white text-xs rounded-full px-2 py-0.5"><?= $pendingCount ?></span>
                </a>

                <div class="relative">
                    <button id="notifToggle" class="hover:bg-gray-700 px-3 py-2 rounded flex items-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                        🔔 
                        <!--<span class="hidden md:inline">Notifications</span>-->
                        <?php if ($unreadCount > 0): ?>
                            <span id="notifCount" class="ml-1 bg-red-600 text-white text-xs rounded-full px-2 py-0.5">(<?= $unreadCount ?>)</span>
                        <?php else: ?>
                            <span id="notifCount" class="ml-1"></span>
                        <?php endif; ?>
                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 max-h-72 overflow-y-auto rounded-lg shadow-lg bg-white text-black border border-gray-200 z-50">
                        <div id="notifList" class="divide-y divide-gray-200"></div>
                        <div class="p-3 text-center border-t border-gray-200">
                            <a href="../notifFolder/markNotificationsRead.php" class="text-blue-600 font-semibold hover:underline">Mark all as read</a>
                        </div>
                    </div>
                </div>

                <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm px-4 py-2 transition duration-200 ease-in-out transform hover:scale-105 focus:ring-2 focus:ring-red-300 dark:focus:ring-red-800">
                    Sign Out
                </a>
            </div>
        </nav>
    </div>
</header>