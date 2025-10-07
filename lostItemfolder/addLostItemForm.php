<?php
include_once __DIR__ . '/../dbconn.php'; 
include_once '../authentication.php';
require_once '../imageUploadFuntion.php';
requireAdminLogin();

$masterConn = getMasterConnection();

$itemNameMsg = $descriptionMsg = $locationFoundMsg = $pickUpLocationMsg = $dateFoundMsg = $imagePathMsg  = "";
$itemName = $description = $locationFound = $pickUpLocation = $dateFound = $imagePath = $returneeName = $admin_ID =  "";

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $itemName = cleanText($_POST['itemName']);
    $description = cleanText($_POST['description']);
    $locationFound = cleanText($_POST['locationFound']);
    $pickUpLocation = cleanText($_POST['pickUpLocation']);
    $dateFound = cleanText($_POST['dateFound']);
    $returneeName = cleanText($_POST['returneeName']);
        if (empty($returneeName)) {
            $returneeName = "N/A";
        }
    $admin_ID = cleanText($_POST['admin_ID']);

    // Validation
    $hasError = false;

    if (empty($itemName)) {
        $itemNameMsg = "Please input item name<br>";
        $hasError = true;
    }
    if (empty($description)) {
        $descriptionMsg = "Please input description<br>";
        $hasError = true;
    }
    if (empty($locationFound)) {
        $locationFoundMsg = "Please input location found<br>";
        $hasError = true;
    }
    if (empty($pickUpLocation)) {
        $pickUpLocationMsg = "Please input pickup location<br>";
        $hasError = true;
    }
    if (empty($dateFound)) {
        $dateFoundMsg = "Please input date found<br>";
        $hasError = true;
    }

     // process the image upload only if no validation errors
    if (!$hasError) {
        $imagePath = uploadImage('imagePath', '../lostImages/', $imagePathMsg);
        if (!$imagePath) {
            $hasError = true;
        }
    }

    // insert into DB
    if (!$hasError) {
        $stmt = $masterConn->prepare("INSERT INTO lost_items (itemName, description, locationFound, pickUpLocation, dateFound, imagePath, returneeName, admin_ID, status) 
        VALUES (:itemName, :description, :locationFound, :pickUpLocation, :dateFound, :imagePath, :returneeName, :admin_ID, :status)");

        $stmt->execute([
            ':itemName' => $itemName,
            ':description' => $description,
            ':locationFound' => $locationFound,
            ':pickUpLocation' => $pickUpLocation,
            ':dateFound' => $dateFound,
            ':imagePath' => $imagePath,
            ':returneeName' => $returneeName,
            ':admin_ID' => $admin_ID,
            ':status' => 'approved' 
        ]);

        header("Location: ../adminFolder/adminDashBoard.php?msg=ItemAdded");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Lost Item</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 text-gray-800 p-6">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl">
            <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-500 text-transparent bg-clip-text mb-8">Add a Lost Item</h2>
            
            <?php include_once '../confirmation.php'; ?>

            <!-- Form using the helper function -->
            <?= createConfirmedForm(
                htmlspecialchars($_SERVER['PHP_SELF']), 
                'POST', 
                'multipart/form-data',
                [
                    'message' => "Are you sure you want to add this lost item?",
                    'title' => 'Confirm Lost Item Creation',
                    'icon' => '👤',
                    'confirmText' => 'Yes, Add Lost Item',
                    'confirmClass' => 'bg-green-600 hover:bg-green-700',
                    'validateFields' => 'itemName,description,locationFound,pickUpLocation,dateFound,returneeName',
                    'validateFiles' => 'imagePath'
                ]
            ) ?>

                <div>
                    <label for="itemName" class="block text-sm font-medium mb-1">Item Name:</label>
                    <input type="text" name="itemName" value="<?= $itemName ?>" class="w-full border px-3 py-2 rounded">
                    <span class="text-red-500 text-sm"><?= $itemNameMsg ?></span>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium mb-1">Description:</label>
                    <input type="text" name="description" value="<?= $description ?>" class="w-full border px-3 py-2 rounded">
                    <span class="text-red-500 text-sm"><?= $descriptionMsg ?></span>
                </div>

                <div>
                    <label for="locationFound" class="block text-sm font-medium mb-1">Location Found:</label>
                    <input type="text" name="locationFound" value="<?= $locationFound ?>" class="w-full border px-3 py-2 rounded">
                    <span class="text-red-500 text-sm"><?= $locationFoundMsg ?></span>
                </div>

                <div>
                    <label for="pickUpLocation" class="block text-sm font-medium mb-1">Pick Up Location:</label>
                    <select name="pickUpLocation" class="w-full border px-3 py-2 rounded">
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
                    <span class="text-red-500 text-sm"><?= $pickUpLocationMsg ?></span>
                </div>

                <div>
                    <label for="dateFound" class="block text-sm font-medium mb-1">Date Found:</label>
                    <input type="date" name="dateFound" value="<?= $dateFound ?>" class="w-full border px-3 py-2 rounded">
                    <span class="text-red-500 text-sm"><?= $dateFoundMsg ?></span>
                </div>

                <div>
                    <label for="imagePath" class="block text-sm font-medium mb-1">Upload Image:</label>
                    <input type="file" name="imagePath" accept="image/*" class="w-full border px-3 py-2 rounded bg-white file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
                    <span class="text-red-500 text-sm"><?= $imagePathMsg ?></span>
                </div>

                <div>
                <label for="returneeName" class="block text-sm font-medium mb-1">Returnee Name:</label>
                <div class="flex items-center space-x-2">
                    <input type="text" name="returneeName" id="returneeName" value="<?= $returneeName ?>" class="w-full border px-3 py-2 rounded"/>
                    <select onchange="if(this.value === 'N/A') document.getElementById('returneeName').value = 'N/A';" class="border px-3 py-2 rounded text-sm">
                    <option value="">Select</option>
                    <option value="N/A">N/A</option>
                    </select>
                </div>
                </div>

                <input type="hidden" name="admin_ID" value="<?= isset($_SESSION['admin_ID']) ? $_SESSION['admin_ID'] : '' ?>">

                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">Submit</button>
            </form>

            <div class="flex justify-between mt-6 text-blue-600 text-sm">
                <a href="../adminFolder/adminDashBoard.php" class="hover:underline">back to Lost Items</a>
            </div>
        </div>
    </div>
</body>
</html>