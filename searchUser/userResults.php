<tr class="hover:bg-blue-100 hover:shadow-md hover:scale-[1.01] even:bg-gray-50 transition-all duration-200 transform">
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($email) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($name) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200">
        <?php
        $imageName = basename($imagePath);
        $webPath = "../userImages/" . $imageName;
        $serverPath = __DIR__ . '/../userImages/' . $imageName;
        ?>
        <?php if (!empty($imageName) && file_exists($serverPath)): ?>
            <a href="<?= htmlspecialchars($webPath) ?>" target="_blank">
                <img src="<?= htmlspecialchars($webPath) ?>" alt="User" class="h-20 w-auto rounded-md border border-gray-300 object-cover shadow-sm hover:scale-105 transition-transform duration-200">
            </a>
        <?php else: ?>
            <span class="text-gray-500 italic">No image</span>
        <?php endif; ?>
    </td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($course) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($year) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($role) ?></td>
    <td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200"><?= htmlspecialchars($department) ?></td>
    <!-- Edit Button -->
    <td class="px-4 py-3 text-sm border-b border-gray-200">
        <a href="editUser.php?user_ID=<?= htmlspecialchars($user_ID) ?>" 
        class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-200 shadow">
            Edit
        </a>
    </td>

    <!-- Delete Button -->
    <td class="px-4 py-3 text-sm border-b border-gray-200">
        <form action="deleteUser.php" method="POST" onsubmit="return confirm('Are you sure?')">
            <button type="submit" name="user_ID" value="<?= htmlspecialchars($user_ID) ?>" 
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-200 shadow">
                Delete
            </button>
        </form>
    </td>
</tr>
