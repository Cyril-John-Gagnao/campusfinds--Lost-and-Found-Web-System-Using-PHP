<?php
include_once __DIR__ . '/../dbconn.php'; 

$masterConn = getMasterConnection();


if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $masterConn->prepare("DELETE FROM notifications WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

header("Location: ../adminFolder/adminDashboard.php"); 
exit();
