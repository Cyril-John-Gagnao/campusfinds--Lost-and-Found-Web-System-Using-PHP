<?php 
include_once __DIR__ . '/../dbconn.php';
include_once '../authentication.php';
requireAdminLogin();

$slaveConn   = getSlaveConnection();

$stmt   = $slaveConn->prepare("SELECT * FROM lost_items WHERE status = 'approved' ORDER BY dateFound DESC, itemName ASC");
$stmt->execute();
$result = $stmt->fetchAll();

// Fetch notifications for admin (unread first)
$masterConn = getMasterConnection();
$stmtNotif  = $masterConn->prepare("SELECT * FROM notifications WHERE receiver_role = 'admin' ORDER BY created_at DESC");
$stmtNotif->execute();
$notifications = $stmtNotif->fetchAll(PDO::FETCH_ASSOC);

// Count unread notifications
$stmtCount = $masterConn->prepare("SELECT COUNT(*) FROM notifications WHERE receiver_role = 'admin' AND is_read = 0");
$stmtCount->execute();
$unreadCount = $stmtCount->fetchColumn();

// Count pending lost items
$stmtPending = $masterConn->prepare("SELECT COUNT(*) FROM lost_items WHERE status = 'pending'");
$stmtPending->execute();
$pendingCount = $stmtPending->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Menu – Lost and Found System</title>
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

<!-- ========== HEADER / HERO (with background + overlay) ========== -->
<section class="relative bg-blue-200 bg-cover bg-center min-h-[200px] sm:min-h-[300px] md:min-h-[350px] lg:min-h-[400px]"
         style="background-image: url('../images/bg8.jpg');">
         
  <!-- Semi-transparent overlay -->
  <div class="absolute inset-0 bg-gray-900/50"></div>

  <!-- Content -->
  <div class="relative py-12 sm:py-16 md:py-20 lg:py-24 px-4 sm:px-6 md:px-8 text-center">
    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500 drop-shadow-lg leading-tight">
      Lost Items
    </h1>
    <p class="mt-2 sm:mt-3 text-white text-xs sm:text-sm md:text-base font-medium max-w-md mx-auto">
      Not all are lost, anything can be found.
    </p>

    <!-- Search Bar -->
    <div class="mt-6 sm:mt-8 md:mt-10 flex justify-center w-full px-2 sm:px-4">
      <div class="relative w-full max-w-xs sm:max-w-md md:max-w-lg lg:max-w-xl">
        <input id="searchInput" type="text"
               placeholder="Search lost items..."
               oninput="debouncedSearch('../searchLost/searchAction.php','fullUserListContainer','results')"
               class="w-full px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 text-sm sm:text-base border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-12 sm:pr-14" />
        <div class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 bg-blue-600 h-7 w-7 sm:h-8 sm:w-8 md:h-9 md:w-9 flex items-center justify-center rounded-full pointer-events-none">
          <img src="../images/search_icon.png" alt="Search Icon" class="h-3 w-3 sm:h-4 sm:w-4 md:h-5 md:w-5" />
        </div>
      </div>
    </div>

    <!-- Add Lost Item button -->
    <div class="mt-8">
      <a href="../lostItemFolder/addlostItemForm.php"
         class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-md shadow-md transition duration-200 transform hover:scale-105">
        Add Lost Item
      </a>
    </div>
  </div>
</section>

<!-- Search Results (AJAX) -->
<div id="results" class="mt-6 px-4 sm:px-8"></div>

