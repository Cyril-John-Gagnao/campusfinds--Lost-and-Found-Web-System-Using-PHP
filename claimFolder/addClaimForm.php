<?php
include_once __DIR__ . '/../dbconn.php'; 
include_once '../authentication.php';

requireAdminLogin();

$masterConn = getMasterConnection();

#to retrieve all existing id of lost items
$lostItemStmt = $masterConn->prepare("SELECT lostItem_ID, itemName, description, pickUpLocation FROM lost_items WHERE status = 'approved'");
$lostItemStmt->execute();
$lostItems = $lostItemStmt->fetchAll(PDO::FETCH_ASSOC);

$lostItem_IDMsg = $claimerNameMsg = $claimDateMsg = $pickUpLocationMsg = $claimerEmailMsg = "";
$lostItem_ID = $admin_ID = $claimerName = $claimDate = $pickUpLocation = $claimerEmail = "";


function cleanText($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lostItem_ID = isset($_POST['lostItem_ID']) ? cleanText($_POST['lostItem_ID']) : '';
    $itemName = isset($_POST['itemName']) ? cleanText($_POST['itemName']) : '';
    $description = isset($_POST['itemDescription']) ? cleanText($_POST['itemDescription']) : '';
    $admin_ID = isset($_POST['admin_ID']) ? cleanText($_POST['admin_ID']) : '';
    $claimerName = isset($_POST['claimerName']) ? cleanText($_POST['claimerName']) : '';
    $claimerEmail = isset($_POST['claimerEmail']) ? cleanText($_POST['claimerEmail']) : '';
    $claimDate = isset($_POST['claimDate']) ? cleanText($_POST['claimDate']) : '';
    $pickUpLocation = isset($_POST['pickUpLocation']) ? cleanText($_POST['pickUpLocation']) : '';
    
    $hasError = false;

    if (empty($lostItem_ID)) {
        $lostItem_IDMsg = "Please input lost item ID<br>";
        $hasError = true;
    }
    if (empty($claimerName)) {
        $claimerNameMsg = "Please input claimer name<br>";
        $hasError = true;
    }
    if (empty($claimerEmail)) {
    $claimerEmail = "N/A";
    $user_ID = null;
    } else if (!filter_var($claimerEmail, FILTER_VALIDATE_EMAIL)) {
        $claimerEmailMsg = "Please input a valid email format<br>";
        $hasError = true;
    } else {
        $userQuery = $masterConn->prepare("SELECT user_ID FROM user WHERE email = :email");
        $userQuery->execute([':email' => $claimerEmail]);
        $userResult = $userQuery->fetch(PDO::FETCH_ASSOC);

        if ($userResult) {
            $user_ID = $userResult['user_ID'];
        } else {
            $user_ID = null;
        }
    }
    if (empty($claimDate)) {
        $claimDateMsg = "Please input claim date<br>";
        $hasError = true;
    }
    if (empty($pickUpLocation)) {
        $pickUpLocationMsg = "Please input pick up location<br>";
        $hasError = true;
    }
    
    // no validation errors ,  insert into DB
    if (!$hasError) {

        $lostItemStmt = $masterConn->prepare("SELECT * FROM lost_items WHERE lostItem_ID = :id AND status = 'approved'");
        $lostItemStmt->execute([':id' => $lostItem_ID]);
        $lostItem = $lostItemStmt->fetch(PDO::FETCH_ASSOC);

        if ($lostItem) {
                if ($lostItem['status'] !== 'approved') { 
                    $lostItem_IDMsg = "❌ This item has not been approved and cannot be claimed.";
                    $hasError = true;
                } else {
                    // proceed with moving image, inserting claim, deleting lost item, etc.
                    $itemName = $lostItem['itemName'];
                    $description = $lostItem['description'];
                    $originalImagePath = $lostItem['imagePath'];

                    // Move image from 'lostImages/' to 'claimsImages/' folder
                    $newImagePath = '';
                    if (!empty($originalImagePath) && file_exists($originalImagePath)) {
                        $newDir = '../claimsImages/';
                        if (!is_dir($newDir)) {
                            mkdir($newDir, 0777, true);
                        }

                        $filename = basename($originalImagePath);
                        $newImagePath = $newDir . time() . "_" . $filename;

                        if (!rename($originalImagePath, $newImagePath)) {
                            echo "Failed to move image.";
                            exit();
                        }
                    }
                }

                // Insert into claims table
                $stmt = $masterConn->prepare("INSERT INTO claims (lostItem_ID, itemName, description, imagePath, admin_ID,user_ID, claimerName,claimerEmail, claimDate, pickUpLocation)
                VALUES (:lostItem_ID, :itemName, :description, :imagePath, :admin_ID,:user_ID, :claimerName,:claimerEmail, :claimDate, :pickUpLocation)");

                $stmt->execute([
                    ':lostItem_ID' => $lostItem_ID,
                    ':itemName' => $itemName,
                    ':description' => $description,
                    ':imagePath' => $newImagePath,
                    ':admin_ID' => $admin_ID,
                    ':user_ID' => $user_ID,
                    ':claimerName' => $claimerName,
                    ':claimerEmail' => $claimerEmail,
                    ':claimDate' => $claimDate,
                    ':pickUpLocation' => $pickUpLocation,
                    
                ]);

                // Delete lost item record
                $deleteStmt = $masterConn->prepare("DELETE FROM lost_items WHERE lostItem_ID = :lostItem_ID");
                $deleteStmt->execute([':lostItem_ID' => $lostItem_ID]);

                header("Location: displayClaims.php?msg=ItemAdded");
                exit();
        }
            
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Claims</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 text-gray-800 p-6">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl">
            <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-500 text-transparent bg-clip-text mb-8">Add Claim Item</h2>
            
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" class="space-y-5">

                <div>
                    <label for="lostItem_ID" class="block text-sm font-medium mb-1">Lost Item ID:</label>
                    <select name="lostItem_ID" class="w-full border px-3 py-2 rounded">
                        <option value="">-- Select Lost Item ID --</option>
                        <?php foreach ($lostItems as $item): ?>
                            <option value="<?= $item['lostItem_ID'] ?>" <?= $lostItem_ID == $item['lostItem_ID'] ? 'selected' : '' ?>>
                                <?= $item['lostItem_ID'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="text-red-500 text-sm"><?= $lostItem_IDMsg ?></span>
                </div>

                <div>
                    <label for="itemName" class="block text-sm font-medium mb-1">Item Name:</label>
                    <input type="text" id="itemName" name="itemName" value="" readonly class="w-full border px-3 py-2 rounded bg-gray-100">
                </div>

                <div>
                    <label for="itemDescription" class="block text-sm font-medium mb-1">Description:</label>
                    <textarea id="itemDescription" name="itemDescription" readonly class="w-full border px-3 py-2 rounded bg-gray-100"></textarea>
                </div>

                <div>
                    <label for="pickUpLocation" class="block text-sm font-medium mb-1">Pick Up Location:</label>
                    <input type="text" id="pickUpLocation" name="pickUpLocation" readonly class="w-full border px-3 py-2 rounded bg-gray-100">
                </div>

                <div>
                    <label for="claimerName" class="block text-sm font-medium mb-1">Claimer Name:</label>
                    <input type="text" name="claimerName" value="<?= $claimerName ?>" class="w-full border px-3 py-2 rounded">
                    <span class="text-red-500 text-sm"><?= $claimerNameMsg ?></span>
                </div>

                <div>
                    <label for="claimerEmail" class="block text-sm font-medium mb-1">Claimer Email:</label>
                    <input type="email" name="claimerEmail" value="" class="w-full border px-3 py-2 rounded">
                    <span class="text-red-500 text-sm"><?= $claimerEmailMsg ?? '' ?></span>
                </div>

                <div>
                    <label for="claimDate" class="block text-sm font-medium mb-1">Claim Date:</label>
                    <input type="date" name="claimDate" value="<?= $claimDate ?>" class="w-full border px-3 py-2 rounded">
                    <span class="text-red-500 text-sm"><?= $claimDateMsg ?></span>
                </div>

                <input type="hidden" name="admin_ID" value="<?= isset($_SESSION['admin_ID']) ? $_SESSION['admin_ID'] : '' ?>">

                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">Submit</button>
            </form>

            <div class="flex justify-between mt-6 text-blue-600 text-sm">
                <a href="displayClaims.php" class="hover:underline">Display Claimed Items</a>
                <a href="../adminFolder/adminDashBoard.php" class="hover:underline">Home Dashboard</a>
            </div>
        </div>
    </div>

    <script>
        const lostItems = <?= json_encode($lostItems) ?>;
        const lostItemSelect = document.querySelector('select[name="lostItem_ID"]');
        const itemNameField = document.getElementById('itemName');
        const itemDescriptionField = document.getElementById('itemDescription');
        const pickUpLocationField = document.getElementById('pickUpLocation');

        lostItemSelect.addEventListener('change', function () {
            const selectedID = this.value;
            const item = lostItems.find(item => item.lostItem_ID == selectedID);
            if (item) {
                itemNameField.value = item.itemName;
                itemDescriptionField.value = item.description;
                pickUpLocationField.value = item.pickUpLocation || '';
            } else {
                itemNameField.value = "";
                itemDescriptionField.value = "";
                pickUpLocationField.value = "";
            }
        });

        window.addEventListener('DOMContentLoaded', () => {
            if (lostItemSelect.value) {
                lostItemSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>
