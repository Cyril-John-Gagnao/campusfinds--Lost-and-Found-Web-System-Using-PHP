<?php
function uploadImage($inputName, $targetDir, &$errorMsg) {
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = "❌ Please upload an image.<br>";
        return false;
    }

    $imageFile = $_FILES[$inputName];
    $check = getimagesize($imageFile['tmp_name']);
    $imageFileType = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
    $allowedTypes = ["jpg", "jpeg", "png", "gif"];

    if ($check === false || !in_array($imageFileType, $allowedTypes)) {
        $errorMsg = "❌ Invalid image file. Must be JPG, PNG, or GIF.<br>";
        return false;
    }

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filename = basename($imageFile["name"]);
    $targetFile = $targetDir . $filename;

    if (move_uploaded_file($imageFile["tmp_name"], $targetFile)) {
        return $targetFile;
    } else {
        $errorMsg = "❌ Failed to move uploaded image.<br>";
        return false;
    }
}
?>
