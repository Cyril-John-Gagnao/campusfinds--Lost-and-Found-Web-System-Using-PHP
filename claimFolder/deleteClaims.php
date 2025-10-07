<?php
include_once __DIR__ . '/../dbconn.php'; 
$masterConn = getMasterConnection();


if (isset($_POST['claim_ID'])) {
    $claim_ID = $_POST['claim_ID'];

    // Get the image path from the database
    $stmt = $masterConn->prepare("SELECT imagePath FROM claims WHERE claim_ID = :claim_ID");
    $stmt->bindParam(':claim_ID', $claim_ID);
    $stmt->execute();
    $item = $stmt->fetch();

    if ($item && !empty($item['imagePath'])) {
        $imagePath = $item['imagePath'];

        //  Delete the image file 
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete the record from the database
    $stmt = $masterConn->prepare("DELETE FROM claims WHERE claim_ID = :claim_ID");
    $stmt->bindParam(':claim_ID', $claim_ID);
    $result = $stmt->execute();

    if ($result) {
        header('Location: displayClaims.php');
        exit();
    } else {
        echo "Deleting of Claim failed.";
    }
} else {
    echo "Invalid request.";
}


?>
