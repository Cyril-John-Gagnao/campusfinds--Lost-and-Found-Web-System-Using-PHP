<?php
session_start();
include_once 'dbconn.php';
$slaveConn = getSlaveConnection();

$stmt = $slaveConn->prepare("SELECT * FROM lost_items WHERE status = 'approved' ORDER BY dateFound DESC, itemName ASC");
$stmt->execute();
$result = $stmt->fetchAll();

// Display messages
$loginMessage = "";
$accessDeniedMessage = "";

if (isset($_SESSION['login_message'])) {
    $loginMessage = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

if (isset($_SESSION['access_denied_message'])) {
    $accessDeniedMessage = $_SESSION['access_denied_message'];
    unset($_SESSION['access_denied_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page - Lost and Found System</title>

    <!-- Tailwind configuration must come before the CDN script so it is picked up -->
    <script>
      tailwind.config = {
        theme: {
          extend: {
            screens: {
              xs: '475px',
            },
          },
        },
      }
    </script>
    <!-- Enable the official line‑clamp plugin so we can keep using the class name -->
    <script src="https://cdn.tailwindcss.com?plugins=line-clamp"></script>

    <!-- Flowbite (after Tailwind so it can access classes) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</head>
<body class="bg-white font-sans">

<!-- Mobile Navigation Overlay -->
<div id="mobileNavOverlay" class="fixed inset-0 bg-black/50 z-40 hidden sm:hidden"></div>

<!-- Mobile Navigation Drawer -->
<nav id="mobileNav" class="fixed top-0 left-0 h-full w-64 bg-gray-800 text-white -translate-x-full transition-transform duration-300 ease-in-out z-50 sm:hidden">
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
            <a href="index.php" class="hover:bg-gray-700 px-3 py-2 rounded">Lost Items</a>
            <a href="indexClaim.php" class="hover:bg-gray-700 px-3 py-2 rounded">Claims</a>

            <!-- Mobile Login Dropdown -->
            <div class="relative">
                <button id="mobileLoginToggle" class="hover:bg-blue-700 px-3 py-2 rounded flex items-center justify-between w-full text-left bg-blue-600">
                    <div class="flex items-center">
                        <img src="images/log in_icon.png" alt="Login Icon" class="w-5 h-5 mr-2 rounded-full">
                        <span>Login</span>
                    </div>
                    <svg id="mobileLoginArrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="mobileLoginDropdown" class="hidden mt-2 ml-4 space-y-1 bg-gray-700 rounded-lg overflow-hidden transition-all duration-300 ease-in-out">
                    <a href="userFolder/userLogin.php" class="block hover:bg-gray-600 px-4 py-3 text-sm transition-colors duration-200">User Login</a>
                    <a href="adminFolder/adminLogin.php" class="block hover:bg-gray-600 px-4 py-3 text-sm transition-colors duration-200">Admin Login</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<?php
include 'elements/navbar-index.php';
?>

<!-- INLINE MESSAGE DISPLAY -->
<?php if (!empty($loginMessage)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-8 my-4">
        <strong class="font-bold">Login Successful: </strong>
        <span class="block sm:inline"><?= htmlspecialchars($loginMessage) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($accessDeniedMessage)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-8 my-4">
        <strong class="font-bold">Access Denied: </strong>
        <span class="block sm:inline"><?= htmlspecialchars($accessDeniedMessage) ?></span>
    </div>
<?php endif; ?>

<!-- HEADER SECTION  -->
<div class="relative bg-blue-200 bg-cover bg-center min-h-[400px] sm:min-h-[450px] md:min-h-[500px] lg:min-h-[550px]" style="background-image: url('images/bg10.jpg');">
    <!-- Gray overlay -->
    <div class="absolute inset-0 bg-gray-900/50"></div>

    <!-- Content on top of overlay -->
    <div class="relative py-12 sm:py-16 md:py-20 lg:py-24 px-4 sm:px-6 md:px-8 text-center">
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500 drop-shadow-lg leading-tight">Lost Items</h1>
        <p class="mt-2 sm:mt-3 text-white text-xs sm:text-sm md:text-base font-medium max-w-md mx-auto">Not all are lost, anything can be found.</p>

        <!-- Search Bar -->
        <div class="mt-6 sm:mt-8 md:mt-10 flex justify-center w-full px-2 sm:px-4">
            <div class="relative w-full max-w-xs sm:max-w-md md:max-w-lg lg:max-w-xl">
                <input type="text" id="searchInput" placeholder="Search Items" oninput="debouncedSearch('searchLostForIndex/searchAction.php', 'fullUserListContainer', 'results')" class="w-full px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 text-sm sm:text-base border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-12 sm:pr-14" />
                <div class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 bg-blue-600 h-7 w-7 sm:h-8 sm:w-8 md:h-9 md:w-9 flex items-center justify-center rounded-full">
                    <img src="images/search_icon.png" alt="Search Icon" class="h-3 w-3 sm:h-4 sm:w-4 md:h-5 md:w-5" />
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Results Table Container  -->
<div id="resultsTableContainer" class="px-2 sm:px-4 md:px-6 lg:px-8 py-4 hidden">
    <?php include 'searchLostForIndex/tableHeader.php'; ?>
</div>

<!-- Display Results -->
<div id="results" class="mt-4 sm:mt-6 overflow-x-auto px-2 sm:px-4 md:px-6 lg:px-8"></div>

<!-- Approved Lost Items Display -->
<div id="fullUserListContainer" class="bg-white px-2 sm:px-4 md:px-6 lg:px-8 py-8 sm:py-10 md:py-12">
    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-center mb-8 sm:mb-10 md:mb-12 text-gray-800">Recently Found Items</h2>

    <div class="grid grid-cols-1 xs:grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4 md:gap-5 lg:gap-6">
        <?php if (!empty($result)): ?>
            <?php foreach ($result as $res): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 hover:scale-105 border border-gray-100">
                    <div class="h-32 sm:h-36 md:h-40 lg:h-44 xl:h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                        <?php
                        $imageRelativePath = str_replace(['../', './'], '', $res['imagePath']);
                        $imageFilePath = __DIR__ . '/' . $imageRelativePath;
                        $imageWebPath = htmlspecialchars($imageRelativePath);
                        ?>
                        <?php if (!empty($res['imagePath']) && file_exists($imageFilePath)): ?>
                            <img src="<?= $imageWebPath ?>" alt="Lost Item Image" class="max-h-full max-w-full object-contain hover:scale-110 transition-transform duration-300 cursor-pointer" onclick="openImageModal('<?= $imageWebPath ?>')">
                        <?php else: ?>
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-xs sm:text-sm">No Image</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-3 sm:p-4 bg-gray-800 text-white">
                        <h3 class="text-sm sm:text-base md:text-lg font-bold mb-1 sm:mb-2 line-clamp-2 leading-tight"><?= htmlspecialchars($res['itemName']) ?></h3>
                        <p class="text-xs sm:text-sm text-gray-300 mb-2 sm:mb-3 line-clamp-2 leading-relaxed"><?= htmlspecialchars($res['description']) ?></p>
                        <div class="space-y-1 sm:space-y-2 text-xs sm:text-sm">
                            <div>
                                <p class="text-gray-400 font-medium">Location Found:</p>
                                <p class="text-gray-100 truncate"><?= htmlspecialchars($res['locationFound']) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-400 font-medium">Pick-Up Location:</p>
                                <p class="text-gray-100 truncate"><?= htmlspecialchars($res['pickUpLocation']) ?></p>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 sm:gap-2">
                                <div class="flex-1">
                                    <p class="text-gray-400 font-medium">Date Found:</p>
                                    <p class="text-gray-100 text-xs sm:text-sm"><?= htmlspecialchars($res['dateFound']) ?></p>
                                </div>
                                <div class="flex-1">
                                    <p class="text-gray-400 font-medium">Returnee:</p>
                                    <p class="text-gray-100 text-xs sm:text-sm truncate"><?= htmlspecialchars($res['returneeName']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-12 sm:py-16">
                <div class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-16 h-16 sm:w-20 sm:h-20 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg sm:text-xl font-semibold mb-2">No Recently Found Items</h3>
                    <p class="text-sm sm:text-base text-gray-400 max-w-md text-center px-4">There are currently no recently found items to display. Check back later for new items.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include_once 'elements/modal-container.php';
?>

<script>
    // Mobile login dropdown
    const mobileLoginToggle = document.getElementById('mobileLoginToggle');
    const mobileLoginDropdown = document.getElementById('mobileLoginDropdown');
    const mobileLoginArrow = document.getElementById('mobileLoginArrow');

    mobileLoginToggle?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      mobileLoginDropdown.classList.toggle('hidden');
      mobileLoginArrow.classList.toggle('rotate-180');
    });
</script>

<script src="elements/image-modal.js"></script>
<script src="elements/mobile-toggle-overlay.js"></script>
<?php include 'elements/footer.php'; ?>
<script src="elements/index.js"></script>
<script src="search.js"></script>
</body>
</html>
