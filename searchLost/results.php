
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
    <!-- Edit Button -->
    <td class="px-4 py-3 text-sm border-b border-gray-200">
        <a href="../lostItemFolder/editLostItem.php?lostItem_ID=<?= htmlspecialchars($lostItem_ID) ?>" 
           onclick="return confirm('Are you sure?')"
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-200 shadow">
            Edit
        </a>
    </td>

    <!-- Delete Button -->
    <td class="px-4 py-3 text-sm border-b border-gray-200">
        <form action="../lostItemFolder/deleteLostItem.php" method="POST" onsubmit="return confirm('Are you sure?')">
            <button type="submit" name="lostItem_ID" value="<?= htmlspecialchars($lostItem_ID) ?>"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-200 shadow">
                Delete
            </button>
        </form>
    </td>

    <!-- Add Claim Button -->
    <td class="px-4 py-3 text-sm border-b border-gray-200">
        <form action="../claimFolder/addClaimForm.php" method="POST" onsubmit="return confirm('Are you sure?')">
            <button type="submit" name="lostItem_ID" value="<?= htmlspecialchars($lostItem_ID) ?>"
                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-200 shadow">
                Add Claim
            </button>
        </form>
    </td>
</tr>
