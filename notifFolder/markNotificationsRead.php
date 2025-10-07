<?php
session_start();
include_once __DIR__ . '/../dbconn.php'; 

$masterConn = getMasterConnection();

// Mark all admin notifications as read
$stmt = $masterConn->prepare("UPDATE notifications SET is_read = 1 WHERE receiver_role = 'admin'");
$stmt->execute();

header("Location: ../adminFolder/adminDashBoard.php");
exit;
