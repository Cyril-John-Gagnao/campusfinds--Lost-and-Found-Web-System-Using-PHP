function openImageModal(src) {
  const modal = document.getElementById("imageModal");
  const modalImg = document.getElementById("modalImage");
  modalImg.src = src;
  modal.classList.remove("hidden");
}
function closeImageModal() {
  const modal = document.getElementById("imageModal");
  const modalImg = document.getElementById("modalImage");
  modal.classList.add("hidden");
  modalImg.src = "";
}
// Close modal when clicking outside the image
window.addEventListener("click", function (e) {
  if (e.target === document.getElementById("imageModal")) {
    closeImageModal();
  }
});
