<?php 
include_once __DIR__ . '/../dbconn.php';
$masterConn = getMasterConnection();

if (isset($_POST['admin_ID'])) {
    $admin_ID = $_POST['admin_ID'];

	//Get the image path from the database
	$stmt = $masterConn->prepare("SELECT imagePath FROM admin WHERE admin_ID = :admin_ID");
	$stmt->bindParam(':admin_ID', $admin_ID);
	$stmt->execute();
	$item = $stmt->fetch();

	if ($item && !empty($item['imagePath'])) {
		$imagePath = $item['imagePath'];

		// Delete the image file if it exists
		if (file_exists($imagePath)) {
			unlink($imagePath);
		}
	}

	$stmt = $masterConn->prepare("DELETE from admin WHERE admin_ID=:admin_ID");
	$stmt->bindParam(':admin_ID', $admin_ID);
	$result = $stmt->execute();

	if($result) {
		header('Location: displayAdmin.php');
	} else {
		echo "Deleting of admin failed.";
	}
} else {
    echo "Invalid request.";
}


?>
