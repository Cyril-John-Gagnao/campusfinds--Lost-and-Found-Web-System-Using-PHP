<?php
include_once __DIR__ . '/../dbconn.php'; 
include_once '../authentication.php';
include_once '../editImageFunction.php';
requireAdminLogin();

$masterConn = getMasterConnection();

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

$emailMsg = $nameMsg = $imagePathMsg = $departmentMsg = "";
$email = $name = $imagePath = $department = "";
$errorMsg = "";
$showForm = true; 

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['admin_ID'])) {
    $admin_ID = $_GET['admin_ID'];

    if ($_SESSION['admin_ID'] != $admin_ID) {
        $errorMsg = "Unauthorized access. You are not allowed to edit this profile.";
        $showForm = false;
    }

    if (empty($errorMsg)) {
        $stmt = $masterConn->prepare("SELECT * FROM admin WHERE admin_ID = :admin_ID");
        $stmt->execute([':admin_ID' => $admin_ID]);
        $result = $stmt->fetch();

        if ($result) {
            $email = $result['email'];
            $name = $result['name'];
            $imagePath = $result['imagePath'];
            $department = $result['department'];
        } else {
            $errorMsg = "Admin not found.";
            $showForm = false;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_ID = $_SESSION['admin_ID']; 
    
    $email = cleanText($_POST['email']);
    $name = cleanText($_POST['name']);
    $department = cleanText($_POST['department']);

    $employment_date_hash = cleanText($_POST['employment_date_hash']);
    $pay_grade_hash = cleanText($_POST['pay_grade_hash']);
    $employee_id_hash = cleanText($_POST['employee_id_hash']);

    $stmtOld = $masterConn->prepare("SELECT imagePath FROM admin WHERE admin_ID = :admin_ID");
    $stmtOld->execute([':admin_ID' => $_SESSION['admin_ID']]);
    $oldImageRow = $stmtOld->fetch();
    $oldImagePath = $oldImageRow ? $oldImageRow['imagePath'] : '';

    $imagePath = uploadEditedImage('image', '../adminImages/', $oldImagePath, $imagePathMsg);

    if (empty($email)) $emailMsg = "Please input correct email<br>";
    if (empty($name)) $nameMsg = "Please input name<br>";
    if (empty($imagePath)) $imagePathMsg = "Please upload an image<br>";
    if (empty($department)) $departmentMsg = "Please select department<br>";

    if (empty($emailMsg) && empty($nameMsg) && empty($imagePathMsg) && empty($departmentMsg)) {
        $updateSQL = "UPDATE admin SET 
            email = :email, 
            name = :name, 
            imagePath = :imagePath, 
            department = :department";

        $params = [
            ':email' => $email,
            ':name' => $name,
            ':imagePath' => $imagePath,
            ':department' => $department,
            ':admin_ID' => $_SESSION['admin_ID']
        ];

        if (!empty($employment_date_hash)) {
            $updateSQL .= ", employment_date_hash = :employment_date_hash";
            $params[':employment_date_hash'] = password_hash($employment_date_hash, PASSWORD_DEFAULT);
        }

        if (!empty($pay_grade_hash)) {
            $updateSQL .= ", pay_grade_hash = :pay_grade_hash";
            $params[':pay_grade_hash'] = password_hash($pay_grade_hash, PASSWORD_DEFAULT);
        }

        if (!empty($employee_id_hash)) {
            $updateSQL .= ", employee_id_hash = :employee_id_hash";
            $params[':employee_id_hash'] = password_hash($employee_id_hash, PASSWORD_DEFAULT);
        }
        
        $updateSQL .= " WHERE admin_ID = :admin_ID";

        $stmt = $masterConn->prepare($updateSQL);
        $stmt->execute($params);

        header("Location: displayAdmin.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
  <div class="max-w-4xl mx-auto bg-white p-10 shadow-md rounded-xl">
    <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-500 text-transparent bg-clip-text mb-8">Edit Admin</h2>

    <?php if (!empty($errorMsg)): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
          <strong class="font-semibold">Error: </strong>
          <span><?= htmlspecialchars($errorMsg) ?></span>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($showForm): ?>
      <?php include_once '../confirmation.php'; ?>

      <!-- Original form with confirmation -->
      <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" 
            class="grid grid-cols-1 md:grid-cols-2 gap-6"
            data-confirm="true"
            data-confirm-message="Are you sure you want to edit admin '{name}' with email '{email}'?"
            data-confirm-title="Confirm Admin Edit"
            data-confirm-icon="👤"
            data-confirm-text="Yes, Edit Admin"
            data-confirm-cancel="Cancel"
            data-confirm-class="bg-green-600 hover:bg-green-700"
            data-validate-fields="email,name,department">
            
        <div class="space-y-4">
          <div>
            <label for="">Email</label>
            <input type="email" name="email" value="<?= $email ?>" placeholder="Email" class="w-full px-4 py-2 border rounded-md" required>
            <p class="text-red-500 text-sm"><?= $emailMsg ?></p>
          </div>

          <div>
            <label for="">Name</label>
            <input type="text" name="name" value="<?= $name ?>" placeholder="Name" class="w-full px-4 py-2 border rounded-md" required>
            <p class="text-red-500 text-sm"><?= $nameMsg ?></p>
          </div>

          <div>
            <label for="">Department</label>
            <select name="department" class="w-full px-4 py-2 border rounded-md">
              <option value="">-- Select Department --</option>
              <optgroup label="Academic">
                <option value="Academic/CAS" <?= $department == 'Academic/CAS' ? 'selected' : '' ?>>CAS Admin</option>
                <option value="Academic/COC" <?= $department == 'Academic/COC' ? 'selected' : '' ?>>COC Admin</option>
                <option value="Academic/COD" <?= $department == 'Academic/COD' ? 'selected' : '' ?>>COD Admin</option>
                <option value="Academic/COL" <?= $department == 'Academic/COL' ? 'selected' : '' ?>>COL Admin</option>
                <option value="Academic/CICT" <?= $department == 'Academic/CICT' ? 'selected' : '' ?>>CICT Admin</option>
                <option value="Academic/CON" <?= $department == 'Academic/CON' ? 'selected' : '' ?>>CON Admin</option>
                <option value="Academic/PESCAR" <?= $department == 'Academic/PESCAR' ? 'selected' : '' ?>>PESCAR Admin</option>
                <option value="Academic/COE" <?= $department == 'Academic/COE' ? 'selected' : '' ?>>COE Admin</option>
                <option value="Academic/CBM" <?= $department == 'Academic/CBM' ? 'selected' : '' ?>>CBM Admin</option>
                <option value="Academic/COM" <?= $department == 'Academic/COM' ? 'selected' : '' ?>>COM Admin</option>
              </optgroup>
              <optgroup label="Non-Academic">
                <option value="Non-Academic/Library" <?= $department == 'Non-Academic/Library' ? 'selected' : '' ?>>Library Admin</option>
                <option value="Non-Academic/HR" <?= $department == 'Non-Academic/HR' ? 'selected' : '' ?>>HR Admin</option>
                <option value="Non-Academic/BAC" <?= $department == 'Non-Academic/BAC' ? 'selected' : '' ?>>BAC Admin</option>
                <option value="Non-Academic/OSA" <?= $department == 'Non-Academic/OSA' ? 'selected' : '' ?>>OSA Admin</option>
                <option value="Non-Academic/Guidance" <?= $department == 'Non-Academic/Guidance' ? 'selected' : '' ?>>Guidance Admin</option>
                <option value="Non-Academic/Clinic" <?= $department == 'Non-Academic/Clinic' ? 'selected' : '' ?>>Clinic Admin</option>
                <option value="Non-Academic/Admin" <?= $department == 'Non-Academic/Admin' ? 'selected' : '' ?>>Admin Admin</option>
                <option value="Non-Academic/Registrar" <?= $department == 'Non-Academic/Registrar' ? 'selected' : '' ?>>Registrar Admin</option>
              </optgroup>
            </select>
            <p class="text-red-500 text-sm"><?= $departmentMsg ?></p>
          </div>

          <div class="w-full border-t pt-5 mt-5">
            <h3 class="text-lg font-bold mb-3">Employment Details</h3>
            <p class="text-gray-600 text-sm mb-4">Optional — update individually as needed</p>
            
            <div class="space-y-4">
              <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">Employment Date:</label>
                <input type="date" name="employment_date_hash" value="<?= isset($_POST['employment_date_hash']) ? htmlspecialchars($_POST['employment_date_hash']) : '' ?>" placeholder="Date of Employment (optional)" class="w-full px-4 py-2 border rounded-md">
                <p class="text-xs text-gray-500 mt-1">Enter new value to update, leave blank to keep current</p>
              </div>
              
              <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">Pay Grade:</label>
                <input type="text" name="pay_grade_hash" value="<?= isset($_POST['pay_grade_hash']) ? htmlspecialchars($_POST['pay_grade_hash']) : '' ?>" placeholder="Pay Grade (optional)" class="w-full px-4 py-2 border rounded-md">
                <p class="text-xs text-gray-500 mt-1">Enter new value to update, leave blank to keep current</p>
              </div>
              
              <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">Employee ID:</label>
                <input type="text" name="employee_id_hash" value="<?= isset($_POST['employee_id_hash']) ? htmlspecialchars($_POST['employee_id_hash']) : '' ?>" placeholder="Employee ID (optional)" class="w-full px-4 py-2 border rounded-md">
                <p class="text-xs text-gray-500 mt-1">Enter new value to update, leave blank to keep current</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col items-center space-y-4">
          <?php if (!empty($imagePath) && file_exists($imagePath)): ?>
            <img src="<?= $imagePath ?>" alt="Admin Image" class="w-48 h-48 object-cover rounded-xl shadow-lg">
          <?php endif; ?>

          <div class="w-full">
            <label for="image" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg cursor-pointer hover:bg-blue-700 transition">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4-4-4m16 8l-4-4 4-4m-4 4H4"></path>
              </svg>
              Choose Image
              <input id="image" name="image" type="file" class="hidden">
            </label>
            <p class="text-red-500 text-sm mt-2"><?= $imagePathMsg ?></p>
          </div>
        </div>

        <div class="col-span-2">
          <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">Submit</button>
        </div>
      </form>
    <?php endif; ?>

    <div class="mt-4 text-center">
      <a href="displayAdmin.php" class="text-blue-500 hover:underline">Back to Admin List</a>
    </div>
  </div>
</body>
</html>