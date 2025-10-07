<!-- MAIN FEATURES CONTAINER -->
<?php
$features = [
  [
    "title" => "Reliable",
    "description" => "Ensures consistent performance and accurate tracking of lost items.",
    "icon" => "images/feature 1_icon.png",
    "alt" => "Reliable Icon"
  ],
  [
    "title" => "User-Friendly",
    "description" => "Simple and intuitive interface for easy use.",
    "icon" => "images/feature 2_icon.png",
    "alt" => "User-Friendly Icon"
  ],
  [
    "title" => "Efficient",
    "description" => "Quickly locate lost items with organized categories.",
    "icon" => "images/feature 3_icon.png",
    "alt" => "Efficient Icon"
  ],
  [
    "title" => "Secure",
    "description" => "Data is protected with secure authentication.",
    "icon" => "images/feature 4_icon.png",
    "alt" => "Secure Icon"
  ]
];
?>

<div class="bg-blue-100 py-3 px-8 w-full mt-40">
  <div class="container mx-auto mt-8 text-center">
    <h2 class="text-3xl font-bold text-center mb-12">Why Campus Finds?</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php foreach ($features as $feature): ?>
        <div class="flex flex-col items-center text-center group transition duration-300 transform hover:scale-105 hover:bg-blue-50 p-4 rounded-lg">
          <img src="<?= htmlspecialchars($feature['icon']) ?>" alt="<?= htmlspecialchars($feature['alt']) ?>" class="h-12 w-12 mb-2">
          <h3 class="font-medium text-lg mb-2 group-hover:text-blue-600"><?= htmlspecialchars($feature['title']) ?></h3>
          <p class="text-gray-500 text-sm group-hover:text-blue-600"><?= htmlspecialchars($feature['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- TEAM CONTAINER -->
<?php
$team = [
  ["name" => "Allysseus", "role" => "UI Designer", "image" => "images/allysseus.jpg", "bg" => "bg-blue-50"],
  ["name" => "Jim Lloyd", "role" => "Software Developer", "image" => "images/jinlloyd.jpg", "bg" => "bg-blue-100"],
  ["name" => "Cyril", "role" => "Software Developer", "image" => "images/cyril.jpg", "bg" => "bg-blue-50"],
  ["name" => "Earl", "role" => "UI Designer", "image" => "images/earl.jpg", "bg" => "bg-green-50"],
  ["name" => "Roselle", "role" => "UI Designer", "image" => "images/roselle.jpg", "bg" => "bg-red-50"],
];
?>

<div class="container mx-auto mt-24 mb-24">
  <h3 class="text-3xl font-bold text-center mb-16">Meet the Team</h3>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
    <?php foreach ($team as $member): ?>
      <div class="<?= $member['bg'] ?> rounded-lg overflow-hidden pb-6 group transition duration-200 transform hover:scale-105 hover:shadow-lg">
        <div class="text-center pt-6 pb-4 px-4">
          <h3 class="font-medium text-lg"><?= htmlspecialchars($member['name']) ?></h3>
          <p class="text-gray-500 text-sm"><?= htmlspecialchars($member['role']) ?></p>
        </div>
        <div class="px-6">
          <img src="<?= htmlspecialchars($member['image']) ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="w-full h-auto rounded-lg transition-transform duration-300 group-hover:scale-105">
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>