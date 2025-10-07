<?php
include_once __DIR__ . '/../dbconn.php'; 

$masterConn = getMasterConnection();

$emailMsg = $statusMsg = $answerMsg = "";
$email = "";
$showAnswerForm = false;
$newPasswordPlain = "";

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == 'email') {
        $email = cleanText($_POST['email']);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailMsg = "Please enter a valid email.";
        } else {
            $stmt = $masterConn->prepare("SELECT * FROM admin WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                $showAnswerForm = true;
                $_POST['email'] = $email;
            } else {
                $emailMsg = "Email not found.";
            }
        }
    } elseif (isset($_POST['step']) && $_POST['step'] == 'answer') {
        $email = cleanText($_POST['email']);
        $emp_date = trim($_POST['employment_date']);
        $pay_grade = trim($_POST['pay_grade']);
        $emp_id = trim($_POST['employee_id']);

        $stmt = $masterConn->prepare("SELECT * FROM admin WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin &&
            password_verify($emp_date, $admin['employment_date_hash']) &&
            password_verify($pay_grade, $admin['pay_grade_hash']) &&
            password_verify($emp_id, $admin['employee_id_hash'])
        ) {
            $newPasswordPlain = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $newPasswordHash = password_hash($newPasswordPlain, PASSWORD_DEFAULT);

            $update = $masterConn->prepare("UPDATE admin SET password = :password WHERE email = :email");
            $update->execute([':password' => $newPasswordHash, ':email' => $email]);

            $statusMsg = "✅ Password reset successful. Your new password is: <strong>$newPasswordPlain</strong>";
        } else {
            $answerMsg = "❌ One or more answers are incorrect.";
            $showAnswerForm = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 text-gray-800 p-6">
<div class="flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl">
        <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-500 text-transparent bg-clip-text mb-8">Admin Forgot Password</h2>
        
        <?php if (!$showAnswerForm): ?>
            <form method="POST" action="" class="space-y-5">
                <input type="hidden" name="step" value="email">
                
                <div>
                    <label class="block text-sm font-medium mb-1">Email:</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full border px-3 py-2 rounded">
                    <span class="text-red-500 text-sm"><?= $emailMsg ?></span>
                </div>
                
                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">Next</button>
            </form>
        <?php else: ?>
            <form method="POST" action="" class="space-y-5">
                <input type="hidden" name="step" value="answer">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                <div>
                    <label class="block text-sm font-medium mb-1">Date of Employment:</label>
                    <input type="date" name="employment_date" 
                        value="<?= htmlspecialchars($_POST['employment_date'] ?? '') ?>" 
                        class="w-full border px-3 py-2 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Pay Grade:</label>
                    <input type="text" name="pay_grade" 
                        value="<?= htmlspecialchars($_POST['pay_grade'] ?? '') ?>" 
                        class="w-full border px-3 py-2 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Employee ID:</label>
                    <input type="text" name="employee_id" 
                        value="<?= htmlspecialchars($_POST['employee_id'] ?? '') ?>" 
                        class="w-full border px-3 py-2 rounded">
                    <span class="text-red-500 text-sm"><?= $answerMsg ?></span>
                </div>

                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>

        <?php if (!empty($statusMsg)): ?>
            <div class="mt-6 p-4 bg-green-50 text-green-700 rounded-lg">
                <?= $statusMsg ?>
            </div>
        <?php endif; ?>

        <div class="flex justify-center mt-6 text-blue-600 text-sm">
            <a href="adminlogin.php" class="hover:underline">Go back to Login</a>
        </div>
    </div>
</div>
</body>
</html>