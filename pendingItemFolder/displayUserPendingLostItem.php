<?php
include_once __DIR__ . '/../dbconn.php';
include_once '../authentication.php';
requireAdminLogin();

$slaveConn = getSlaveConnection();

// Fetch pending lost items only
$stmt = $slaveConn->prepare("SELECT * FROM lost_items WHERE status = 'pending' ORDER BY dateFound DESC, itemName ASC");
$stmt->execute();
$pending_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch notifications for admin (unread first)
$masterConn = getMasterConnection();
$stmtNotif = $masterConn->prepare("SELECT * FROM notifications WHERE receiver_role = 'admin' ORDER BY created_at DESC");
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
    <title>Pending Lost Items - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

<?php
include '../elements/mobile-overlay.php';
?>

<?php
include '../elements/navbar-Admin.php';
?>

<?php include_once '../confirmation.php'; ?>

<!-- HEADER without Search Bar -->
<section class="relative bg-blue-200 bg-cover bg-center min-h-[200px] sm:min-h-[300px] md:min-h-[400px] lg:min-h-[450px]"
         style="background-image: url('../images/bg8.jpg');">
  <!-- Semi-transparent overlay -->
  <div class="absolute inset-0 bg-gray-900/50"></div>

  <!-- Content -->
  <div class="relative py-12 sm:py-16 md:py-20 lg:py-24 px-4 sm:px-6 md:px-8 text-center">
    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500 drop-shadow-lg leading-tight py-4">
      Pending Lost Items
    </h1>
    <p class="mt-2 sm:mt-3 text-white text-xs sm:text-sm md:text-base font-medium max-w-md mx-auto">
      Review and approve or reject pending items.
    </p>
</div>
</section>

<!-- Pending Lost Items Display -->
<div id="fullUserListContainer" class="bg-white px-4 sm:px-8 py-8 sm:py-12 rounded-lg shadow-md mx-2 sm:mx-4 md:mx-8">
    <h2 class="text-2xl sm:text-3xl font-extrabold text-center mb-8 sm:mb-12">Pending Items</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <?php if (!empty($pending_items)): ?>
            <?php foreach ($pending_items as $item): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-transform transform hover:scale-105 flex flex-col">
                    <div class="h-40 sm:h-48 bg-gray-100 flex items-center justify-center">
                        <?php if (!empty($item['imagePath']) && file_exists($item['imagePath'])): ?>
                            <a href="<?= htmlspecialchars($item['imagePath']) ?>" target="_blank" class="inline-block h-full w-full">
                                <img src="<?= htmlspecialchars($item['imagePath']) ?>" alt="Lost Item Image" class="h-full w-full object-contain rounded" />
                            </a>
                        <?php else: ?>
                            <span class="text-gray-500">No image</span>
                        <?php endif; ?>
                    </div>
                    <div class="p-3 sm:p-4 bg-gray-800 text-white flex-grow flex flex-col justify-between">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold mb-1"><?= htmlspecialchars($item['itemName']) ?></h3>
                            <p class="text-xs sm:text-sm text-gray-300 mb-2 truncate"><?= htmlspecialchars($item['description']) ?></p>
                            <div class="mb-2">
                                <p class="text-xs text-gray-400">Location Found:</p>
                                <p class="text-xs sm:text-sm"><?= htmlspecialchars($item['locationFound']) ?></p>
                            </div>
                            <div class="mb-2">
                                <p class="text-xs text-gray-400">Pick‑Up Location:</p>
                                <p class="text-xs sm:text-sm"><?= htmlspecialchars($item['pickUpLocation']) ?></p>
                            </div>
                            <div class="mb-2">
                                <p class="text-xs text-gray-400">Date Found:</p>
                                <p class="text-xs sm:text-sm"><?= htmlspecialchars($item['dateFound']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Returnee:</p>
                                <p class="text-xs sm:text-sm"><?= htmlspecialchars($item['returneeName']) ?></p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 flex gap-3 justify-center">
                            <!-- Approve Button -->
                            <form method="post" action="handlePendingItem.php" 
                                  data-confirm="true"
                                  data-confirm-message="Are you sure you want to approve '<?= htmlspecialchars($item['itemName']) ?>'? This will make it visible to all users."
                                  data-confirm-title="Approve Item"
                                  data-confirm-icon="✅"
                                  data-confirm-text="Approve"
                                  data-confirm-cancel="Cancel"
                                  data-confirm-class="bg-green-600 hover:bg-green-700"
                                  class="inline">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($item['lostItem_ID']) ?>" />
                                <input type="hidden" name="action" value="approve" />
                                <button type="submit" class="min-w-[100px] bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded font-semibold text-center">
                                    Approve
                                </button>
                            </form>

                            <!-- Reject Button -->
                            <form method="post" action="handlePendingItem.php" 
                                  data-confirm="true"
                                  data-confirm-message="Are you sure you want to reject '<?= htmlspecialchars($item['itemName']) ?>'? This item will be permanently deleted."
                                  data-confirm-title="Reject Item"
                                  data-confirm-icon="❌"
                                  data-confirm-text="Reject"
                                  data-confirm-cancel="Cancel"
                                  data-confirm-class="bg-red-600 hover:bg-red-700"
                                  class="inline">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($item['lostItem_ID']) ?>" />
                                <input type="hidden" name="action" value="reject" />
                                <button type="submit" class="min-w-[100px] bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded font-semibold text-center">
                                    Reject
                                </button>
                            </form>
                            
                            <!-- Edit Button -->
                            <a href="editPendingItem.php?id=<?= htmlspecialchars($item['lostItem_ID']) ?>" 
                               class="min-w-[100px] bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded font-semibold text-center inline-flex items-center justify-center">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-600 text-base sm:text-lg col-span-full py-8">No pending lost items found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- ========== SCRIPTS ========== -->
<script src="../notifFolder/notifScript.js"></script>
<script src="../elements/mobile-toggle-overlay.js"></script>
<?php include '../elements/footer-User-Admin.php'; ?>
<script src="../elements/index.js"></script>
</body>
</html>
