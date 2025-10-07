const root = document.documentElement;
const primaryPicker = document.getElementById("primaryColorPicker");
const secondaryPicker = document.getElementById("secondaryColorPicker");
const darkModeToggle = document.getElementById("darkModeToggle");

// Cambiar color primario y secundario dinámicamente
primaryPicker.addEventListener("input", e => root.style.setProperty("--primary-color", e.target.value));
secondaryPicker.addEventListener("input", e => root.style.setProperty("--secondary-color", e.target.value));

// Modo oscuro
darkModeToggle.addEventListener("change", e => {
  document.body.classList.toggle("dark", e.target.checked);
});

// Modal
const modal = document.getElementById("myModal");
const openModalBtn = document.getElementById("openModal");
const closeModal = document.querySelector(".close");

openModalBtn.onclick = () => modal.classList.add("show");
closeModal.onclick = () => modal.classList.remove("show");
window.onclick = (e) => { if(e.target === modal) modal.classList.remove("show"); };

// Tabs
const tabButtons = document.querySelectorAll(".tab-btn");
const tabContents = document.querySelectorAll(".tab-content");

tabButtons.forEach(btn => {
  btn.addEventListener("click", () => {
    tabButtons.forEach(b => b.classList.remove("active"));
    tabContents.forEach(c => c.classList.remove("active"));
    btn.classList.add("active");
    document.getElementById(btn.dataset.tab).classList.add("active");
  });
});

// Card
const readMoreBtn = document.querySelector(".card .read-more");
const shortText = document.querySelector(".card .short-text");
const fullText = document.querySelector(".card .full-text");

readMoreBtn.addEventListener("click", () => {
  shortText.style.display = "none";
  fullText.style.display = "block";
  readMoreBtn.style.display = "none";
});

