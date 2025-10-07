<?php
include_once __DIR__ . '/../dbconn.php'; 
include_once '../authentication.php';
require_once '../imageUploadFuntion.php';
requireAdminLogin();

$masterConn = getMasterConnection();

$emailMsg = $nameMsg = $imagePathMsg = $courseMsg = $yearMsg = $roleMsg = $departmentMsg = $questionMsg = $maidenNameMsg = $lrnMsg = $studentIdMsg = "";
$email = $name = $imagePath = $course = $year = $role = $department = $security_question = $security_answer = "";

$mother_maiden_name = '';
$lrn = '';
$student_id = '';
$maidenNameMsg = '';
$lrnMsg = '';
$studentIdMsg = '';

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hasError = false;
    $email = cleanText($_POST['email']);
    $defaultPassword = 'wvsu2025';

    $name = cleanText($_POST['name']);
    $mother_maiden_name = cleanText($_POST['mother_maiden_name']);
    $lrn = cleanText($_POST['lrn']);
    $student_id = cleanText($_POST['student_id']);

    $mother_maiden_name_hash = password_hash($mother_maiden_name, PASSWORD_DEFAULT);
    $lrn_hash = password_hash($lrn, PASSWORD_DEFAULT);
    $student_id_hash = password_hash($student_id, PASSWORD_DEFAULT);

    $course= cleanText($_POST['course']);
    $year = cleanText($_POST['year']);
    $role = cleanText($_POST['role']);
    $department = cleanText($_POST['department']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailMsg = "Please input a valid email.<br>";
        $hasError = true;
    } else {
        
        // Check user table
        $stmt = $masterConn->prepare("SELECT email FROM user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $userExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userExists) {
            $emailMsg = "❌ This email already exists in the system. Please use a different email.";
            $hasError = true;
        }
    }
    
    if (empty($name)) {
        $nameMsg = "Please input user name<br>";
        $hasError = true;
    }
    if (empty($course)) {
        $courseMsg = "Please input course<br>";
        $hasError = true;
    }
    if (empty($year)) {
        $yearMsg = "Please input year of student<br>";
        $hasError = true;
    }
    if (empty($role)) {
        $roleMsg = "Please input user role<br>";
        $hasError = true;
    }
    if (empty($department)) {
        $departmentMsg = "Please input user department<br>";
        $hasError = true;
    }
   if (empty($mother_maiden_name)) {
    $maidenNameMsg .= "❌ Please provide Mother's Maiden Name<br>";
    $hasError = true;
    }
    if (empty($lrn)) {
        $lrnMsg .= "❌ Please provide LRN<br>";
        $hasError = true;
    }
    if (empty($student_id)) {
        $studentIdMsg .= "❌ Please provide Student ID<br>";
        $hasError = true;
    }

    if (!$hasError) {
        $imagePath = uploadImage('imagePath', '../userImages/', $imagePathMsg);
        if (!$imagePath) {
            $hasError = true;
        }
    }


    if (!$hasError) {
        $hashedPassword = password_hash("wvsu2025", PASSWORD_DEFAULT);

        $mother_maiden_name_hash = password_hash($mother_maiden_name_hash, PASSWORD_DEFAULT);
        $lrn_hash = password_hash($lrn_hash, PASSWORD_DEFAULT);
        $student_id_hash = password_hash($student_id_hash, PASSWORD_DEFAULT);

        $stmt = $masterConn->prepare("INSERT INTO user (email, password, name, imagePath, course, year, role, department, mother_maiden_name_hash, lrn_hash, student_id_hash)
                        VALUES (:email, :password, :name, :imagePath, :course, :year, :role, :department, :mother_maiden_name_hash, :lrn_hash, :student_id_hash)");
        $stmt->execute([
            ':email' => $email,
            ':password' => $hashedPassword,
            ':name' => $name,
            ':imagePath' => $imagePath,
            ':course' => $course,
            ':year' => $year,
            ':role' => $role,
            ':department' => $department,
            ':mother_maiden_name_hash' => $mother_maiden_name_hash,
            ':lrn_hash' => $lrn_hash,
            ':student_id_hash' => $student_id_hash
        ]);


        header("Location: displayUser.php?msg=UserAdded");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 text-gray-800 min-h-screen flex items-center justify-center p-6">
  <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl p-8">
    <h2 class="text-4xl font-extrabold text-center text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-cyan-500 mb-10">
      Add User
    </h2>

<?php include_once '../confirmation.php'; ?>
            <!-- Form using the helper function -->
            <?= createConfirmedForm(
                htmlspecialchars($_SERVER['PHP_SELF']), 
                'POST', 
                'multipart/form-data',
                [
                    'message' => "Are you sure you want to add user '{name}' with email '{email}'?",
                    'title' => 'Confirm User Creation',
                    'icon' => '👤',
                    'confirmText' => 'Yes, Add User',
                    'confirmClass' => 'bg-green-600 hover:bg-green-700',
                    'validateFields' => 'email,name,course,year,role,department',
                    'validateFiles' => 'imagePath'
                ]
            ) ?>
      
     
      <div>
        <label class="block font-medium mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-blue-400 focus:outline-none">
        <span class="text-red-500 text-sm"><?= $emailMsg ?></span>
      </div>

  
      <div>
        <label class="block font-medium mb-1">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="w-full border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-blue-400 focus:outline-none">
        <span class="text-red-500 text-sm"><?= $nameMsg ?></span>
      </div>

      
      <div>
        <label class="block font-medium mb-1">Upload Image</label>
        <input type="file" name="imagePath" accept="image/*" class="w-full border border-gray-300 px-4 py-2 rounded-md file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
        <span class="text-red-500 text-sm"><?= $imagePathMsg ?></span>
      </div>

      
      <div>
        <label class="block font-medium mb-1">Course</label>
        <select name="course" class="w-full border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-blue-400 focus:outline-none">
          <option value="">-- Select Course --</option>
          <?php
            $courses = ["ABELS", "ABFL", "ABPolSci", "BSAMath", "BSBio", "BSChem", "BSBA", "BSCM", "BSHM", "BSTM", "ABBroadcasting", "ABJournalism", "BSDC", "DDM", "BECE", "BSED-Elementary", "BSED-Secondary", "BSNE", "BSN", "BLIS", "BSCS", "BSEMC", "BSIS", "BSIT", "BCAEd", "BPA", "BPEd", "BSESS", "WEST Med", "N/A"];
            foreach ($courses as $c) {
              echo "<option value=\"$c\" " . ($course == $c ? "selected" : "") . ">$c</option>";
            }
          ?>
        </select>
        <span class="text-red-500 text-sm"><?= $courseMsg ?></span>
      </div>

      
      <div>
        <label class="block font-medium mb-1">Year</label>
        <select name="year" class="w-full border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-blue-400 focus:outline-none">
          <option value="">-- Select Year --</option>
          <?php
            $years = ['1st Year', '2nd Year', '3rd Year', '4th Year', 'N/A'];
            foreach ($years as $y) {
              echo "<option value=\"$y\" " . ($year == $y ? "selected" : "") . ">$y</option>";
            }
          ?>
        </select>
        <span class="text-red-500 text-sm"><?= $yearMsg ?></span>
      </div>

      
      <div>
        <label class="block font-medium mb-1">Role</label>
        <select name="role" class="w-full border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-blue-400 focus:outline-none">
          <option value="">-- Select Role --</option>
          <?php
            $roles = ['Student', 'non-teaching', 'Faculty'];
            foreach ($roles as $r) {
              echo "<option value=\"$r\" " . ($role == $r ? "selected" : "") . ">$r</option>";
            }
          ?>
        </select>
        <span class="text-red-500 text-sm"><?= $roleMsg ?></span>
      </div>

      
      <div>
        <label class="block font-medium mb-1">Department</label>
        <select name="department" class="w-full border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-blue-400 focus:outline-none">
          <option value="">-- Select Department --</option>
          <optgroup label="Academic">
            <?php
              $acad = ['Academic/CAS' => 'CAS', 'Academic/COC' => 'COC', 'Academic/COD' => 'COD', 'Academic/COL' => 'COL', 'Academic/CICT' => 'CICT', 'Academic/CON' => 'CON', 'Academic/PESCAR' => 'PESCAR', 'Academic/COE' => 'COE', 'Academic/CBM' => 'CBM', 'Academic/COM' => 'COM'];
              foreach ($acad as $val => $label) {
                echo "<option value=\"$val\" " . ($department == $val ? "selected" : "") . ">$label</option>";
              }
            ?>
          </optgroup>
          <optgroup label="Non-Academic">
            <?php
              $nonAcad = ['Non-Academic/Library' => 'Library', 'Non-Academic/HR' => 'HR', 'Non-Academic/BAC' => 'BAC', 'Non-Academic/OSA' => 'OSA', 'Non-Academic/Guidance' => 'Guidance', 'Non-Academic/Clinic' => 'Clinic', 'Non-Academic/Admin' => 'Admin', 'Non-Academic/Registrar' => 'Registrar'];
              foreach ($nonAcad as $val => $label) {
                echo "<option value=\"$val\" " . ($department == $val ? "selected" : "") . ">$label</option>";
              }
            ?>
          </optgroup>
        </select>
        <span class="text-red-500 text-sm"><?= $departmentMsg ?></span>
      </div>

      <!-- Security Questions -->

      <div class="pt-2">
        <label class="block text-lg font-semibold mb-2 text-blue-800">Security Questions (Required)</label>
        
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Mother's Maiden Name</label>
          <input type="text" name="mother_maiden_name" value="<?= htmlspecialchars($mother_maiden_name) ?>" class="w-full border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-blue-400 focus:outline-none">
          <span class="text-red-500 text-sm"><?= empty($mother_maiden_name) ? $maidenNameMsg : '' ?></span>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Learner Reference Number (LRN)</label>
          <input type="text" name="lrn" value="<?= htmlspecialchars($lrn) ?>" class="w-full border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-blue-400 focus:outline-none">
          <span class="text-red-500 text-sm"><?= empty($lrn) ? $lrnMsg : '' ?></span>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Student ID Number</label>
          <input type="text" name="student_id" value="<?= htmlspecialchars($student_id) ?>" class="w-full border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-blue-400 focus:outline-none">
          <span class="text-red-500 text-sm"><?= empty($student_id) ? $studentIdMsg : '' ?></span><br><br>
        </div>

      
      <div>
        <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg transition">
          Add User
        </button>
      </div>
    </form>

    
    <div class="flex justify-between mt-8 text-blue-700 text-sm">
      <a href="displayUser.php" class="hover:underline">Display Users</a>
    </div>
  </div>
</body>
</html>
