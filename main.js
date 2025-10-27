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
// Helper: convertir hex (#rrggbb or #rgb) a componentes H S L ("H S% L%")
function hexToHslComponents(hex) {
  if (!hex) return null;
  // normalizar
  hex = hex.replace('#','');
  if (hex.length === 3) {
    hex = hex.split('').map(c => c + c).join('');
  }
  const r = parseInt(hex.substring(0,2),16) / 255;
  const g = parseInt(hex.substring(2,4),16) / 255;
  const b = parseInt(hex.substring(4,6),16) / 255;

  const max = Math.max(r,g,b), min = Math.min(r,g,b);
  let h, s, l = (max + min) / 2;

  if (max === min) {
    h = s = 0; // achromatic
  } else {
    const d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    switch(max){
      case r: h = (g - b) / d + (g < b ? 6 : 0); break;
      case g: h = (b - r) / d + 2; break;
      case b: h = (r - g) / d + 4; break;
    }
    h = h * 60;
  }
  h = Math.round(h || 0);
  s = Math.round((s || 0) * 100);
  l = Math.round(l * 100);
  return `${h} ${s}% ${l}%`;
}

// Cambiar color primario y secundario dinámicamente
function applyPrimary(color){
  // si recibimos un hex, actualizamos la variable --h-primary para mantener la paleta HSL derivada
  const hcomp = hexToHslComponents(color);
  if (hcomp) {
    // Set the HSL components only. Do NOT overwrite --primary-color so CSS that
    // derives from --h-primary (via hsl(var(--h-primary))) keeps HSL semantics.
    root.style.setProperty('--h-primary', hcomp);
  } else {
    // Fallback: if a non-hex value (like an hsl(...) string) is provided, set
    // it to --h-primary when possible by extracting components would be ideal,
    // but for now set the raw value to --primary-color so older rules still work.
    root.style.setProperty("--primary-color", color);
  }
}
function applySecondary(color){
  const hcomp = hexToHslComponents(color);
  if (hcomp) {
    root.style.setProperty('--h-secondary', hcomp);
  } else {
    root.style.setProperty("--secondary-color", color);
  }
}

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
  if(p){ applyPrimary(p); if(primaryPicker && typeof p === 'string' && p.startsWith('#')) primaryPicker.value = p; }
  if(s){ applySecondary(s); if(secondaryPicker && typeof s === 'string' && s.startsWith('#')) secondaryPicker.value = s; }
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
    tabButtons.forEach(b => {
      b.classList.remove("active");
      b.setAttribute('aria-selected', 'false');
    });
    tabContents.forEach(c => {
      c.classList.remove("active");
      c.setAttribute('aria-hidden', 'true');
    });
    btn.classList.add("active");
    btn.setAttribute('aria-selected', 'true');
    const panel = document.getElementById(btn.dataset.tab);
    if (panel) {
      panel.classList.add("active");
      panel.setAttribute('aria-hidden', 'false');
    }
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

// ======= FUNCIONALIDAD DE FORMULARIOS =======

// Toggle de contraseña
const togglePasswordBtn = document.getElementById('togglePassword');
const loginPasswordInput = document.getElementById('loginPassword');

if (togglePasswordBtn && loginPasswordInput) {
  togglePasswordBtn.addEventListener('click', () => {
    const type = loginPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    loginPasswordInput.setAttribute('type', type);
    
    // Cambiar icono
    const icon = togglePasswordBtn.querySelector('.material-icons');
    icon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
  });
}

// Validación de formularios en tiempo real
const modernInputs = document.querySelectorAll('.modern-input, .modern-select, .modern-textarea');

modernInputs.forEach(input => {
  input.addEventListener('blur', () => {
    validateField(input);
  });
  
  input.addEventListener('input', () => {
    // Remover clases de validación mientras el usuario escribe
    input.classList.remove('is-valid', 'is-invalid');
  });
});

function validateField(field) {
  const value = field.value.trim();
  const isRequired = field.hasAttribute('required');
  const type = field.type;
  
  // Limpiar clases anteriores
  field.classList.remove('is-valid', 'is-invalid');
  
  if (isRequired && !value) {
    field.classList.add('is-invalid');
    return false;
  }
  
  if (value) {
    // Validaciones específicas por tipo
    if (type === 'email') {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (emailRegex.test(value)) {
        field.classList.add('is-valid');
        return true;
      } else {
        field.classList.add('is-invalid');
        return false;
      }
    } else if (type === 'tel') {
      const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
      if (phoneRegex.test(value)) {
        field.classList.add('is-valid');
        return true;
      } else {
        field.classList.add('is-invalid');
        return false;
      }
    } else {
      field.classList.add('is-valid');
      return true;
    }
  }
  
  return true;
}

// Manejo de envío de formularios
const modernForms = document.querySelectorAll('.modern-form');

modernForms.forEach(form => {
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const inputs = form.querySelectorAll('.modern-input, .modern-select, .modern-textarea');
    let isValid = true;
    
    inputs.forEach(input => {
      if (!validateField(input)) {
        isValid = false;
      }
    });
    
    if (isValid) {
      // Simular envío exitoso
      showNotification('Formulario enviado correctamente', 'success');
      form.reset();
      
      // Limpiar clases de validación
      inputs.forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
      });
    } else {
      showNotification('Por favor, corrige los errores en el formulario', 'error');
    }
  });
});

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
  // Crear elemento de notificación
  const notification = document.createElement('div');
  notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
  notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
  
  notification.innerHTML = `
    <span class="material-icons me-2">${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'}</span>
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(notification);
  
  // Auto-remover después de 5 segundos
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 5000);
}









