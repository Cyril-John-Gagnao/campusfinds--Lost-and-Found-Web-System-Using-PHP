<tr class="hover:bg-blue-100 hover:shadow-md hover:scale-[1.01] even:bg-gray-50 transition-all duration-200 transform">
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($itemName) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($description) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($claimerName) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($claimDate) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($pickUpLocation) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200">
        <?php
            $imageName = basename($imagePath);
            $webPath = "../claimsImages/" . $imageName;
            $serverPath = __DIR__ . '/../claimsImages/' . $imageName;
        ?>
        <?php if (!empty($imageName) && file_exists($serverPath)): ?>
            <a href="<?= htmlspecialchars($webPath) ?>" target="_blank">
                <img src="<?= htmlspecialchars($webPath) ?>" alt="Claims" class="h-20 w-auto rounded-md border border-gray-300 object-cover shadow-sm hover:scale-105 transition-transform duration-200">
            </a>
        <?php else: ?>
            <span class="text-gray-500 italic">No image</span>
        <?php endif; ?>
    </td>
</tr>
