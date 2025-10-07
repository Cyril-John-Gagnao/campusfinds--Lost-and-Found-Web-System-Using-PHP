<tr class="hover:bg-blue-100 hover:shadow-md hover:scale-[1.01] even:bg-gray-50 transition-all duration-200 transform">
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($itemName) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($description) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($locationFound) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($pickUpLocation) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($dateFound) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200">
        <?php
        $imageName = basename($imagePath);
        $webPath = "../lostImages/" . $imageName;
        $serverPath = __DIR__ . '/../lostImages/' . $imageName;
        ?>
        <?php if (!empty($imageName) && file_exists($serverPath)): ?>
            <a href="<?= htmlspecialchars($webPath) ?>" target="_blank">
                <img src="<?= htmlspecialchars($webPath) ?>" alt="Lost item" class="h-20 w-auto rounded-md border border-gray-300 object-cover shadow-sm hover:scale-105 transition-transform duration-200">
            </a>
        <?php else: ?>
            <span class="text-gray-500 italic">No image</span>
        <?php endif; ?>
    </td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($returneeName) ?></td>
    <td class="px-4 py-3 border-b border-gray-200">
        <form action="../notifFolder/userClaim.php" method="POST">
            <input type="hidden" name="lostItem_ID" value="<?= htmlspecialchars($data['lostItem_ID']) ?>">
            <input type="hidden" name="claimerEmail" value="<?= htmlspecialchars($_SESSION['email']) ?>"> <!-- or current user's email -->
            <button type="submit" onclick="return confirm('Are you sure you want to claim this item?');" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors duration-200">Claim</button>
        </form>
    </td>
</tr>
