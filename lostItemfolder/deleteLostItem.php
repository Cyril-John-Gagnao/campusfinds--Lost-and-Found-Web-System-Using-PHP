<?php 
include_once __DIR__ . '/../dbconn.php'; 

$masterConn = getMasterConnection();


if (isset($_POST['lostItem_ID'])) {
    $lostItem_ID = $_POST['lostItem_ID'];

    //  Get the image path from the database
    $stmt = $masterConn->prepare("SELECT imagePath FROM lost_items WHERE lostItem_ID = :lostItem_ID");
    $stmt->bindParam(':lostItem_ID', $lostItem_ID);
    $stmt->execute();
    $item = $stmt->fetch();

    if ($item && !empty($item['imagePath'])) {
        $imagePath = $item['imagePath'];

        //  Delete the image file if it exists
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete the record from the database
    $stmt = $masterConn->prepare("DELETE FROM lost_items WHERE lostItem_ID = :lostItem_ID");
    $stmt->bindParam(':lostItem_ID', $lostItem_ID);
    $result = $stmt->execute();

    if ($result) {
        header('Location: ../adminFolder/adminDashboard.php');
        exit();
    } else {
        echo "Deleting of lost item failed.";
    }
} else {
    echo "Invalid request.";
}


?>
