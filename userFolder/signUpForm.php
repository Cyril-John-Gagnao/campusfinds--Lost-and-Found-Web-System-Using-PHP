<?php
session_start();
include_once __DIR__ . '/../dbconn.php'; 
require_once '../imageUploadFuntion.php';

$masterConn = getMasterConnection();

$emailMsg = $nameMsg = $imagePathMsg = $courseMsg = $yearMsg = $roleMsg = $departmentMsg = $questionMsg = $maidenNameMsg = $lrnMsg = $studentIdMsg = "";
$email = $name = $imagePath = $course = $year = $role = $department = $security_question = $security_answer = "";

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hasError = false;
    $email = cleanText($_POST['email']);

    $defaultPassword = 'wvsu2025';
    $name = cleanText($_POST['name']);
    $mother_maiden_name_hash = cleanText($_POST['mother_maiden_name_hash']);
    $lrn_hash = cleanText($_POST['lrn_hash']);
    $student_id_hash = cleanText($_POST['student_id_hash']);
    $course = cleanText($_POST['course']);
    $year = cleanText($_POST['year']);
    $role = cleanText($_POST['role']);
    $department = cleanText($_POST['department']);


    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailMsg = "Please input a valid email.<br>";
        $hasError = true;
    } else {
        
        // Check admin table
        $stmt = $masterConn->prepare("SELECT email FROM user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $userExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userExists) {
            $emailMsg = "❌ This email already exists in the system";
            $hasError = true;
        }
    }
    if (empty($name)) {
        $nameMsg = "❌ Please input user name<br>";
        $hasError = true;
    }
    if (empty($course)) {
        $courseMsg = "❌ Please select course<br>";
        $hasError = true;
    }
    if (empty($year)) {
        $yearMsg = "❌ Please select year level<br>";
        $hasError = true;
    }
    if (empty($role)) {
        $roleMsg = "❌ Please select user role<br>";
        $hasError = true;
    }
    if (empty($department)) {
        $departmentMsg = "❌ Please select department<br>";
        $hasError = true;
    }
    if (empty($_POST['mother_maiden_name_hash'])) {
        $maidenNameMsg .= "❌ Please provide Mother's Maiden Name<br>";
        $hasError = true;
    }
    if (empty($_POST['lrn_hash'])) {
        $lrnMsg .= "❌ Please provide LRN<br>";
        $hasError = true;
    }
    if (empty($_POST['student_id_hash'])) {
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

        $_SESSION['signup_success'] = "User successfully signed up!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Signup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-xl bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-semibold mb-6 text-center text-indigo-700">Student Signup</h2>

        <?php if (isset($_SESSION['signup_success'])): 
            $msg = htmlspecialchars($_SESSION['signup_success']);
            unset($_SESSION['signup_success']); ?>
            <div id="successPopup" 
                class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 
                       bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg z-50 min-w-[300px] text-center">
                <p><?= $msg ?></p>
                <button onclick="document.getElementById('successPopup').style.display='none'" 
                    class="mt-4 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded transition">
                    Close
                </button>
            </div>
        <?php endif; ?>

        <form id="signupForm" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Email Section -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    value="<?= htmlspecialchars($email ?? '') ?>" 
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"
                >
                <span class="text-red-600 text-sm"><?= htmlspecialchars($emailMsg ?? '') ?></span>
            </div>

            <div>
                <button type="button" onclick="validateEmail()" 
                    class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition">
                    Proceed
                </button>
            </div>

            <div id="restOfForm" style="display:none;" class="space-y-6">

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="flex items-center gap-4 mt-1">
                        <input 
                            type="text" 
                            name="password" 
                            id="password" 
                            value="wvsu2025" 
                            readonly
                            class="rounded-md border border-gray-300 p-2 w-36 bg-gray-100 cursor-not-allowed"
                        >
                        <span class="text-gray-500 text-sm">(Default password)</span>
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        value="<?= htmlspecialchars($name ?? '') ?>" 
                        class="mt-1 block w-full rounded-md border border-gray-300 p-2 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <span class="text-red-600 text-sm"><?= htmlspecialchars($nameMsg ?? '') ?></span>
                </div>

                <div>
                    <label for="imagePath" class="block text-sm font-medium text-gray-700">Upload Image</label>
                    <input 
                        type="file" 
                        name="imagePath" 
                        accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-600"
                    >
                    <span class="text-red-600 text-sm"><?= htmlspecialchars($imagePathMsg ?? '') ?></span>
                </div>

                <div>
                    <label for="course" class="block text-sm font-medium text-gray-700">Course</label>
                    <select 
                        name="course" 
                        class="mt-1 block w-full rounded-md border border-gray-300 p-2 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">--Select Course--</option>
                        <?php
                        $courses = ["ABELS", "ABFL", "ABPolSci", "BSAMath", "BSBio", "BSChem", "BSBA", "BSCM", "BSHM", "BSTM", "ABBroadcasting", "ABJournalism", "BSDC", "DDM", "BECE", "BSED-Elementary", "BSED-Secondary", "BSNE", "BSN", "BLIS", "BSCS", "BSEMC", "BSIS", "BSIT", "BCAEd", "BPA", "BPEd", "BSESS", "WEST Med", "N/A"];
                        foreach ($courses as $c) {
                            $selected = ($course ?? '') === $c ? 'selected' : '';
                            echo "<option value=\"$c\" $selected>$c</option>";
                        }
                        ?>
                    </select>
                    <span class="text-red-600 text-sm"><?= htmlspecialchars($courseMsg ?? '') ?></span>
                </div>

                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                    <select 
                        name="year" 
                        class="mt-1 block w-full rounded-md border border-gray-300 p-2 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">-- Select Year --</option>
                        <?php
                        $years = ["1st Year", "2nd Year", "3rd Year", "4th Year", "N/A"];
                        foreach ($years as $y) {
                            $selected = ($year ?? '') === $y ? 'selected' : '';
                            echo "<option value=\"$y\" $selected>$y</option>";
                        }
                        ?>
                    </select>
                    <span class="text-red-600 text-sm"><?= htmlspecialchars($yearMsg ?? '') ?></span>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select 
                        name="role" 
                        class="mt-1 block w-full rounded-md border border-gray-300 p-2 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">-- Select Role --</option>
                        <?php
                        $roles = ["Student", "Staff", "Faculty"];
                        foreach ($roles as $r) {
                            $selected = ($role ?? '') === $r ? 'selected' : '';
                            echo "<option value=\"$r\" $selected>$r</option>";
                        }
                        ?>
                    </select>
                    <span class="text-red-600 text-sm"><?= htmlspecialchars($roleMsg ?? '') ?></span>
                </div>

                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                    <select 
                        name="department" 
                        id="department" 
                        class="mt-1 block w-full rounded-md border border-gray-300 p-2 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">-- Select Department --</option>
                        <optgroup label="Academic">
                            <?php
                            $academic = ["Academic/CAS" => "CAS", "Academic/COC" => "COC", "Academic/COD" => "COD", "Academic/COL" => "COL", "Academic/CICT" => "CICT", "Academic/CON" => "CON", "Academic/PESCAR" => "PESCAR", "Academic/COE" => "COE", "Academic/CBM" => "CBM", "Academic/COM" => "COM"];
                            foreach ($academic as $val => $label) {
                                $selected = ($department ?? '') === $val ? 'selected' : '';
                                echo "<option value=\"$val\" $selected>$label</option>";
                            }
                            ?>
                        </optgroup>
                        <optgroup label="Non-Academic">
                            <?php
                            $nonAcademic = ["Non-Academic/Library" => "Library", "Non-Academic/HR" => "HR", "Non-Academic/BAC" => "BAC", "Non-Academic/OSA" => "OSA", "Non-Academic/Guidance" => "Guidance", "Non-Academic/Clinic" => "Clinic", "Non-Academic/Admin" => "Admin", "Non-Academic/Registrar" => "Registrar"];
                            foreach ($nonAcademic as $val => $label) {
                                $selected = ($department ?? '') === $val ? 'selected' : '';
                                echo "<option value=\"$val\" $selected>$label</option>";
                            }
                            ?>
                        </optgroup>
                    </select>
                    <span class="text-red-600 text-sm"><?= htmlspecialchars($departmentMsg ?? '') ?></span>
                </div>

                <h3 class="text-xl font-semibold text-indigo-700 mb-4">Security Questions (Required)</h3>

                <div>
                    <label for="mother_maiden_name_hash" class="block text-sm font-medium text-gray-700">Mother's Maiden Name</label>
                    <input 
                        type="text" 
                        name="mother_maiden_name_hash" 
                        value="<?= htmlspecialchars($_POST['mother_maiden_name_hash'] ?? '') ?>" 
                        class="mt-1 block w-full rounded-md border border-gray-300 p-2 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <span class="text-red-600 text-sm"><?= htmlspecialchars($maidenNameMsg ?? '') ?></span>
                </div>

                <div>
                    <label for="lrn_hash" class="block text-sm font-medium text-gray-700">Learner Reference Number (LRN)</label>
                    <input 
                        type="text" 
                        name="lrn_hash" 
                        value="<?= htmlspecialchars($_POST['lrn_hash'] ?? '') ?>" 
                        class="mt-1 block w-full rounded-md border border-gray-300 p-2 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <span class="text-red-600 text-sm"><?= htmlspecialchars($lrnMsg ?? '') ?></span>
                </div>

                <div>
                    <label for="student_id_hash" class="block text-sm font-medium text-gray-700">Student ID Number</label>
                    <input 
                        type="text" 
                        name="student_id_hash" 
                        value="<?= htmlspecialchars($_POST['student_id_hash'] ?? '') ?>" 
                        class="mt-1 block w-full rounded-md border border-gray-300 p-2 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <span class="text-red-600 text-sm"><?= htmlspecialchars($studentIdMsg ?? '') ?></span>
                </div>

                <div>
                    <input 
                        type="submit" 
                        value="Sign Up" 
                        class="w-full bg-indigo-600 text-white py-3 rounded hover:bg-indigo-700 cursor-pointer transition"
                    >
                </div>
            </div>
        </form>

        <p class="mt-6 text-center text-gray-600">
            <a href="userLogin.php" class="text-indigo-600 hover:text-indigo-800 font-medium">Back to Login</a>
        </p>
    </div>

    <script>
        function validateEmail() {
            const emailInput = document.getElementById('email');
            const email = emailInput.value.trim();
            const errorSpan = emailInput.nextElementSibling;

            const wvsuPattern = /^[a-zA-Z0-9._%+-]+@wvsu\.edu\.ph$/;

            if (!email) {
                errorSpan.innerText = "❌ Please enter an email address.";
            } else if (!wvsuPattern.test(email)) {
                errorSpan.innerText = "❌ your account is not valid";
            } else {
                errorSpan.innerText = "";
                document.getElementById('restOfForm').style.display = "block";
            }
        }

        // Show rest of form if POST submission or error messages exist
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') : ?>
            document.getElementById('restOfForm').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>
