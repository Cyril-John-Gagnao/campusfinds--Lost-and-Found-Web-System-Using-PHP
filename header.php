<!-- header.php -->
<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CampusFinds - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .nav-link {
      position: relative;
      font-weight: bold;
    }
    .nav-link::after {
      content: '';
      position: absolute;
      width: 0%;
      height: 3px;
      bottom: -4px;
      left: 0;
      background-color: #2563eb;
      transition: width 0.3s ease;
    }
    .nav-link:hover::after {
      width: 100%;
    }
  </style>
</head>
<body class="bg-white font-sans">

<header class="bg-white text-black h-16 px-6 shadow-md">
  <div class="flex items-center justify-between w-full h-full">
    <div class="flex items-center gap-3">
      <img src="images/logo_campus_2.png" alt="System Logo" class="h-8 w-8 object-contain"/>
      <h1 class="text-xl font-bold">Campus Finds</h1>
    </div>

    <nav class="flex items-center gap-10">
      <a href="userDashboard.php" class="nav-link text-black">Lost Items</a>
      <a href="displayClaimsUser.php" class="nav-link text-black">Claimed Items</a>
    </nav>

    <a href="logout.php" class="border px-3 py-1 rounded-full hover:bg-gray-100 text-sm inline-block">Sign Out</a>
  </div>
</header>
