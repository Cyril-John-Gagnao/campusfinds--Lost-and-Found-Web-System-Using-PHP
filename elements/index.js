//BACK TO TOP FUNCTIONALITY
const backToTopBtn = document.getElementById("backToTopBtn");

window.onscroll = function () {
  if (
    document.body.scrollTop > 300 ||
    document.documentElement.scrollTop > 300
  ) {
    backToTopBtn.style.display = "flex";
  } else {
    backToTopBtn.style.display = "none";
  }
};

backToTopBtn.addEventListener("click", function () {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
});

backToTopBtn.style.display = "none";

//Fade-in Transition on Page Load
document.addEventListener("DOMContentLoaded", function () {
  document.body.classList.add("fade-in");
});

const links = document.querySelectorAll("a");
links.forEach((link) => {
  link.addEventListener("click", function (e) {
    e.preventDefault();
    document.body.style.opacity = 1;
    setTimeout(() => {
      window.location.href = link.href;
    }, 600);
  });
});

//DROP DOWN FUNCTIONALITY
function toggleDropdown() {
  document.getElementById("dropdownMenu").classList.toggle("hidden");
}

//TOGGLE PASSWORD FUNCTIONALITY
function togglePassword(id) {
  const input = document.getElementById(id);
  input.type = input.type === "password" ? "text" : "password";
}

//TOGGLE PASSWORD LOGIN FUNCTIONALITY
function togglePasswordVisibility() {
  const passwordInput = document.getElementById("password");
  const toggleIcon = document.getElementById("toggleIcon");

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    toggleIcon.classList.remove("fa-eye-slash");
    toggleIcon.classList.add("fa-eye");
  } else {
    passwordInput.type = "password";
    toggleIcon.classList.remove("fa-eye");
    toggleIcon.classList.add("fa-eye-slash");
  }
}

//CONFIRM SUBMIT FUNCTION
function confirmSubmit() {
  return confirm("Are you sure you want to update this user?");
}
