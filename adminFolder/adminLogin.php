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
    $email = cleanText($_POST['email'] ?? '');
    $password = cleanText($_POST['password'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailMsg = "Please input a valid email<br>";
    }

    if (empty($password)) {
        $passwordMsg = "Please input password<br>";
    }

    if (empty($emailMsg) && empty($passwordMsg)) {
        $stmt = $masterConn->prepare("SELECT * FROM admin WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            $emailMsg = "Email not found<br>";
        } else {
            if (!password_verify($password, $admin['password'])) {
                $passwordMsg = "Incorrect password<br>";
            } else {
                // Login success
                $_SESSION['admin_ID'] = $admin['admin_ID'];
                $_SESSION['email'] = $admin['email'];

                header("Location: adminDashboard.php");
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
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="flex h-screen w-full">
    <!-- Left side - Login Form -->
    <div class="left-side w-full lg:w-1/2 flex flex-col items-center justify-center bg-white">
        <div class="w-full max-w-md px-4">
            <div class="mb-6 text-center">
                <h1 class="campus-finds-text mb-12 text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500 text-6xl font-extrabold">Campus Finds</h1>
            </div>
            <div class="flex justify-center mb-6">
                <div class="flex flex-col items-center">
                    <div class="h-16 w-26 flex items-center justify-center text-teal-800">
                        <img src="../images/logo_campus_2.png" alt="Logo" class="h-16 w-26">
                    </div>
                    <h1 class="mt-2 text-blue-500 font-bold text-xl">Admin Log in</h1>
                </div>
            </div>

            <!-- Login Form -->
            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="w-full mt-6">
               
                <div class="mb-2">
                    <input 
                        type="text" 
                        name="email" 
                        placeholder="Email" 
                        value="<?= htmlspecialchars($email) ?>"
                        class="w-full px-4 py-3 rounded-md bg-gray-100 border border-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-400"
                    >
                    <span class="text-red-500 text-sm"><?= $emailMsg ?></span>
                </div>

                <div class="mb-2 relative">
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        placeholder="Password" 
                        class="w-full px-4 py-3 rounded-md bg-gray-100 border border-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-400"
                    >
                    <button 
                        type="button"
                        class="absolute right-3 top-3"
                        onclick="togglePasswordVisibility()"
                    >
                        <i id="toggleIcon" class="fa-regular fa-eye-slash text-gray-500"></i>
                    </button>
                    <span class="text-red-500 text-sm block mt-1"><?= $passwordMsg ?></span>
                </div>

                <div class="flex justify-between mt-3 mb-6">
                    <a href="adminChangePassword.php" class="text-sm text-blue-500 hover:underline">Change Password</a>
                    <a href="adminForgotPassword.php" class="text-sm text-blue-500 hover:underline">Forgot Password?</a>
                </div>

                <div class="mb-6 flex justify-center">
                    <button type="submit" class="login-button w-full lg:w-[280px] py-2 bg-blue-500 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        Login
                    </button>
                </div>

                <div class="text-center mt-6">
                    <a href="../index.php" class="text-sm font-medium text-blue-500 hover:underline">Back to Home</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Right side - Image (Only shows on screens larger than 1024px) -->
    <div class="right-container w-1/2 bg-blue-500 hidden lg:flex items-center justify-center">
        <div class="p-28 text-white w-full h-full flex justify-center text-center">
            <div class="h-full w-full bg-opacity-30 rounded-full overflow-hidden">
                <img src="../images/admin.gif" alt="Right Bg" class="h-full w-full object-cover">
            </div>
        </div>
    </div>
</div>

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