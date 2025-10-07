<?php
session_start();
include_once __DIR__ . '/../dbconn.php'; 

$masterConn = getMasterConnection();

$emailMsg = $oldPassMsg = $newPassMsg = $confirmPassMsg = "";
$email = $oldPassword = $newPassword = $confirmPassword = "";

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanText($_POST['email']);
    $oldPassword = cleanText($_POST['old_password']);
    $newPassword = cleanText($_POST['new_password']);
    $confirmPassword = cleanText($_POST['confirm_password']);

    // Validate inputs
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailMsg = "Please enter a valid email<br>";
    }

    if (empty($oldPassword)) {
        $oldPassMsg = "Please enter your current password<br>";
    }

    if (empty($newPassword)) {
        $newPassMsg = "Please enter a new password<br>";
    }

    if ($newPassword !== $confirmPassword) {
        $confirmPassMsg = "Passwords do not match<br>";
    }

    // Proceed if no validation errors
    if (empty($emailMsg) && empty($oldPassMsg) && empty($newPassMsg) && empty($confirmPassMsg)) {
        $stmt = $masterConn->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($oldPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            // Update password
            $update = $masterConn->prepare("UPDATE user SET password = :new_password WHERE email = :email");
            $update->execute([
                ':new_password' => $hashedPassword, // use password_hash($newPassword, PASSWORD_DEFAULT) if using hashes
                ':email' => $email
            ]);

           // Set session success message and redirect to avoid resubmission
            $_SESSION['password_change_success'] = "Password successfully changed!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $oldPassMsg = "Incorrect current password or email<br>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 text-gray-800 p-6">
    <?php
    // Show success popup message if set
    if (isset($_SESSION['password_change_success'])) {
        $msg = htmlspecialchars($_SESSION['password_change_success']);
        unset($_SESSION['password_change_success']);
        echo "
        <div id='successPopup' class='fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 
            bg-green-100 text-green-800 p-6 border border-green-200 rounded-lg shadow-lg z-50 min-w-80 text-center'>
            <span class='text-lg font-medium'>$msg</span>
            <div class='mt-4'>
                <button onclick='document.getElementById(\"successPopup\").style.display=\"none\"' 
                    class='bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md cursor-pointer'>
                    Close
                </button>
            </div>
        </div>
        ";
    }
    ?>

<div class="flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl">
        <h2 class="text-4xl font-extrabold text-center bg-gradient-to-r from-blue-600 to-cyan-500 text-transparent bg-clip-text mb-8">Change Password</h2>
        
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="space-y-5">
            <div>
                <label class="block text-sm font-medium mb-1">Email:</label>
                <input type="text" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full border px-3 py-2 rounded">
                <span class="text-red-500 text-sm"><?= $emailMsg ?></span>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Current Password:</label>
                <div class="flex items-center">
                    <input type="password" name="old_password" id="old_password" class="w-full border px-3 py-2 rounded">
                    <div class="ml-2 flex items-center">
                        <input type="checkbox" id="show_old_password" onclick="togglePassword('old_password')" class="mr-1">
                        <label for="show_old_password" class="text-sm text-gray-600">Show</label>
                    </div>
                </div>
                <span class="text-red-500 text-sm"><?= $oldPassMsg ?></span>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">New Password:</label>
                <div class="flex items-center">
                    <input type="password" name="new_password" id="new_password" class="w-full border px-3 py-2 rounded">
                    <div class="ml-2 flex items-center">
                        <input type="checkbox" id="show_new_password" onclick="togglePassword('new_password')" class="mr-1">
                        <label for="show_new_password" class="text-sm text-gray-600">Show</label>
                    </div>
                </div>
                <span class="text-red-500 text-sm"><?= $newPassMsg ?></span>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Confirm New Password:</label>
                <div class="flex items-center">
                    <input type="password" name="confirm_password" id="confirm_password" class="w-full border px-3 py-2 rounded">
                    <div class="ml-2 flex items-center">
                        <input type="checkbox" id="show_confirm_password" onclick="togglePassword('confirm_password')" class="mr-1">
                        <label for="show_confirm_password" class="text-sm text-gray-600">Show</label>
                    </div>
                </div>
                <span class="text-red-500 text-sm"><?= $confirmPassMsg ?></span>
            </div>

            <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg">Change Password</button>
        </form>

        <div class="mt-6 text-center">
            <a href="userLogin.php" class="text-blue-600 hover:underline">Back to Login</a>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = (input.type === "password") ? "text" : "password";
}
</script>
</body>
</html>