<!-- ========== APPROVED ITEMS GRID ========== -->
<section id="fullUserListContainer" class="bg-white px-4 sm:px-8 py-8 sm:py-12 rounded-lg shadow-md mx-2 sm:mx-4 md:mx-8">
  <h2 class="text-2xl sm:text-3xl font-extrabold text-center mb-8 sm:mb-12 text-gray-800">Recently Found Items</h2>

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
    <?php if (!empty($result)): ?>
      <?php foreach ($result as $res): ?>
        <!-- Card -->
        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transform transition hover:scale-105 flex flex-col">
          <!-- Image -->
          <div class="h-40 sm:h-48 bg-gray-100 flex items-center justify-center">
            <?php if (!empty($res['imagePath']) && file_exists($res['imagePath'])): ?>
              <a href="<?= htmlspecialchars($res['imagePath']) ?>" target="_blank" class="block h-full w-full">
                <img src="<?= htmlspecialchars($res['imagePath']) ?>" alt="Lost Item Image" 
                     class="h-full w-full object-contain rounded"/>
              </a>
            <?php else: ?>
              <span class="text-gray-500">No image</span>
            <?php endif; ?>
          </div>

          <!-- Details -->
          <div class="p-3 sm:p-4 bg-gray-800 text-white flex-1 flex flex-col justify-between">
            <div>
              <h3 class="text-base sm:text-lg font-bold mb-1"><?= htmlspecialchars($res['itemName']) ?></h3>
              <p class="text-xs sm:text-sm text-gray-300 mb-2 truncate"><?= htmlspecialchars($res['description']) ?></p>

              <div class="mb-2">
                <p class="text-xs text-gray-400">Location Found:</p>
                <p class="text-xs sm:text-sm"><?= htmlspecialchars($res['locationFound']) ?></p>
              </div>
              <div class="mb-2">
                <p class="text-xs text-gray-400">Pick‑Up Location:</p>
                <p class="text-xs sm:text-sm"><?= htmlspecialchars($res['pickUpLocation']) ?></p>
              </div>
              <div class="mb-2">
                <p class="text-xs text-gray-400">Date Found:</p>
                <p class="text-xs sm:text-sm"><?= htmlspecialchars($res['dateFound']) ?></p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Returnee:</p>
                <p class="text-xs sm:text-sm"><?= htmlspecialchars($res['returneeName']) ?></p>
              </div>
            </div>
            
            <div class="mt-4 flex justify-between space-x-2">

                  <!-- Edit Button -->
                  <a href="../lostItemFolder/editLostItem.php?lostItem_ID=<?= htmlspecialchars($res['lostItem_ID']) ?>" 
                  class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md shadow transition">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M16.5 3.75a2.25 2.25 0 113.182 3.182L7.5 19.564 3 21l1.436-4.5L16.5 3.75z" />
                      </svg>
                      Edit
                  </a>
                            
              <!-- Delete Button -->
              <form action="../lostItemFolder/deleteLostItem.php" method="POST" 
                  data-confirm="true"
                  data-confirm-message="Are you sure you want to delete '<?= htmlspecialchars($res['itemName']) ?>'? This action cannot be undone."
                  data-confirm-title="Delete Lost Item"
                  data-confirm-icon="🗑️"
                  data-confirm-text="Delete"
                  data-confirm-cancel="Cancel"
                  data-confirm-class="bg-red-600 hover:bg-red-700"
                  class="inline">
                <input type="hidden" name="lostItem_ID" value="<?= htmlspecialchars($res['lostItem_ID']) ?>">
                <input type="hidden" name="itemName" value="<?= htmlspecialchars($res['itemName']) ?>">
                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                      Delete
                  </button>
              </form>

              <!-- Add Claim Button -->
              <form action="../claimFolder/addClaimForm.php" method="POST" 
                  data-confirm="true"
                  data-confirm-message="Add '<?= htmlspecialchars($res['itemName']) ?>' to claimed items?"
                  data-confirm-title="Add Claim"
                  data-confirm-icon="📋"
                  data-confirm-text="Add Claim"
                  data-confirm-cancel="Cancel"
                  data-confirm-class="bg-green-600 hover:bg-green-700"
                  class="inline">
              <input type="hidden" name="lostItem_ID" value="<?= htmlspecialchars($res['lostItem_ID']) ?>">
              <input type="hidden" name="itemName" value="<?= htmlspecialchars($res['itemName']) ?>">
              <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md shadow transition">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                      </svg>
                      Claim
                  </button>
              </form>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center text-gray-500 col-span-full py-8">No recently found items.</p>
    <?php endif; ?>
  </div>
</section>


<!-- ========== SCRIPTS ========== -->
<script src="../notifFolder/notifScript.js"></script>
<script src="../elements/mobile-toggle-overlay.js"></script>
<script src="../search.js"></script>
<?php include '../elements/footer-User-Admin.php'; ?>
<script src="../elements/index.js"></script>

</body>
</html>
