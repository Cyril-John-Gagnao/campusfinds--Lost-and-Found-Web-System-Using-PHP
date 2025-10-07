<?php
session_start();
include_once __DIR__. '/../dbconn.php'; 
include_once '../editImageFunction.php';

$masterConn = getMasterConnection();

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

$emailMsg = $nameMsg = $imagePathMsg = $courseMsg = $yearMsg = $roleMsg = $departmentMsg = "";
$email = $name = $imagePath = $course = $year = $role = $department = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['user_ID'])) {
    $user_ID = $_GET['user_ID'];

    $stmt = $masterConn->prepare("SELECT * FROM user WHERE user_ID = :user_ID");
    $stmt->execute([':user_ID' => $user_ID]);
    $result = $stmt->fetch();

    if ($result) {
        $_SESSION['user_ID'] = $result['user_ID'];
        $email = $result['email'];
        $name = $result['name'];
        $imagePath = $result['imagePath'];
        $course = $result['course'];
        $year = $result['year'];
        $role = $result['role'];
        $department = $result['department'];
    } else {
        echo "User not found.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanText($_POST['email']);
    $name = cleanText($_POST['name']);
    $course = cleanText($_POST['course']);
    $year = cleanText($_POST['year']);
    $role = cleanText($_POST['role']);
    $department = cleanText($_POST['department']);
    $mother_maiden_name = cleanText($_POST['mother_maiden_name']);
    $lrn = cleanText($_POST['lrn']);
    $student_id = cleanText($_POST['student_id']);

    $stmtOld = $masterConn->prepare("SELECT imagePath FROM user WHERE user_ID = :user_ID");
    $stmtOld->execute([':user_ID' => $_SESSION['user_ID']]);
    $oldImageRow = $stmtOld->fetch();
    $oldImagePath = $oldImageRow ? $oldImageRow['imagePath'] : '';

    $imagePath = uploadEditedImage('image', '../userImages/', $oldImagePath, $imagePathMsg);

    if (empty($email)) $emailMsg = "Please input a valid email<br>";
    if (empty($name)) $nameMsg = "Please input name<br>";
    if (empty($imagePath)) $imagePathMsg = "Please input image<br>";
    if (empty($course)) $courseMsg = "Please input your course<br>";
    if (empty($year)) $yearMsg = "Please input year<br>";
    if (empty($role)) $roleMsg = "Please input role<br>";
    if (empty($department)) $departmentMsg = "Please input department<br>";

    if (empty($emailMsg) && empty($nameMsg) && empty($imagePathMsg) && empty($courseMsg) && empty($yearMsg) && empty($roleMsg) && empty($departmentMsg)) {
        
        $updateSQL = "UPDATE user SET email = :email, name = :name, imagePath = :imagePath, course = :course, year = :year, role = :role, department = :department";
        
        $params = [
            ':email' => $email,
            ':name' => $name,
            ':imagePath' => $imagePath,
            ':course' => $course,
            ':year' => $year,
            ':role' => $role,
            ':department' => $department,
            ':user_ID' => $_SESSION['user_ID']
        ];

        // Add security fields to update if they are provided
        if (!empty($mother_maiden_name)) {
            $updateSQL .= ", mother_maiden_name_hash = :mother_maiden_name_hash";
            $params[':mother_maiden_name_hash'] = password_hash(strtolower($mother_maiden_name), PASSWORD_DEFAULT);
        }

        if (!empty($lrn)) {
            $updateSQL .= ", lrn_hash = :lrn_hash";
            $params[':lrn_hash'] = password_hash(strtolower($lrn), PASSWORD_DEFAULT);
        }

        if (!empty($student_id)) {
            $updateSQL .= ", student_id_hash = :student_id_hash";
            $params[':student_id_hash'] = password_hash(strtolower($student_id), PASSWORD_DEFAULT);
        }

        // Complete the query and execute
        $updateSQL .= " WHERE user_ID = :user_ID";
        $stmt = $masterConn->prepare($updateSQL);
        $stmt->execute($params);

        header("Location: displayUser.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit User</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
  <div class="max-w-4xl mx-auto bg-white p-10 shadow-md rounded-xl">
    <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-500 text-transparent bg-clip-text mb-8">Edit User</h2>
    
  <?php include_once '../confirmation.php'; ?>

      <!-- Original form with confirmation -->
      <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" 
            class="grid grid-cols-1 md:grid-cols-2 gap-6"
            data-confirm="true"
            data-confirm-message="Are you sure you want to edit this user?"
            data-confirm-title="Confirm User Edit"
            data-confirm-icon="👤"
            data-confirm-text="Yes, Edit User"
            data-confirm-cancel="Cancel"
            data-confirm-class="bg-green-600 hover:bg-green-700"
            data-validate-fields="email,name,course,year,role,department">
      <!-- Left Column -->
      <div class="space-y-4">
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-1">Email:</label>
          <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" class="w-full px-4 py-2 border rounded-md" required>
          <p class="text-red-500 text-sm"><?= $emailMsg ?></p>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-1">Name:</label>
          <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Name" class="w-full px-4 py-2 border rounded-md" required>
          <p class="text-red-500 text-sm"><?= $nameMsg ?></p>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-1">Course:</label>
          <select name="course" class="w-full px-4 py-2 border rounded-md">
            <option value="">--Select Course</option>
            <option value="ABELS" <?= $course == 'ABELS' ? 'selected' : '' ?>>ABELS</option>
            <option value="ABFL" <?= $course == 'ABFL' ? 'selected' : '' ?>>ABFL</option>
            <option value="ABPolSci" <?= $course == 'ABPolSci' ? 'selected' : '' ?>>ABPolSci</option>
            <option value="BSAMath" <?= $course == 'BSAMath' ? 'selected' : '' ?>>BSAMath</option>
            <option value="BSBio" <?= $course == 'BSBio' ? 'selected' : '' ?>>BSBio</option>
            <option value="BSChem" <?= $course == 'BSChem' ? 'selected' : '' ?>>BSChem</option>
            <option value="BSBA" <?= $course == 'BSBA' ? 'selected' : '' ?>>BSBA</option>
            <option value="BSCM" <?= $course == 'BSCM' ? 'selected' : '' ?>>BSCM</option>
            <option value="BSHM" <?= $course == 'BSHM' ? 'selected' : '' ?>>BSHM</option>
            <option value="BSTM" <?= $course == 'BSTM' ? 'selected' : '' ?>>BSTM</option>
            <option value="ABBroadcasting" <?= $course == 'ABBroadcasting' ? 'selected' : '' ?>>ABBroadcasting</option>
            <option value="ABJournalism" <?= $course == 'ABJournalism' ? 'selected' : '' ?>>ABJournalism</option>
            <option value="BSDC" <?= $course == 'BSDC' ? 'selected' : '' ?>>BSDC</option>
            <option value="DDM" <?= $course == 'DDM' ? 'selected' : '' ?>>DDM</option>
            <option value="BECE" <?= $course == 'BECE' ? 'selected' : '' ?>>BECE</option>
            <option value="BSED-Elementary" <?= $course == 'BSED-Elementary' ? 'selected' : '' ?>>BSED-Elementary</option>
            <option value="BSED-Secondary" <?= $course == 'BSED-Secondary' ? 'selected' : '' ?>>BSED-Secondary</option>
            <option value="BSNE" <?= $course == 'BSNE' ? 'selected' : '' ?>>BSNE</option>
            <option value="BSN" <?= $course == 'BSN' ? 'selected' : '' ?>>BSN</option>
            <option value="BLIS" <?= $course == 'BLIS' ? 'selected' : '' ?>>BLIS</option>
            <option value="BSCS" <?= $course == 'BSCS' ? 'selected' : '' ?>>BSCS</option>
            <option value="BSEMC" <?= $course == 'BSEMC' ? 'selected' : '' ?>>BSEMC</option>
            <option value="BSIS" <?= $course == 'BSIS' ? 'selected' : '' ?>>BSIS</option>
            <option value="BSIT" <?= $course == 'BSIT' ? 'selected' : '' ?>>BSIT</option>
            <option value="BCAEd" <?= $course == 'BCAEd' ? 'selected' : '' ?>>BCAEd</option>
            <option value="BPA" <?= $course == 'BPA' ? 'selected' : '' ?>>BPA</option>
            <option value="BPEd" <?= $course == 'BPEd' ? 'selected' : '' ?>>BPEd</option>
            <option value="BSESS" <?= $course == 'BSESS' ? 'selected' : '' ?>>BSESS</option>
            <option value="WEST Med" <?= $course == 'WEST Med' ? 'selected' : '' ?>>WEST Med</option>
            <option value="N/A" <?= $course == 'N/A' ? 'selected' : '' ?>>N/A</option>
          </select>
          <p class="text-red-500 text-sm"><?= $courseMsg ?></p>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-1">Year:</label>
          <select name="year" class="w-full px-4 py-2 border rounded-md">
            <option value="">-- Select Year --</option>
            <option value="1st Year" <?= $year == '1st Year' ? 'selected' : '' ?>>1st Year</option>
            <option value="2nd Year" <?= $year == '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
            <option value="3rd Year" <?= $year == '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
            <option value="4th Year" <?= $year == '4th Year' ? 'selected' : '' ?>>4th Year</option>
            <option value="N/A" <?= $year == 'N/A' ? 'selected' : '' ?>>N/A</option>
          </select>
          <p class="text-red-500 text-sm"><?= $yearMsg ?></p>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-1">Role:</label>
          <select name="role" class="w-full px-4 py-2 border rounded-md">
            <option value="">-- Select Role --</option>
            <option value="Student" <?= $role == 'Student' ? 'selected' : '' ?>>Student</option>
            <option value="Faculty" <?= $role == 'Faculty' ? 'selected' : '' ?>>Faculty</option>
            <option value="Non-Teaching" <?= $role == 'Non-Teaching' ? 'selected' : '' ?>>Non-Teaching</option>
          </select>
          <p class="text-red-500 text-sm"><?= $roleMsg ?></p>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-1">Department:</label>
          <select name="department" id="department" class="w-full px-4 py-2 border rounded-md">
            <option value="">-- Select Department --</option>

            <optgroup label="Academic">
                <option value="Academic/CAS" <?= $department == 'Academic/CAS' ? 'selected' : '' ?>>CAS</option>
                <option value="Academic/COC" <?= $department == 'Academic/COC' ? 'selected' : '' ?>>COC</option>
                <option value="Academic/COD" <?= $department == 'Academic/COD' ? 'selected' : '' ?>>COD</option>
                <option value="Academic/COL" <?= $department == 'Academic/COL' ? 'selected' : '' ?>>COL</option>
                <option value="Academic/CICT" <?= $department == 'Academic/CICT' ? 'selected' : '' ?>>CICT</option>
                <option value="Academic/CON" <?= $department == 'Academic/CON' ? 'selected' : '' ?>>CON</option>
                <option value="Academic/PESCAR" <?= $department == 'Academic/PESCAR' ? 'selected' : '' ?>>PESCAR</option>
                <option value="Academic/COE" <?= $department == 'Academic/COE' ? 'selected' : '' ?>>COE</option>
                <option value="Academic/CBM" <?= $department == 'Academic/CBM' ? 'selected' : '' ?>>CBM</option>
                <option value="Academic/COM" <?= $department == 'Academic/COM' ? 'selected' : '' ?>>COM</option>
            </optgroup>

            <optgroup label="Non-Academic">
                <option value="Non-Academic/Library" <?= $department == 'Non-Academic/Library' ? 'selected' : '' ?>>Library</option>
                <option value="Non-Academic/HR" <?= $department == 'Non-Academic/HR' ? 'selected' : '' ?>>HR</option>
                <option value="Non-Academic/BAC" <?= $department == 'Non-Academic/BAC' ? 'selected' : '' ?>>BAC</option>
                <option value="Non-Academic/OSA" <?= $department == 'Non-Academic/OSA' ? 'selected' : '' ?>>OSA</option>
                <option value="Non-Academic/Guidance" <?= $department == 'Non-Academic/Guidance' ? 'selected' : '' ?>>Guidance</option>
                <option value="Non-Academic/Clinic" <?= $department == 'Non-Academic/Clinic' ? 'selected' : '' ?>>Clinic</option>
                <option value="Non-Academic/Admin" <?= $department == 'Non-Academic/Admin' ? 'selected' : '' ?>>Admin</option>
                <option value="Non-Academic/Registrar" <?= $department == 'Non-Academic/Registrar' ? 'selected' : '' ?>>Registrar</option>
            </optgroup>
          </select>
          <p class="text-red-500 text-sm"><?= $departmentMsg ?></p>
        </div>
      </div>

      <!-- Right Column -->
      <div class="flex flex-col items-center space-y-6">
        <div class="w-full text-center">
          <label class="block text-gray-700 text-sm font-medium mb-2">Current Image:</label>
          <?php if (!empty($imagePath) && file_exists($imagePath)): ?>
            <img src="<?= htmlspecialchars($imagePath) ?>" alt="User Image" class="w-48 h-48 object-cover rounded-xl shadow-lg mx-auto mb-4">
          <?php endif; ?>

          <label for="image" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg cursor-pointer hover:bg-blue-700 transition mx-auto">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4-4-4m16 8l-4-4 4-4m-4 4H4"></path>
            </svg>
            Choose Image
            <input id="image" name="image" type="file" class="hidden">
          </label>
          <p class="text-red-500 text-sm mt-2"><?= $imagePathMsg ?></p>
        </div>

        <!-- Security Questions Section -->
        <div class="w-full border-t pt-5 mt-5">
          <h3 class="text-lg font-bold mb-3">Security Questions</h3>
          <p class="text-gray-600 text-sm mb-4">Optional — update individually as needed</p>
          
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1">Mother's Maiden Name:</label>
              <input type="text" name="mother_maiden_name" value="<?= isset($_POST['mother_maiden_name']) ? htmlspecialchars($_POST['mother_maiden_name']) : '' ?>" placeholder="Leave blank to keep current" class="w-full px-4 py-2 border rounded-md">
              <p class="text-xs text-gray-500 mt-1">Enter new value to update, leave blank to keep current</p>
            </div>
            
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1">LRN:</label>
              <input type="text" name="lrn" value="<?= isset($_POST['lrn']) ? htmlspecialchars($_POST['lrn']) : '' ?>" placeholder="Leave blank to keep current" class="w-full px-4 py-2 border rounded-md">
              <p class="text-xs text-gray-500 mt-1">Enter new value to update, leave blank to keep current</p>
            </div>
            
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-1">Student ID:</label>
              <input type="text" name="student_id" value="<?= isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : '' ?>" placeholder="Leave blank to keep current" class="w-full px-4 py-2 border rounded-md">
              <p class="text-xs text-gray-500 mt-1">Enter new value to update, leave blank to keep current</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Submit Button - Full Width -->
      <div class="col-span-1 md:col-span-2 mt-4">
        <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">Update User</button>
      </div>
    </form>

    <div class="mt-4 text-center">
      <a href="displayUser.php" class="text-blue-500 hover:underline">Back to User List</a>
    </div>
  </div>
</body>
</html>