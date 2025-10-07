<?php
session_start();
include_once __DIR__ . '/../dbconn.php'; 
$masterConn = getMasterConnection();

$passwordMsg = $emailMsg = "";
$password = $email = "";

function cleanText($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanText($_POST['email']);
    $password = cleanText($_POST['password']);

    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailMsg = "Please input a valid email<br>";
    }

    if (empty($password)) {
        $passwordMsg = "Please input password<br>";
    }
    // Authentication only if no validation errors
    if (empty($emailMsg) && empty($passwordMsg)) {
        $stmt = $masterConn->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $emailMsg = "Email not found<br>";
        } else {
            if (!password_verify($password, $user['password'])) {
                $passwordMsg = "Incorrect password<br>";
            } else {
                $_SESSION['user_ID'] = $user['user_ID'];
                $_SESSION['email'] = $user['email'];

                header("Location: userDashboard.php"); 
                exit();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="flex flex-col md:flex-row h-screen w-full">
    <!-- Left Side (Form) -->
    <div class="left-container w-full md:w-1/2 flex flex-col items-center justify-center bg-white">
        <div class="form-container w-full max-w-md px-4">
            <div class="text-center mb-6">
                <h1 class="logo-text mb-6 text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500 text-5xl font-extrabold">Campus Finds</h1>
            </div>
            <div class="flex justify-center mb-6">
                <div class="flex flex-col items-center">
                    <img src="../images/logo_campus_2.png" alt="Logo" class="h-16 w-26">
                    <h1 class="mt-2 text-blue-500 font-bold text-xl">User Log in</h1>
                </div>
            </div>

            <!-- Login Form -->
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="w-full mt-6">
                
                <div class="mb-2">
                    <input type="text" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>"
                           class="w-full px-4 py-3 rounded-md bg-gray-100 border border-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-400">
                    <?php if (!empty($emailMsg)): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $emailMsg ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-2 relative">
                    <input type="password" name="password" id="password" placeholder="Password"
                           value="<?= htmlspecialchars($password) ?>"
                           class="w-full px-4 py-3 rounded-md bg-gray-100 border border-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-400">
                    <button type="button" class="absolute right-3 top-3" onclick="togglePasswordVisibility()">
                        <i id="toggleIcon" class="fa-regular fa-eye-slash text-gray-500"></i>
                    </button>
                    <?php if (!empty($passwordMsg)): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $passwordMsg ?></p>
                    <?php endif; ?>
                </div>

                <div class="text-right mt-3 mb-6 flex justify-between text-sm text-blue-500">
                    <a href="userChangePassword.php" class="hover:underline">Change Password</a>
                    <a href="userForgotPassword.php" class="hover:underline">Forgot Password?</a>
                </div>

                <!-- Login Button -->
                <div class="mb-6 flex justify-center">
                    <button type="submit"
                            class="w-full md:w-[280px] py-2 bg-blue-500 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        Login
                    </button>
                </div>

                <!-- Separator -->
                <div class="flex items-center mb-6">
                    <div class="flex-grow h-px bg-gray-300"></div>
                    <div class="px-4 text-sm text-gray-500">Or with</div>
                    <div class="flex-grow h-px bg-gray-300"></div>
                </div>

               <!-- Sign Up -->
                <div class="flex justify-center items-center mb-4">
                    <div class="w-full md:w-auto text-center">
                        <a href="../userFolder/signUpForm.php" class="block w-full md:w-auto px-4 py-2 bg-blue-500 text-white font-medium rounded-md hover:bg-blue-600 transition-colors">
                            Sign Up
                        </a>
                        <span class="mt-2 text-xs text-blue-500 block">Don't have an account?</span>
                    </div>
                </div>

                <div class="text-center mt-6">
                    <a href="../index.php" class="text-sm font-medium text-blue-500 hover:underline">Back to Home</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Side (Image) -->
    <div class="right-container w-1/2 bg-blue-500 hidden lg:flex items-center justify-center">
        <div class="p-8 lg:p-28 text-white w-full h-full flex justify-center text-center">
            <div class="h-full w-full bg-opacity-30 rounded-full overflow-hidden">
                <img src="../images/user.gif" alt="Right Bg" class="h-full w-full object-cover">
            </div>
        </div>
    </div>
</div>

<!-- TOGGLE PASSWORD VISIBILITY FUNCTION -->
<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    }
</script>
</body>
</html>