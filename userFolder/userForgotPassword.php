<?php
include_once __DIR__. '/../dbconn.php'; 

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
            $stmt = $masterConn->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $showAnswerForm = true;
                $_POST['email'] = $email;
            } else {
                $emailMsg = "Email not found.";
            }
        }
    } elseif (isset($_POST['step']) && $_POST['step'] == 'answer') {
        $email = cleanText($_POST['email']);
        $maiden = strtolower(trim($_POST['mother_maiden_name'])); 
        $lrn = strtolower(trim($_POST['lrn'])); 
        $studentId = strtolower(trim($_POST['student_id'])); 

        $stmt = $masterConn->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $allCorrect = true;
            $answersProvided = 0;
            // Check mother's maiden name if hash exists
            if (!empty($user['mother_maiden_name_hash'])) {
                $answersProvided++;
                if (!password_verify($maiden, $user['mother_maiden_name_hash'])) {
                    $allCorrect = false;
                }
            }
            // Check LRN if hash exists
            if (!empty($user['lrn_hash'])) {
                $answersProvided++;
                if (!password_verify($lrn, $user['lrn_hash'])) {
                    $allCorrect = false;
                }
            }
            
            // Check student ID if hash exists
            if (!empty($user['student_id_hash'])) {
                $answersProvided++;
                if (!password_verify($studentId, $user['student_id_hash'])) {
                    $allCorrect = false;
                }
            }
            
            // Only proceed if at least one security answer exists and all provided answers are correct
            if ($answersProvided > 0 && $allCorrect) {
                //  reset password
                $newPasswordPlain = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
                $newPasswordHash = password_hash($newPasswordPlain, PASSWORD_DEFAULT);

                $update = $masterConn->prepare("UPDATE user SET password = :password WHERE email = :email");
                $update->execute([':password' => $newPasswordHash, ':email' => $email]);

                $statusMsg = "✅ Password reset successful. Your new password is: <strong>$newPasswordPlain</strong>";
            } else {
                if ($answersProvided == 0) {
                    $answerMsg = "❌ No security questions are set up for this account. Please contact administrator.";
                } else {
                    $answerMsg = "❌ One or more answers are incorrect.";
                }
                $showAnswerForm = true;
            }
        } else {
            $answerMsg = "❌ User not found.";
            $showAnswerForm = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 text-gray-800 p-6">
<div class="flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-500 text-transparent bg-clip-text mb-8">Forgot Password</h2>
        
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
                    <label class="block text-sm font-medium mb-1">Mother's Maiden Name:</label>
                    <input type="text" name="mother_maiden_name" 
                        value="<?= htmlspecialchars($_POST['mother_maiden_name'] ?? '') ?>" 
                        class="w-full border px-3 py-2 rounded" 
                        placeholder="Enter if you set this up">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Learner Reference Number (LRN):</label>
                    <input type="text" name="lrn" 
                        value="<?= htmlspecialchars($_POST['lrn'] ?? '') ?>" 
                        class="w-full border px-3 py-2 rounded" 
                        placeholder="Enter if you set this up">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Student ID Number:</label>
                    <input type="text" name="student_id" 
                        value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" 
                        class="w-full border px-3 py-2 rounded" 
                        placeholder="Enter if you set this up">
                </div>

                <span class="text-red-500 text-sm block"><?= $answerMsg ?></span>

                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>

        <?php if (!empty($statusMsg)): ?>
            <div class="mt-6 p-4 bg-green-100 text-green-800 rounded-lg">
                <?= $statusMsg ?>
            </div>
        <?php endif; ?>
        
        <div class="flex justify-center mt-6 text-blue-600 text-sm">
            <a href="userlogin.php" class="hover:underline">Go back to Login</a>
        </div>
    </div>
</div>
</body>
</html>