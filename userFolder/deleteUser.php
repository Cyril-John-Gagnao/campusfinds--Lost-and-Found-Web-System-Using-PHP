<?php
include_once __DIR__ . '/../dbconn.php'; 

$masterConn = getMasterConnection();

if (isset($_POST['user_ID'])) {
    $user_ID = $_POST['user_ID'];

	// Get the image path from the database
	$stmt = $masterConn->prepare("SELECT imagePath FROM user WHERE user_ID = :user_ID");
	$stmt->bindParam(':user_ID', $user_ID);
	$stmt->execute();
	$item = $stmt->fetch();

	if ($item && !empty($item['imagePath'])) {
		$imagePath = $item['imagePath'];

		// Delete the image file if it exists
		if (file_exists($imagePath)) {
			unlink($imagePath);
		}
	}

	$stmt = $masterConn->prepare("DELETE from user WHERE user_ID=:user_ID");
	$stmt->bindParam(':user_ID', $user_ID);
	$result = $stmt->execute();

	if($result) {
		header('Location: displayUser.php');
	} else {
		echo "Deleting of User failed.";
	}
} else {
    echo "Invalid request.";
}

?>