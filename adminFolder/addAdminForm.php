<?php
include_once __DIR__ . '/../dbconn.php'; 
include_once '../authentication.php';
require_once '../imageUploadFuntion.php';
requireAdminLogin();

$masterConn = getMasterConnection();

$emailMsg = $passwordMsg = $nameMsg = $imagePathMsg = $departmentMsg = $questionMsg = $answerMsg = $employmentDateMsg = $payGradeMsg = $employeeIdMsg = "";
$email = $password = $name = $imagePath = $department = "";
$employmentDate = $payGrade = $employeeId = "";

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanText($_POST['email'] ?? '');
    $password = cleanText($_POST['password'] ?? '');
    $name = cleanText($_POST['name'] ?? '');
    $employmentDate = cleanText($_POST['employmentDate'] ?? '');
    $payGrade = cleanText($_POST['payGrade'] ?? '');
    $employeeId = cleanText($_POST['employeeId'] ?? '');
    $department = cleanText($_POST['department'] ?? '');

    $hasError = false;

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailMsg = "Please input a valid email.<br>";
        $hasError = true;
    } else {
        
        // Check admin table
        $stmt = $masterConn->prepare("SELECT email FROM admin WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $adminExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($adminExists) {
            $emailMsg = "❌ This email already exists in the system. Please use a different email.<br>";
            $hasError = true;
        }
    }

    if (empty($password)) {
        $passwordMsg = "Please input password.<br>";
        $hasError = true;
    }
    if (empty($name)) {
        $nameMsg = "Please input name.<br>";
        $hasError = true;
    }
    
    if (empty($department)) {
        $departmentMsg = "Please input department.<br>";
        $hasError = true;
    }
    if (empty($employmentDate)) {
        $employmentDateMsg = "❌ Please provide employment date<br>";
        $hasError = true;
    }
    if (empty($payGrade)) {
        $payGradeMsg = "❌ Please provide pay grade<br>";
        $hasError = true;
    }
    if (empty($employeeId)) {
        $employeeIdMsg = "❌ Please provide employee ID<br>";
        $hasError = true;
    }

        if (!$hasError) {
        $imagePath = uploadImage('imagePath', '../adminImages/', $imagePathMsg);
        if (!$imagePath) {
            $hasError = true;
        }
    }

    if (!$hasError) {
        $hashEmpDate = password_hash($employmentDate, PASSWORD_DEFAULT);
        $hashPayGrade = password_hash($payGrade, PASSWORD_DEFAULT);
        $hashEmployeeId = password_hash($employeeId, PASSWORD_DEFAULT);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
       
        $stmt = $masterConn->prepare("INSERT INTO admin (email, password, name, imagePath, department, employment_date_hash, pay_grade_hash, employee_id_hash) 
                                VALUES (:email, :password, :name, :imagePath, :department, :employmentDate, :payGrade, :employeeId)");
        $stmt->execute([
            ':email' => $email,
            ':password' => $hashedPassword,
            ':name' => $name,
            ':imagePath' => $imagePath,
            ':department' => $department,
            ':employmentDate' => $hashEmpDate,
            ':payGrade' => $hashPayGrade,
            ':employeeId' => $hashEmployeeId
        ]);

        header("Location: displayAdmin.php?msg=AdminAdded");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 text-gray-800 p-6">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl">
            <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-500 text-transparent bg-clip-text mb-8">
                Add Admin
            </h2>

        <?php include_once '../confirmation.php'; ?>

            <?= createConfirmedForm(
                htmlspecialchars($_SERVER['PHP_SELF']), 
                'POST', 
                'multipart/form-data',
                [
                    'message' => "Are you sure you want to add admin '{name}' with email '{email}'?",
                    'title' => 'Confirm Admin Creation',
                    'icon' => '👤',
                    'confirmText' => 'Yes, Add Admin',
                    'confirmClass' => 'bg-green-600 hover:bg-green-700',
                    'validateFields' => 'email,password,name,department,employmentDate,payGrade,employeeId',
                    'validateFiles' => 'imagePath'
                ]
            ) ?>
                <div>
                    <label class="block text-sm font-medium mb-1">Email:</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full border px-3 py-2 rounded" />
                    <span class="text-red-500 text-sm"><?= $emailMsg ?></span>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Password:</label>
                    <input type="password" id="password" name="password" value="<?= htmlspecialchars($password) ?>" class="w-full border px-3 py-2 rounded" />
                    <div class="flex items-center mt-1">
                        <input type="checkbox" id="showPassword" class="mr-2" />
                        <label for="showPassword" class="text-sm select-none">Show Password</label>
                    </div>
                    <span class="text-red-500 text-sm"><?= $passwordMsg ?></span>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="w-full border px-3 py-2 rounded" />
                    <span class="text-red-500 text-sm"><?= $nameMsg ?></span>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Upload Image:</label>
                    <input type="file" name="imagePath" accept="image/*" class="w-full border px-3 py-2 rounded" />
                    <span class="text-red-500 text-sm"><?= $imagePathMsg ?></span>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Department:</label>
                    <select name="department" class="w-full border px-3 py-2 rounded">
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
                    <span class="text-red-500 text-sm"><?= $departmentMsg ?></span>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Date of Employment:</label>
                    <input type="date" name="employmentDate" value="<?= htmlspecialchars($employmentDate) ?>" class="w-full border px-3 py-2 rounded" />
                    <span class="text-red-500 text-sm"><?= $employmentDateMsg ?></span>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Pay Grade:</label>
                    <input type="text" name="payGrade" value="<?= htmlspecialchars($payGrade) ?>" class="w-full border px-3 py-2 rounded" />
                    <span class="text-red-500 text-sm"><?= $payGradeMsg ?></span>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Employee ID:</label>
                    <input type="text" name="employeeId" value="<?= htmlspecialchars($employeeId) ?>" class="w-full border px-3 py-2 rounded" />
                    <span class="text-red-500 text-sm"><?= $employeeIdMsg ?></span>
                </div>

                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">Submit</button>
            </form>

            <div class="flex justify-between mt-6 text-blue-600 text-sm">
                <a href="displayAdmin.php" class="hover:underline">Display Admin</a>
                <a href="adminDashBoard.php" class="hover:underline">Home Dashboard</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('showPassword').addEventListener('change', function () {
            const passwordField = document.getElementById('password');
            passwordField.type = this.checked ? 'text' : 'password';
        });
    </script>
</body>
</html>
