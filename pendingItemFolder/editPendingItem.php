<?php
//session_start();
include_once __DIR__ . '/../dbconn.php';
include_once '../authentication.php';
include_once '../editImageFunction.php';
requireAdminLogin();

$masterConn = getMasterConnection();

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

$itemName = $description = $locationFound = $pickUpLocation = $dateFound = $imagePath = $returneeName = "";
$itemNameMsg = $descriptionMsg = $locationFoundMsg = $pickUpLocationMsg = $dateFoundMsg = $imagePathMsg = "";

// Handle URL parameter first (from the link in the listing page)
if (isset($_GET['id'])) {
    $_SESSION['lostItem_ID'] = $_GET['id'];
} else if (isset($_POST['id'])) {
    $_SESSION['lostItem_ID'] = $_POST['id'];
    header("Location: editPendingItem.php");
    exit();
}

// If we have a pending item ID in the session, load its data
if (isset($_SESSION['lostItem_ID'])) {
    $id = $_SESSION['lostItem_ID'];

    $stmt = $masterConn->prepare("SELECT * FROM lost_items WHERE lostItem_ID = :lostItem_ID");
    $stmt->execute([':lostItem_ID' => $id]);
    $result = $stmt->fetch();

    if ($result) {
        $itemName = $result['itemName'];
        $description = $result['description'];
        $locationFound = $result['locationFound'];
        $pickUpLocation = $result['pickUpLocation'];
        $dateFound = $result['dateFound'];
        $imagePath = $result['imagePath'];
        $returneeName = $result['returneeName'];
    } else {
        echo "Pending item not found.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $itemName = cleanText($_POST['itemName']);
    $description = cleanText($_POST['description']);
    $locationFound = cleanText($_POST['locationFound']);
    $pickUpLocation = cleanText($_POST['pickUpLocation']);
    $dateFound = cleanText($_POST['dateFound']);
    $returneeName = cleanText($_POST['returneeName']);
    if (empty($returneeName)) $returneeName = "N/A";

    // Get old image path
    $stmtOld = $masterConn->prepare("SELECT imagePath FROM lost_items WHERE lostItem_ID = :lostItem_ID");
    $stmtOld->execute([':lostItem_ID' => $_SESSION['lostItem_ID']]);
    $oldImageRow = $stmtOld->fetch();
    $oldImagePath = $oldImageRow ? $oldImageRow['imagePath'] : '';

    $imagePath = uploadEditedImage('image', '../pendingLostImages/', $oldImagePath, $imagePathMsg);

    if (empty($itemName)) $itemNameMsg = "Please input item name";
    if (empty($description)) $descriptionMsg = "Please input description";
    if (empty($locationFound)) $locationFoundMsg = "Please input location found";
    if (empty($pickUpLocation)) $pickUpLocationMsg = "Please input pickup location";
    if (empty($dateFound)) $dateFoundMsg = "Please input date found";
    if (empty($imagePath)) $imagePathMsg = "Please upload an image";

    if (
        empty($itemNameMsg) && empty($descriptionMsg) && empty($locationFoundMsg) &&
        empty($pickUpLocationMsg) && empty($dateFoundMsg) && empty($imagePathMsg)
    ) {
        $stmt = $masterConn->prepare(
            "UPDATE lost_items SET
                itemName = :itemName,
                description = :description,
                locationFound = :locationFound,
                pickUpLocation = :pickUpLocation,
                dateFound = :dateFound,
                imagePath = :imagePath,
                returneeName = :returneeName
            WHERE lostItem_ID = :lostItem_ID"
        );
        $stmt->execute([
            ':itemName' => $itemName,
            ':description' => $description,
            ':locationFound' => $locationFound,
            ':pickUpLocation' => $pickUpLocation,
            ':dateFound' => $dateFound,
            ':imagePath' => $imagePath,
            ':returneeName' => $returneeName,
            ':lostItem_ID' => $_SESSION['lostItem_ID']
        ]);

        unset($_SESSION['lostItem_ID']);
        header("Location: displayUserPendingLostItem.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Pending Lost Item</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 py-10">
  <div class="max-w-4xl mx-auto bg-white p-10 shadow-md rounded-xl">
    <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-400 text-transparent bg-clip-text mb-8">
      Edit Pending Lost Item
    </h2>

      <?php include_once '../confirmation.php'; ?>

      <!-- Original form with confirmation -->
      <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" 
            class="grid grid-cols-1 md:grid-cols-2 gap-6"
            data-confirm="true"
            data-confirm-message="Are you sure you want to edit this pending item?"
            data-confirm-title="Confirm Pending Item Edit"
            data-confirm-icon="👤"
            data-confirm-text="Yes, Edit Pending Item"
            data-confirm-cancel="Cancel"
            data-confirm-class="bg-green-600 hover:bg-green-700"
            data-validate-fields="itemName,description,locationFound,pickUpLocation,dateFound,returneeName">
      <input type="hidden" name="update" value="1">

      <div class="space-y-4">
        <div>
          <input type="text" name="itemName" value="<?= htmlspecialchars($itemName) ?>" placeholder="Name of Item" class="w-full px-4 py-2 border rounded-md" />
          <p class="text-red-500 text-sm"><?= $itemNameMsg ?></p>
        </div>

        <div>
          <input type="text" name="description" value="<?= htmlspecialchars($description) ?>" placeholder="Description" class="w-full px-4 py-2 border rounded-md" />
          <p class="text-red-500 text-sm"><?= $descriptionMsg ?></p>
        </div>

        <div>
          <input type="text" name="locationFound" value="<?= htmlspecialchars($locationFound) ?>" placeholder="Location Found" class="w-full px-4 py-2 border rounded-md" />
          <p class="text-red-500 text-sm"><?= $locationFoundMsg ?></p>
        </div>

        <div>
          <select name="pickUpLocation" class="w-full px-4 py-2 border rounded-md">
            <option value="">-- Select Pick Up Location --</option>

            <optgroup label="Academic">
              <option value="CAS Admin" <?= $pickUpLocation == 'CAS Admin' ? 'selected' : '' ?>>CAS Admin</option>
              <option value="COC Admin" <?= $pickUpLocation == 'COC Admin' ? 'selected' : '' ?>>COC Admin</option>
              <option value="COD Admin" <?= $pickUpLocation == 'COD Admin' ? 'selected' : '' ?>>COD Admin</option>
              <option value="COL Admin" <?= $pickUpLocation == 'COL Admin' ? 'selected' : '' ?>>COL Admin</option>
              <option value="CICT Admin" <?= $pickUpLocation == 'CICT Admin' ? 'selected' : '' ?>>CICT Admin</option>
              <option value="CON Admin" <?= $pickUpLocation == 'CON Admin' ? 'selected' : '' ?>>CON Admin</option>
              <option value="PESCAR Admin" <?= $pickUpLocation == 'PESCAR Admin' ? 'selected' : '' ?>>PESCAR Admin</option>
              <option value="COE Admin" <?= $pickUpLocation == 'COE Admin' ? 'selected' : '' ?>>COE Admin</option>
              <option value="CBM Admin" <?= $pickUpLocation == 'CBM Admin' ? 'selected' : '' ?>>CBM Admin</option>
              <option value="COM Admin" <?= $pickUpLocation == 'COM Admin' ? 'selected' : '' ?>>COM Admin</option>
            </optgroup>

            <optgroup label="Non-Academic">
              <option value="Library Admin" <?= $pickUpLocation == 'Library Admin' ? 'selected' : '' ?>>Library Admin</option>
              <option value="HR Admin" <?= $pickUpLocation == 'HR Admin' ? 'selected' : '' ?>>HR Admin</option>
              <option value="BAC Admin" <?= $pickUpLocation == 'BAC Admin' ? 'selected' : '' ?>>BAC Admin</option>
              <option value="OSA Admin" <?= $pickUpLocation == 'OSA Admin' ? 'selected' : '' ?>>OSA Admin</option>
              <option value="Guidance Admin" <?= $pickUpLocation == 'Guidance Admin' ? 'selected' : '' ?>>Guidance Admin</option>
              <option value="Clinic Admin" <?= $pickUpLocation == 'Clinic Admin' ? 'selected' : '' ?>>Clinic Admin</option>
              <option value="Admin Admin" <?= $pickUpLocation == 'Admin Admin' ? 'selected' : '' ?>>Admin Admin</option>
              <option value="Registrar Admin" <?= $pickUpLocation == 'Registrar Admin' ? 'selected' : '' ?>>Registrar Admin</option>
            </optgroup>
          </select>
          <p class="text-red-500 text-sm"><?= $pickUpLocationMsg ?></p>
        </div>

        <div>
          <input type="date" name="dateFound" value="<?= htmlspecialchars($dateFound) ?>" placeholder="Date Found" class="w-full px-4 py-2 border rounded-md" />
          <p class="text-red-500 text-sm"><?= $dateFoundMsg ?></p>
        </div>

        <div>
          <input type="text" name="returneeName" value="<?= htmlspecialchars($returneeName) ?>" placeholder="Returnee Name (optional)" class="w-full px-4 py-2 border rounded-md" />
        </div>
      </div>

      <div class="flex flex-col items-center space-y-4">
        <div>
          <label class="block text-gray-700 font-medium">Current Image</label>
          <?php if (!empty($imagePath) && file_exists($imagePath)): ?>
            <img src="<?= $imagePath ?>" alt="Current Image" class="w-48 h-48 object-cover rounded-xl shadow-lg">
          <?php else: ?>
            <p class="text-gray-500 text-sm">No image available.</p>
          <?php endif; ?>
        </div>

        <div class="w-full">
          <label for="image" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg cursor-pointer hover:bg-blue-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4-4-4m16 8l-4-4 4-4m-4 4H4"></path>
            </svg>
            Change Image
            <input id="image" name="image" type="file" class="hidden">
          </label>
          <p class="text-red-500 text-sm mt-2"><?= $imagePathMsg ?></p>
        </div>
      </div>

      <div class="col-span-2">
        <button type="submit" onclick="return confirmSubmit();" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">Update Pending Item</button>
      </div>
    </form>

    <div class="mt-4 text-center">
      <a href="displayUserPendingLostItem.php" class="text-blue-600 hover:underline">← Back to Pending Items List</a>
    </div>
  </div>
</body>
</html>
