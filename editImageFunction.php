<?php
function uploadEditedImage($inputName, $targetDir, $oldImagePath, &$errorMsg) {
    // Default to keeping old image
    $imagePath = $oldImagePath;

    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == 0) {
        $validTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = basename($_FILES[$inputName]['name']);
        $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($imageFileType, $validTypes)) {
            $targetFile = $targetDir . time() . "_" . $fileName;

            if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;

                // Delete the old image
                if (!empty($oldImagePath) && file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            } else {
                $errorMsg = "❌ Failed to upload new image.<br>";
            }
        } else {
            $errorMsg = "❌ Only JPG, PNG, and GIF files are allowed.<br>";
        }
    }

    return $imagePath;
}
?>
