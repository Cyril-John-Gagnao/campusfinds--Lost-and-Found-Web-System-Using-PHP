<?php
session_start();
include_once __DIR__ . '/../dbconn.php'; 

$masterConn = getMasterConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? '';

    if (!$id || !in_array($action, ['approve', 'reject'])) {
        die("Invalid request.");
    }

    // Fetch the item from lost_items
    $stmt = $masterConn->prepare("SELECT * FROM lost_items WHERE lostItem_ID = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        die("Item not found.");
    }

    if ($action === 'approve') {
        // Move image if not already in 'lost/' directory
        $old_path = $item['imagePath'];
        $new_path = $old_path;

        if (strpos($old_path, '../pendingLostImages/') === 0) {
            $new_path = str_replace('../pendingLostImages/', '../lostImages/', $old_path);
            if (!file_exists('../lostImages/')) {
                mkdir('../lostImages/', 0777, true);
            }
            if (file_exists($old_path)) {
                rename($old_path, $new_path);
            }
        }

        // Update status and image path + store admin_ID
        $update = $masterConn->prepare("UPDATE lost_items SET status = 'approved', imagePath = ?, admin_ID = ? WHERE lostItem_ID = ?");
        $update->execute([$new_path, $_SESSION['admin_ID'], $id]);
    }

    if ($action === 'reject') {
        // Delete image file
        if (file_exists($item['imagePath'])) {
            unlink($item['imagePath']);
        }

        // Set status to rejected (optional: delete row instead if preferred)
        //$update = $masterConn->prepare("UPDATE lost_items SET status = 'rejected', imagePath = NULL WHERE lostItem_ID = ?");
        //$update->execute([$id]);
        $delete = $masterConn->prepare("DELETE FROM lost_items WHERE lostItem_ID = ?");
        $delete->execute([$id]);
    }

    header("Location: displayUserPendingLostItem.php");
    exit();
} else {
    echo "Invalid request method.";
}
?>