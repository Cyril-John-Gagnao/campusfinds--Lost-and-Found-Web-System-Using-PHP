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

$claimerName = $itemName = $description = $claimDate = $pickUpLocation = $imagePath =  "";
$claimerNameMsg = $itemNameMsg = $descriptionMsg = $claimDateMsg = $pickUpLocationMsg = $imagePathMsg =  "";

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['claim_ID'])) {
    $claim_ID = $_GET['claim_ID'];

    $stmt = $masterConn->prepare("SELECT * FROM claims WHERE claim_ID = :claim_ID");
    $stmt->execute([':claim_ID' => $claim_ID]);
    $result = $stmt->fetch();

    if ($result) {
        $_SESSION['claim_ID'] = $result['claim_ID'];
        $claimerName = $result['claimerName'];
        $itemName = $result['itemName'];
        $description = $result['description'];
        $claimDate = $result['claimDate'];
        $pickUpLocation = $result['pickUpLocation'];
        $imagePath = $result['imagePath'];
    } else {
        echo "Claim not found.";
        exit();
    }
    
}

// Handle POST request to update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $claimerName = cleanText($_POST['claimerName']);
    $itemName = cleanText($_POST['itemName']);
    $description = cleanText($_POST['description']);
    $claimDate = cleanText($_POST['claimDate']);
    $pickUpLocation = cleanText($_POST['pickUpLocation']);
    $imagePathMsg = "";
    $oldImagePath = "";

    // Get old image path from database
    $stmtOld = $masterConn->prepare("SELECT imagePath FROM claims WHERE claim_ID = :claim_ID");
    $stmtOld->execute([':claim_ID' => $_SESSION['claim_ID']]);
    $oldImageRow = $stmtOld->fetch();
    $oldImagePath = $oldImageRow ? $oldImageRow['imagePath'] : '';

    $imagePath = uploadEditedImage('image', '../claimsImages/', $oldImagePath, $imagePathMsg);

    if (empty($claimerName)) $claimerNameMsg = "Please input claimer name<br>";
    if (empty($itemName)) $itemNameMsg = "Please input item name<br>";
    if (empty($description)) $descriptionMsg = "Please input description<br>";
    if (empty($claimDate)) $claimDateMsg = "Please input claim date<br>";
    if (empty($pickUpLocation)) $pickUpLocationMsg = "Please input pick up location<br>";
    if (empty($imagePath)) $imagePathMsg = "Please input image <br>";


    if (
        empty($itemNameMsg) &&
        empty($descriptionMsg) &&
        empty($claimerNameMsg) &&
        empty($claimDateMsg) &&
        empty($pickUpLocationMsg) &&
        empty($imagePathMsg)
    ) {
        $stmt = $masterConn->prepare(
             "UPDATE claims SET
                itemName = :itemName,
                description = :description,
                claimerName = :claimerName,
                claimDate = :claimDate,
                pickUpLocation = :pickUpLocation,
                imagePath = :imagePath
            WHERE claim_ID = :claim_ID"
            
        );
        $stmt->execute([
             ':itemName' => $itemName,
            ':description' => $description,
            ':claimerName' => $claimerName,
            ':claimDate' => $claimDate,
            ':pickUpLocation' => $pickUpLocation,
            ':imagePath' => $imagePath,
            ':claim_ID' => $_SESSION['claim_ID']
        ]);

        header("Location: displayClaims.php");
        exit();
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Claimed Item</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
  <div class="max-w-4xl mx-auto bg-white p-10 shadow-md rounded-xl">
    <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-500 text-transparent bg-clip-text mb-8">
      Edit Claimed Item
    </h2>

    <?php include_once '../confirmation.php';?>

        <!-- Form with confirmation -->
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" 
          class="grid grid-cols-1 md:grid-cols-2 gap-6"
          data-confirm="true"
          data-confirm-message="Are you sure you want to edit the claimer name for this item?"
          data-confirm-title="Confirm Claim Edit"
          data-confirm-icon="✏️"
          data-confirm-text="Yes, Edit Claim"
          data-confirm-cancel="Cancel"
          data-confirm-class="bg-blue-600 hover:bg-blue-700"
          data-validate-fields="claimerName,claimDate">
          
      <div class="space-y-4">

        <div>
          <input type="text" name="claimerName" value="<?= $claimerName ?>" placeholder="Claimer Name" class="w-full px-4 py-2 border rounded-md" />
          <p class="text-red-500 text-sm"><?= $claimerNameMsg ?></p>
        </div>

        <div>
          <input type="text" name="itemName" value="<?= $itemName ?>" readonly placeholder="Item Name" class="w-full px-4 py-2 border bg-gray-100 rounded-md" />
          <p class="text-red-500 text-sm"><?= $itemNameMsg ?></p>
        </div>

        <div>
          <textarea name="description" readonly placeholder="Description" class="w-full px-4 py-2 border bg-gray-100 rounded-md"><?= $description ?></textarea>
          <p class="text-red-500 text-sm"><?= $descriptionMsg ?></p>
        </div>

        <div>
          <input type="date" name="claimDate" value="<?= $claimDate ?>" placeholder="Claim Date" class="w-full px-4 py-2 border rounded-md" />
          <p class="text-red-500 text-sm"><?= $claimDateMsg ?></p>
        </div>

        <div>
          <input type="text" name="pickUpLocation" value="<?= $pickUpLocation ?>" readonly  placeholder="Pick Up Location" class="w-full px-4 py-2 border rounded-md" />
          <p class="text-red-500 text-sm"><?= $pickUpLocationMsg ?></p>
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

      <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white font-semibold text-sm py-2 px-10 rounded-md">
        Submit
      </button>
    </form>

    <div class="mt-4 text-center">
      <a href="displayClaims.php" class="text-blue-500 hover:underline">← Back to Claimed Items</a>
    </div>
  </div>
</body>
</html>

