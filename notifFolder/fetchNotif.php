<?php
session_start();
include_once __DIR__ . '/../dbconn.php'; 

$slaveConn = getSlaveConnection();

header('Content-Type: application/json');

// Fetch notifications for admin (unread first)
$stmt = $slaveConn->prepare("SELECT * FROM notifications WHERE receiver_role = 'admin' ORDER BY created_at DESC");
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread notifications
$stmtCount = $slaveConn->prepare("SELECT COUNT(*) FROM notifications WHERE receiver_role = 'admin' AND is_read = 0");
$stmtCount->execute();
$unreadCount = (int)$stmtCount->fetchColumn();

// Count pending lost items
$stmtPending = $slaveConn->prepare("SELECT COUNT(*) FROM lost_items WHERE status = 'pending'");
$stmtPending->execute();
$pendingCount = (int)$stmtPending->fetchColumn();

echo json_encode([
    'notifications' => $notifications,
    'unreadCount' => $unreadCount,
    'pendingCount' => $pendingCount
]);
