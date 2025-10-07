<?php
session_start();
include_once __DIR__ . '/../dbconn.php'; 

$masterConn = getMasterConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lostItem_ID = $_POST['lostItem_ID'];
    $claimerEmail = $_POST['claimerEmail'];

    // Get user info (claimer)
    $stmtUser = $masterConn->prepare("SELECT user_ID, name FROM user WHERE email = ?");
    $stmtUser->execute([$claimerEmail]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    $name = $user['name'];
    $user_ID = $user['user_ID'];

    // Get lost item details
    $stmtItem = $masterConn->prepare("SELECT * FROM lost_items WHERE lostItem_ID = ?");
    $stmtItem->execute([$lostItem_ID]);
    $item = $stmtItem->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        die("Lost item not found.");
    }

    $message = "User <strong>{$name} ({$claimerEmail})</strong> has request to claim the lost item: <br>"
             . "Item Name: {$item['itemName']}<br>"
             . "Description: {$item['description']}<br>"
             . "Location Found: {$item['locationFound']}<br>"
             . "Date Found: {$item['dateFound']}";

    // Insert notification to notifications table for admin
    $stmtNotif = $masterConn->prepare("INSERT INTO notifications (user_ID, receiver_role, message, is_read) VALUES (?, 'admin', ?, 0)");
    $stmtNotif->execute([$user_ID, $message]);

    $_SESSION['claim_success'] = "Claim notification sent to admin.";
    header("Location: ../userFolder/userDashboard.php");
    exit;
} else {
    die("Invalid request.");
}

?>
