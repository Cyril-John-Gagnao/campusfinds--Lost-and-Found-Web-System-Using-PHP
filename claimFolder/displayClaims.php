<?php
include_once __DIR__ . '/../dbconn.php';
include_once '../authentication.php';
requireAdminLogin();

$slaveConn = getSlaveConnection();

$stmt = $slaveConn->prepare("SELECT * FROM claims ORDER BY claimDate DESC, itemName ASC");
$stmt->execute();
$result = $stmt->fetchAll();

// Notifications & counts for navbar (same as your lost items page)
$masterConn = getMasterConnection();
$stmtNotif = $masterConn->prepare("SELECT * FROM notifications WHERE receiver_role = 'admin' ORDER BY created_at DESC");
$stmtNotif->execute();
$notifications = $stmtNotif->fetchAll(PDO::FETCH_ASSOC);

$stmtCount = $masterConn->prepare("SELECT COUNT(*) FROM notifications WHERE receiver_role = 'admin' AND is_read = 0");
$stmtCount->execute();
$unreadCount = $stmtCount->fetchColumn();

$stmtPending = $masterConn->prepare("SELECT COUNT(*) FROM lost_items WHERE status = 'pending'");
$stmtPending->execute();
$pendingCount = $stmtPending->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Claims - Lost and Found System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

<?php
include '../elements/mobile-overlay.php';
?>

<?php
include '../elements/navbar-Admin.php';
?>

<?php include_once '../confirmation.php';?>

<!-- HEADER with Search Bar -->
<section class="relative bg-blue-200 bg-cover bg-center min-h-[200px] sm:min-h-[300px] md:min-h-[400px] lg:min-h-[450px]"
         style="background-image: url('../images/bg8.jpg');">
  <!-- Semi-transparent overlay -->
  <div class="absolute inset-0 bg-gray-900/50"></div>

  <!-- Content -->
  <div class="relative py-12 sm:py-16 md:py-20 lg:py-24 px-4 sm:px-6 md:px-8 text-center">
    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500 drop-shadow-lg leading-tight">
      Claims
    </h1>
    <p class="mt-2 sm:mt-3 text-white text-xs sm:text-sm md:text-base font-medium max-w-md mx-auto">
      Manage all claim items here.
    </p>

    <div class="mt-6 sm:mt-10 flex justify-center w-full px-4 sm:px-0">
        <div class="relative w-full max-w-xs sm:max-w-md">
            <input type="text" id="searchInput"
                   placeholder="Search claims..."
                   oninput="debouncedSearch('../searchClaims/claimsSearchAction.php', 'fullUserListContainer', 'results')"
                   class="w-full px-4 sm:px-6 py-2 sm:py-3 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 bg-blue-600 h-8 w-8 sm:h-9 sm:w-9 flex items-center justify-center rounded-full pointer-events-none">
                <img src="../images/search_icon.png" alt="Search Icon" class="h-4 w-4 sm:h-5 sm:w-5">
            </div>
        </div>
    </div>
</div>
</section>

<div id="results" class="mt-6 px-4 sm:px-8"></div>

<!-- Claims Display as Cards -->
<div id="fullUserListContainer" class="bg-white px-4 sm:px-8 py-8 sm:py-12 rounded-lg shadow-md mx-2 sm:mx-4 md:mx-8">
    <h2 class="text-2xl sm:text-3xl font-extrabold text-center mb-8 sm:mb-12">Recent Claims</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <?php if (!empty($result)): ?>
            <?php foreach ($result as $res): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-transform transform hover:scale-105 flex flex-col">
                    <div class="h-40 sm:h-48 bg-gray-100 flex items-center justify-center">
                        <?php if (!empty($res['imagePath']) && file_exists($res['imagePath'])): ?>
                            <a href="<?= htmlspecialchars($res['imagePath']) ?>" target="_blank" class="inline-block h-full w-full">
                                <img src="<?= htmlspecialchars($res['imagePath']) ?>" alt="Claimed Item Image" class="h-full w-full object-contain rounded" />
                            </a>
                        <?php else: ?>
                            <span class="text-gray-500">No image</span>
                        <?php endif; ?>
                    </div>
                    <div class="p-3 sm:p-4 bg-gray-800 text-white flex-grow flex flex-col justify-between">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold mb-1"><?= htmlspecialchars($res['itemName']) ?></h3>
                            <p class="text-xs sm:text-sm text-gray-300 mb-2"><?= htmlspecialchars($res['description']) ?></p>

                            <div class="mb-2">
                                <p class="text-xs text-gray-400">Claimer Name:</p>
                                <p class="text-xs sm:text-sm"><?= htmlspecialchars($res['claimerName']) ?></p>
                            </div>
                            <div class="mb-2">
                                <p class="text-xs text-gray-400">Claim Date:</p>
                                <p class="text-xs sm:text-sm"><?= htmlspecialchars($res['claimDate']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Pick‑Up Location:</p>
                                <p class="text-xs sm:text-sm"><?= htmlspecialchars($res['pickUpLocation']) ?></p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-3 sm:mt-4 flex justify-center space-x-2 sm:space-x-4">
                            <a href="editClaims.php?claim_ID=<?= htmlspecialchars($res['claim_ID']) ?>" 
                               class="inline-flex items-center px-2 sm:px-3 py-1 sm:py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm rounded-md shadow transition whitespace-nowrap">
                                Edit
                            </a>

                                <form action="deleteClaims.php" method="POST" 
                                    data-confirm="true"
                                    data-confirm-message="Are you sure you want to delete this item? This action cannot be undone."
                                    data-confirm-title="Delete Claim"
                                    data-confirm-icon="🗑️"
                                    data-confirm-text="Delete"
                                    data-confirm-cancel="Cancel"
                                    data-confirm-class="bg-red-600 hover:bg-red-700"
                                class="inline">
                              <input type="hidden" name="claim_ID" value="<?= htmlspecialchars($res['claim_ID']) ?>">
                              <input type="hidden" name="itemName" value="<?= htmlspecialchars($res['itemName']) ?>">
                            <button type="submit" 
                                class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md shadow transition">
                                Delete
                            </button>
                              </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-500 col-span-full py-8">No claims found.</p>
        <?php endif; ?>
    </div>
</div>


<!-- ========== SCRIPTS ========== -->
<script src="../notifFolder/notifScript.js"></script>
<script src="../elements/mobile-toggle-overlay.js"></script>
<script src="../search.js"></script>
<?php include '../elements/footer-User-Admin.php'; ?>
<script src="../elements/index.js"></script>
</body>
</html>
