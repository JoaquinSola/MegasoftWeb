// Scroll en web
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const targetEl = document.querySelector(this.getAttribute('href'));
    if(targetEl){
      const yOffset = -80; // offset de 80px
      const y = targetEl.getBoundingClientRect().top + window.pageYOffset + yOffset;
      window.scrollTo({ top: y, behavior: 'smooth' });
    }
  });
});



const root = document.documentElement;
const primaryPicker = document.getElementById("primaryColorPicker");
const secondaryPicker = document.getElementById("secondaryColorPicker");
const darkModeToggle = document.getElementById("darkModeToggle");
const menuToggleBtn = document.querySelector('.menu-toggle');
const sidebar = document.getElementById('sidebar');
const backdrop = document.querySelector('.backdrop');

// Cambiar color primario y secundario dinámicamente
function applyPrimary(color){ root.style.setProperty("--primary-color", color); }
function applySecondary(color){ root.style.setProperty("--secondary-color", color); }

// Guardar/Aplicar desde pickers y localStorage
if(primaryPicker){
  primaryPicker.addEventListener("input", e => {
    applyPrimary(e.target.value);
    // guardar con la clave que usa agenda
    localStorage.setItem('agendaPrimary', e.target.value);
    // también guardar claves genéricas por compatibilidad
    localStorage.setItem('primaryColor', e.target.value);
  });
}
if(secondaryPicker){
  secondaryPicker.addEventListener("input", e => {
    applySecondary(e.target.value);
    localStorage.setItem('agendaSecondary', e.target.value);
    localStorage.setItem('secondaryColor', e.target.value);
  });
}

// Modo oscuro
if(darkModeToggle){
  darkModeToggle.addEventListener("change", e => {
    document.body.classList.toggle("dark", e.target.checked);
    localStorage.setItem('agendaDark', e.target.checked ? '1' : '0');
  });
}

// Cargar valores guardados al inicio (compatibles con agenda)
(function loadSharedColors(){
  try{
    const p = localStorage.getItem('agendaPrimary') || localStorage.getItem('primaryColor');
    const s = localStorage.getItem('agendaSecondary') || localStorage.getItem('secondaryColor');
    const dark = (localStorage.getItem('agendaDark') === '1');
    if(p){ applyPrimary(p); if(primaryPicker) primaryPicker.value = p; }
    if(s){ applySecondary(s); if(secondaryPicker) secondaryPicker.value = s; }
    if(darkModeToggle){ darkModeToggle.checked = dark; document.body.classList.toggle('dark', dark); }
  }catch(e){ console.warn('No se pudieron cargar colores compartidos', e); }
})();

// Sidebar mobile toggle
function closeSidebar() {
  sidebar.classList.remove('open');
  backdrop.classList.remove('show');
  if (menuToggleBtn) menuToggleBtn.setAttribute('aria-expanded', 'false');
  document.body.classList.remove('menu-open');
}

if (menuToggleBtn && !menuToggleBtn.hasAttribute('data-bs-toggle')) {
  // only attach legacy toggle if the button is not configured to open a Bootstrap offcanvas
  menuToggleBtn.addEventListener('click', () => {
    const opening = sidebar && !sidebar.classList.contains('open');
    if(sidebar) sidebar.classList.toggle('open');
    if(backdrop) backdrop.classList.toggle('show', opening);
    menuToggleBtn.setAttribute('aria-expanded', String(opening));
    document.body.classList.toggle('menu-open', opening);
  });
}

if (backdrop) {
  backdrop.addEventListener('click', closeSidebar);
}

// Close on Esc key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && sidebar.classList.contains('open')) {
    closeSidebar();
  }
});

// Close when clicking a nav link (improves UX on mobile)
document.querySelectorAll('.sidebar a').forEach(link => {
  link.addEventListener('click', () => {
    if (sidebar.classList.contains('open')) closeSidebar();
  });
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

// Toggle que cambia de vertical a horizantal Buttons Orientation
if (window.bootstrap) {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
  });
}


const toggle = document.getElementById("toggleOrientation");
const buttonContainer = document.getElementById("buttonContainer");
const label = document.querySelector('label[for="toggleOrientation"]');

toggle.addEventListener("change", () => {
  if (toggle.checked) {
    buttonContainer.classList.remove("flex-row", "flex-wrap");
    buttonContainer.classList.add("flex-column", "align-items-start");
    label.textContent = "Vertical";
  } else {
    buttonContainer.classList.remove("flex-column", "align-items-start");
    buttonContainer.classList.add("flex-row", "flex-wrap");
    label.textContent = "Horizontal";
  }
});

// Inicialización segura de carousels
const horizontalCarouselEl = document.querySelector('#horizontalCarousel');
if (horizontalCarouselEl) {
  new bootstrap.Carousel(horizontalCarouselEl, { interval: 4500, ride: 'carousel' });
}

const verticalCarouselEl = document.querySelector('#verticalCarousel');
if (verticalCarouselEl) {
  new bootstrap.Carousel(verticalCarouselEl, { interval: 4500, ride: 'carousel' });
}

// Toggle mostrar/ocultar flechas
const toggleArrows = document.getElementById('toggleArrows');
if (toggleArrows) {
  const updateArrows = (show) => {
    document.querySelectorAll('.carousel').forEach(carousel => {
      carousel.querySelectorAll('.carousel-control-prev, .carousel-control-next')
        .forEach(control => control.style.display = show ? 'flex' : 'none');
    });
  }

  // Ejecutar al cargar
  updateArrows(toggleArrows.checked);

  // Ejecutar al cambiar toggle
  toggleArrows.addEventListener('change', () => {
    updateArrows(toggleArrows.checked);
  });
}

//Collapse
const collapseBtn = document.getElementById('collapseBtn');
const collapseContent = document.getElementById('collapseContent');

collapseBtn.addEventListener('click', () => {
  collapseContent.classList.toggle('show');

  // Cambiar texto del botón
  collapseBtn.textContent = collapseContent.classList.contains('show') ? 'Colapsar' : 'Expandir';
});









