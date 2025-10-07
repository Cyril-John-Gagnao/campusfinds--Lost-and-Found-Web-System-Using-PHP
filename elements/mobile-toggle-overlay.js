// Mobile navigation toggle
document.addEventListener("DOMContentLoaded", function () {
  const mobileMenuButton = document.getElementById("mobileMenuButton");
  const mobileNavOverlay = document.getElementById("mobileNavOverlay");
  const mobileNav = document.getElementById("mobileNav");
  const closeMobileNav = document.getElementById("closeMobileNav");

  // Toggle mobile nav
  if (mobileMenuButton) {
    mobileMenuButton.addEventListener("click", function () {
      mobileNavOverlay.classList.toggle("hidden");
      mobileNav.classList.toggle("-translate-x-full");
      document.body.style.overflow = mobileNav.classList.contains(
        "-translate-x-full"
      )
        ? ""
        : "hidden";
    });
  }

  // Close mobile nav
  function closeMobileNavigation() {
    mobileNavOverlay.classList.add("hidden");
    mobileNav.classList.add("-translate-x-full");
    document.body.style.overflow = "";
  }

  if (closeMobileNav) {
    closeMobileNav.addEventListener("click", closeMobileNavigation);
  }

  if (mobileNavOverlay) {
    mobileNavOverlay.addEventListener("click", closeMobileNavigation);
  }
});
