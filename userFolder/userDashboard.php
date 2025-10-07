<?php
include_once __DIR__ . '/../dbconn.php'; 
include_once '../authentication.php';
requireLogin();

$slaveConn = getSlaveConnection();

$stmt = $slaveConn->prepare("SELECT * FROM lost_items WHERE status = 'approved' ORDER BY dateFound DESC, itemName ASC");
$stmt->execute();
$result = $stmt->fetchAll();

if (isset($_SESSION['claim_success'])) {
    $msg = htmlspecialchars($_SESSION['claim_success']);
    unset($_SESSION['claim_success']);
    echo "
    <div id='successPopup' class='fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-green-100 text-green-900 p-5 border border-green-200 rounded-lg shadow-lg z-[10000] min-w-[300px] text-center'>
        <span>$msg</span>
        <br><br>
        <button onclick=\"document.getElementById('successPopup').style.display='none'\" 
            class='bg-green-900 text-white px-4 py-2 rounded cursor-pointer'>
            Close
        </button>
    </div>
    ";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Lost Items</title>
    <!-- Tailwind with line‑clamp plugin -->
    <script src="https://cdn.tailwindcss.com?plugins=line-clamp"></script>
</head>
<body class="bg-gray-50 text-gray-900">

<!-- Mobile Navigation Overlay -->
<div id="mobileNavOverlay" class="fixed inset-0 bg-black/50 z-40 hidden sm:hidden"></div>
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
            <a href="../userFolder/userDashboard.php" class="hover:bg-gray-700 px-3 py-2 rounded">Lost Items</a>
            <a href="../claimFolder/displayClaimsUser.php" class="hover:bg-gray-700 px-3 py-2 rounded">Claims</a>
            <a href="../logout.php"
               class="bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm px-4 py-2.5 transition duration-200 ease-in-out transform hover:scale-105 focus:ring-2 focus:ring-red-300 dark:focus:ring-red-800">
                Sign Out
            </a>
        </div>
    </div>
</nav>

<?php include '../elements/navbar-User.php'; ?>
<?php include_once '../confirmation.php'; ?>

<!-- HEADER SECTION -->
<div class="relative bg-cover bg-center min-h-[400px] sm:min-h-[450px] md:min-h-[500px] lg:min-h-[550px] bg-[url('../images/bg10.jpg')]">
    <!-- Gray overlay -->
    <div class="absolute inset-0 bg-gray-900/50"></div>

    <!-- Content -->
    <div class="relative z-10 py-12 sm:py-16 md:py-20 lg:py-24 px-4 sm:px-6 md:px-8 text-center">
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500 drop-shadow-lg leading-tight">
            Lost Items
        </h1>
        <p class="mt-2 sm:mt-3 text-white text-xs sm:text-sm md:text-base font-medium max-w-md mx-auto">
            Browse lost items and find what you're looking for.
        </p>

        <!-- Search Bar -->
        <div class="mt-6 sm:mt-8 md:mt-10 flex justify-center w-full px-2 sm:px-4">
            <div class="relative w-full max-w-xs sm:max-w-md md:max-w-lg lg:max-w-2xl">
                <input type="text" id="searchInput"
                    placeholder="Search lost items..."
                    oninput="debouncedSearch('../searchLostForUser/searchAction.php', 'fullUserListContainer', 'results')"
                    class="w-full px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 text-sm sm:text-base border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-12 sm:pr-14">
                <div class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 bg-blue-600 h-7 w-7 sm:h-8 sm:w-8 md:h-9 md:w-9 flex items-center justify-center rounded-full">
                    <img src="../images/search_icon.png" alt="Search Icon" class="h-3 w-3 sm:h-4 sm:w-4 md:h-5 md:w-5">
                </div>
            </div>
        </div>

        <!-- Report Lost Item Button -->
        <div class="text-center mt-6 sm:mt-8 mb-2">
            <form action="../pendingItemFolder/userSubmitLostItemForm.php" method="POST">
                <input type="hidden" name="from_dashboard" value="1">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm sm:text-base px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 rounded-full shadow-md transition duration-200 transform hover:scale-105">
                    Report Lost Item
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Search Results -->
<div id="results" class="px-2 sm:px-4 md:px-6 lg:px-8 mt-4 sm:mt-6"></div>

<!-- Main Content -->
<div id="fullUserListContainer" class="bg-white px-2 sm:px-4 md:px-6 lg:px-8 py-8 sm:py-10 md:py-12">
    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-center mb-8 sm:mb-10 md:mb-12 text-gray-800">
        Lost Items
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4 md:gap-5 lg:gap-6">
        <?php if (!empty($result)): ?>
            <?php foreach ($result as $res): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105 border border-gray-100">
                    <!-- Image -->
                    <div class="h-32 sm:h-36 md:h-40 lg:h-44 xl:h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                        <?php
                            $imgPath = str_replace(['../', './'], '', $res['imagePath']);
                            $imgFullPath = __DIR__ . '/../' . $imgPath;
                        ?>
                        <?php if (!empty($res['imagePath']) && file_exists($imgFullPath)): ?>
                            <img src="../<?= htmlspecialchars($imgPath) ?>" alt="Lost Item Image" 
                                 class="object-cover h-full w-full hover:scale-110 transition-transform duration-300 cursor-pointer"
                                 onclick="openImageModal('../<?= htmlspecialchars($imgPath) ?>')">
                        <?php else: ?>
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-xs sm:text-sm">No Image</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Card Content -->
                    <div class="p-3 sm:p-4 bg-gray-800 text-white">
                        <h3 class="text-sm sm:text-base md:text-lg font-bold mb-1 sm:mb-2 line-clamp-2 leading-tight">
                            <?= htmlspecialchars($res['itemName']) ?>
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-300 mb-2 sm:mb-3 line-clamp-2 leading-relaxed">
                            <?= htmlspecialchars($res['description']) ?>
                        </p>
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
                        <form action="../notifFolder/userClaim.php" method="POST" 
                            class="mt-3 sm:mt-4 text-center"
                            data-confirm="true"
                            data-confirm-message="Are you sure you want to claim this item?."
                            data-confirm-title="Claim Item"
                            data-confirm-icon="✅"
                            data-confirm-text="Claim"
                            data-confirm-cancel="Cancel"
                            data-confirm-class="bg-green-600 hover:bg-green-700"
                            class="inline">
                                <input type="hidden" name="lostItem_ID" value="<?= htmlspecialchars($res['lostItem_ID']) ?>">
                                <input type="hidden" name="claimerEmail" value="<?= htmlspecialchars($_SESSION['email']) ?>">
                                <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm font-medium px-3 sm:px-4 py-2 sm:py-2.5 rounded-md transition duration-200 transform hover:scale-105 focus:ring-2 focus:ring-green-300">
                                Claim Item
                                </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-12 sm:py-16">
                <div class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-16 h-16 sm:w-20 sm:h-20 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg sm:text-xl font-semibold mb-2">No Lost Items Found</h3>
                    <p class="text-sm sm:text-base text-gray-400 max-w-md mx-auto px-4">
                        There are currently no approved lost items to display. Check back later or report a lost item if you've found something.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-[10000] hidden">
    <div class="relative">
        <img id="modalImage" src="" class="max-h-[90vh] max-w-[90vw] rounded shadow-2xl" alt="Full view">
        <button onclick="closeImageModal()" class="absolute top-2 right-2 bg-black/60 hover:bg-black/80 text-white rounded-full p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

<?php include '../elements/footer-User-Admin.php'; ?>

<!-- Scripts -->
<script>
function openImageModal(src) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modalImg.src = src;
    modal.classList.remove('hidden');
}
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modal.classList.add('hidden');
    modalImg.src = '';
}
// Close modal when clicking outside the image
window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('imageModal')) {
        closeImageModal();
    }
});
</script>
<script src="../search.js"></script>
<script src="../elements/index.js"></script>

</body>
</html>