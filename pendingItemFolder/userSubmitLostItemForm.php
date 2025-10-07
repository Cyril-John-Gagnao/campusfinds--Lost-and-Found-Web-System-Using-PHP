<?php
include_once __DIR__ . '/../dbconn.php'; 
include_once '../authentication.php';
require_once '../imageUploadFuntion.php';
requireLogin();

$masterConn = getMasterConnection();

// Check login
if (!isset($_SESSION['email'])) {
    header("Location: ../iserFolder/userLogin.php");
    exit();
}

$item_name_msg = $description_msg = $location_found_msg = $pick_up_location_msg = $date_found_msg = $image_path_msg = "";
$itemName = $description = $locationFound = $pickUpLocation = $dateFound = $imagePath = $returneeName = "";
$hasError = false;
$show_errors = false;

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['from_dashboard'])) {
    $show_errors = true;
    $itemName = cleanText($_POST['itemName'] ?? '');
    $description = cleanText($_POST['description'] ?? '');
    $locationFound = cleanText($_POST['locationFound'] ?? '');
    $pickUpLocation = cleanText($_POST['pickUpLocation'] ?? '');
    $dateFound = cleanText($_POST['dateFound'] ?? '');
    $returneeName = cleanText($_POST['returneeName'] ?? '');
    if (empty($returneeName)) {
        $returneeName = "N/A";
    }

    if (empty($itemName)) {
        $item_name_msg = "Please input item name<br>";
        $hasError = true;
    }
    if (empty($description)) {
        $description_msg = "Please input description<br>";
        $hasError = true;
    }
    if (empty($locationFound)) {
        $location_found_msg = "Please input location found<br>";
        $hasError = true;
    }
    if (empty($pickUpLocation)) {
        $pick_up_location_msg = "Please input pickup location<br>";
        $hasError = true;
    }
    if (empty($dateFound)) {
        $date_found_msg = "Please input date found<br>";
        $hasError = true;
    }

    if (!$hasError) {
        $imagePath = uploadImage('imagePath', '../pendingLostImages/', $imagePathMsg);
        if (!$imagePath) {
            $hasError = true;
        }
    }

    if (!$hasError) {
        try {
           $stmt = $masterConn->prepare("INSERT INTO lost_items 
            (user_ID, itemName, description, locationFound, pickUpLocation, dateFound, imagePath, returneeName, status) 
            VALUES (:user_ID, :itemName, :description, :locationFound, :pickUpLocation, :dateFound, :imagePath, :returneeName, :status)");

            $stmt->execute([
                ':user_ID' => $_SESSION['user_ID'],
                ':itemName' => $itemName,
                ':description' => $description,
                ':locationFound' => $locationFound,
                ':pickUpLocation' => $pickUpLocation,
                ':dateFound' => $dateFound,
                ':imagePath' => $imagePath,
                ':returneeName' => $returneeName,
                ':status' => 'pending'
            ]);

            $_SESSION['success_msg'] = "Lost item submitted successfully and is pending admin approval.";
            header("Location: usersubmitlostitemform.php");
            exit();
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Lost Item</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 text-gray-800 p-6">
    <?php
    if (isset($_SESSION['success_msg'])) {
        $msg = htmlspecialchars($_SESSION['success_msg']);
        unset($_SESSION['success_msg']);
        echo "
        <div id='successPopup' class='fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2
            bg-green-100 text-green-800 px-6 py-4 rounded-lg shadow-lg z-50 text-center'>
            <span>$msg</span>
            <br><br>
            <button onclick='document.getElementById(\"successPopup\").style.display=\"none\"' 
                class='bg-green-700 hover:bg-green-800 text-white py-2 px-4 rounded'>
                Close
            </button>
        </div>
        ";
    }
    ?>

    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl">
            <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-500 text-transparent bg-clip-text mb-8">Report a Lost Item</h2>

<?php include_once '../confirmation.php'; ?>

            <?= createConfirmedForm(
                htmlspecialchars($_SERVER['PHP_SELF']), 
                'POST', 
                'multipart/form-data',
                [
                    'message' => "Are you sure you want to report this lost item?",
                    'title' => 'Confirm Report Creation',
                    'icon' => '👤',
                    'confirmText' => 'Yes, Report Lost Item',
                    'confirmClass' => 'bg-green-600 hover:bg-green-700',
                    'validateFields' => 'itemName,description,locationFound,pickUpLocation,dateFound,returneeName',
                    'validateFiles' => 'imagePath'
                ]
            ) ?>
                <div>
                    <label for="itemName" class="block text-sm font-medium mb-1">Item Name:</label>
                    <input type="text" name="itemName" value="<?= htmlspecialchars($itemName) ?>" class="w-full border px-3 py-2 rounded">
                    <?php if ($show_errors) : ?>
                        <span class="text-red-500 text-sm"><?= $item_name_msg ?></span>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium mb-1">Description:</label>
                    <textarea name="description" class="w-full border px-3 py-2 rounded resize-y h-28"><?= htmlspecialchars($description) ?></textarea>
                    <?php if ($show_errors) : ?>
                        <span class="text-red-500 text-sm"><?= $description_msg ?></span>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="locationFound" class="block text-sm font-medium mb-1">Location Found:</label>
                    <input type="text" name="locationFound" value="<?= htmlspecialchars($locationFound) ?>" class="w-full border px-3 py-2 rounded">
                    <?php if ($show_errors) : ?>
                        <span class="text-red-500 text-sm"><?= $location_found_msg ?></span>
                    <?php endif; ?>
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
                    <?php if ($show_errors) : ?>
                        <span class="text-red-500 text-sm"><?= $pick_up_location_msg ?></span>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="dateFound" class="block text-sm font-medium mb-1">Date Found:</label>
                    <input type="date" name="dateFound" value="<?= htmlspecialchars($dateFound) ?>" class="w-full border px-3 py-2 rounded">
                    <?php if ($show_errors) : ?>
                        <span class="text-red-500 text-sm"><?= $date_found_msg ?></span>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="imagePath" class="block text-sm font-medium mb-1">Upload Image:</label>
                    <input type="file" name="imagePath" accept="image/*" class="w-full border px-3 py-2 rounded bg-white file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
                    <?php if ($show_errors) : ?>
                        <span class="text-red-500 text-sm"><?= $image_path_msg ?></span>
                    <?php endif; ?>
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

                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">Submit Lost Item</button>
            </form>

            <div class="flex justify-between mt-6 text-blue-600 text-sm">
                <a href="../userFolder/userDashboard.php" class="hover:underline">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("lostItemForm").addEventListener("submit", function(event) {
            const confirmSubmit = confirm("Do you want to submit this lost item?");
            if (!confirmSubmit) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>