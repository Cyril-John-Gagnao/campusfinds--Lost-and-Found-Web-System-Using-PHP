<!-- NAVIGATION BAR -->
<header class="bg-gray-800 text-white px-4 sm:px-6 py-4">
    <div class="flex items-center justify-between">
        <!-- Hamburger button - visible only on mobile, positioned on the left -->
        <button id="mobileMenuButton" class="sm:hidden">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Logo and Title - centered on mobile, left-aligned on larger screens -->
        <div class="flex items-center space-x-3 mx-auto sm:mx-0">
            <img src="images/logo_campus_2.png" alt="Logo" class="h-8 w-8 sm:h-10 sm:w-10 object-contain" />
            <span class="text-lg sm:text-xl font-bold">Campus Finds</span>
        </div>

        <!-- Empty div to maintain centering on mobile -->
        <div class="w-6 sm:hidden"></div>

        <!-- Desktop Navigation - hidden on mobile -->
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="hidden sm:flex items-center space-x-4 text-sm sm:text-base">
    <a href="index.php"
       class="px-3 py-2 rounded transition duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:scale-105 <?php if ($currentPage === 'index.php') echo 'bg-gray-500 font-semibold'; ?>">
        Lost Items
    </a>
    <a href="indexClaim.php"
       class="px-3 py-2 rounded transition duration-200 ease-in-out hover:bg-gray-700 hover:text-white hover:scale-105 <?php if ($currentPage === 'indexClaim.php') echo 'bg-gray-500 font-semibold'; ?>">
        Claims
    </a>

            <!-- Desktop LOGIN DROPDOWN -->
            <div class="relative" id="loginDropdownContainer">
                <!-- Toggle Button -->
                <button id="loginDropdownBtn" data-dropdown-toggle="loginDropdownMenu" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-2 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2.5 inline-flex items-center gap-2 transition duration-200 ease-in-out hover:scale-105">
                    <img src="images/log in_icon.png" alt="Icon" class="w-6 h-6 mr-3 rounded-full">
                    <span class="text-sm">Login</span>
                    <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div id="loginDropdownMenu" class="hidden absolute right-0 mt-2 w-44 z-50 bg-white divide-y divide-gray-100 rounded-lg shadow-lg dark:bg-gray-700 dark:divide-gray-600">
                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="loginDropdownBtn">
                        <li><a href="userFolder/userLogin.php" class="block px-4 py-2 transition duration-200 ease-in-out rounded hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white hover:scale-105">User Login</a></li>
                        <li><a href="adminFolder/adminLogin.php" class="block px-4 py-2 transition duration-200 ease-in-out rounded hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white hover:scale-105">Admin Login</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>