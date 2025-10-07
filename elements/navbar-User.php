<!-- Flowbite (keep at the top of your page) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>

<!-- Mobile Nav Overlay -->
<div id="mobileNavOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden sm:hidden"></div>

<!-- Mobile Navigation Drawer -->
<nav id="mobileNav" class="fixed top-0 left-0 h-full w-64 bg-gray-800 text-white transform -translate-x-full transition-transform duration-300 ease-in-out z-50 sm:hidden">
    <div class="p-4">
        <div class="flex items-center justify-between mb-6">
            <span class="text-lg font-bold">Campus Finds</span>
            <button id="closeMobileNav" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex flex-col space-y-2">
            <a href="../userFolder/userDashboard.php" class="hover:bg-gray-700 px-3 py-2 rounded">Lost Items</a>
            <a href="../claimFolder/displayClaimsUser.php" class="hover:bg-gray-700 px-3 py-2 rounded">Claims</a>
            <a href="../logout.php"
               class="bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm px-4 py-2.5 transition duration-200 ease-in-out transform hover:scale-105 focus:ring-2 focus:ring-red-300 dark:focus:ring-red-800">
                Sign Out
            </a>
        </div>
    </div>
</nav>

<!-- MAIN NAVIGATION BAR -->
<header class="bg-gray-800 text-white px-4 sm:px-6 py-4">
    <div class="flex items-center justify-between">
        <!-- Hamburger button - visible only on mobile -->
        <button id="mobileMenuButton" class="sm:hidden">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Logo and Title -->
        <div class="flex items-center space-x-3 mx-auto sm:mx-0">
            <img src="../images/logo_campus_2.png" alt="Logo" class="h-8 w-8 sm:h-10 sm:w-10 object-contain" />
            <span class="text-lg sm:text-xl font-bold">Campus Finds</span>
        </div>

        <!-- Empty div to keep title centered on mobile -->
        <div class="w-6 sm:hidden"></div>

        <!-- Desktop Navigation -->
        <nav class="hidden sm:flex items-center space-x-4 text-sm sm:text-base mr-4">
            <a href="../userFolder/userDashboard.php"
               class="px-3 py-2 rounded transition duration-200 ease-in-out hover:bg-gray-700 hover:text-white font-medium hover:scale-105
               <?php echo (basename($_SERVER['PHP_SELF']) == 'userDashboard.php') ? 'bg-gray-500' : ''; ?>">
               Lost Items
            </a>
            <a href="../claimFolder/displayClaimsUser.php"
               class="px-3 py-2 rounded transition duration-200 ease-in-out hover:bg-gray-700 hover:text-white font-medium hover:scale-105
               <?php echo (basename($_SERVER['PHP_SELF']) == 'displayClaimsUser.php') ? 'bg-gray-500' : ''; ?>">
               Claims
            </a>
            <a href="../logout.php"
               class="text-white bg-red-500 hover:bg-red-700 focus:ring-2 focus:outline-none focus:ring-red-300
                      font-medium rounded-lg text-sm px-4 py-2.5 transition duration-200 ease-in-out transform hover:scale-105
                      dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                Sign Out
            </a>
        </nav>
    </div>
</header>

<!-- Toggle Logic -->
<script>
    // Mobile menu toggle functionality
    document.getElementById('mobileMenuButton').addEventListener('click', function () {
        const mobileNav = document.getElementById('mobileNav');
        const overlay = document.getElementById('mobileNavOverlay');

        mobileNav.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    });

    // Function to close mobile nav
    function closeMobileNav() {
        const mobileNav = document.getElementById('mobileNav');
        const overlay = document.getElementById('mobileNavOverlay');

        mobileNav.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Event listeners
    document.getElementById('closeMobileNav').addEventListener('click', closeMobileNav);
    document.getElementById('mobileNavOverlay').addEventListener('click', closeMobileNav);
</script>
