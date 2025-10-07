<?php
include 'db.php';

// Guardar evento
if(isset($_POST['guardar'])) {
    $fecha = $_POST['fecha'];
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];

    $stmt = $conn->prepare("INSERT INTO eventos (fecha, titulo, descripcion) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $fecha, $titulo, $descripcion);
    $stmt->execute();
    $stmt->close();
}

// Traer eventos
$result = $conn->query("SELECT * FROM eventos ORDER BY fecha ASC");
$event_array = [];
while($row = $result->fetch_assoc()) {
    $event_array[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Agenda Visual</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="../style.css" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body>
  <header>
    <button class="menu-toggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="mobileSidebar" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">☰</button>
    <h1>Agenda - Megasoft</h1>
    <div class="controls">
      <div class="color-pickers">
        <div class="picker">
          <label for="primaryColorPicker">Color primario:</label>
          <input type="color" id="primaryColorPicker" value="#0078b7" />
        </div>
        <div class="picker">
          <label for="secondaryColorPicker">Color secundario:</label>
          <input type="color" id="secondaryColorPicker" value="#f8f9fa" />
        </div>
      </div>
      <div class="dark-mode-switch">
        <label class="switch">
          <input type="checkbox" id="darkModeToggle">
          <span class="slider"></span>
        </label>
        <span>Modo Oscuro</span>
      </div>
    </div>
  </header>

  <!-- aside removed: hamburger offcanvas used for navigation -->

  <!-- Mobile/Universal offcanvas sidebar -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="mobileSidebarLabel">Menú</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
      <nav>
        <a href="../ui-kit.html#navbar" class="d-block mb-2" data-bs-dismiss="offcanvas">Navbar</a>
        <a href="../ui-kit.html#buttons" class="d-block mb-2" data-bs-dismiss="offcanvas">Botones</a>
        <a href="../ui-kit.html#forms" class="d-block mb-2" data-bs-dismiss="offcanvas">Formularios</a>
        <a href="../ui-kit.html#alerts" class="d-block mb-2" data-bs-dismiss="offcanvas">Alertas</a>
        <a href="../ui-kit.html#cards" class="d-block mb-2" data-bs-dismiss="offcanvas">Cards</a>
        <a href="../ui-kit.html#switches" class="d-block mb-2" data-bs-dismiss="offcanvas">Switches</a>
        <a href="../ui-kit.html#tabs" class="d-block mb-2" data-bs-dismiss="offcanvas">Tabs</a>
        <a href="../ui-kit.html#progress" class="d-block mb-2" data-bs-dismiss="offcanvas">Progreso</a>
        <a href="../ui-kit.html#modal" class="d-block mb-2" data-bs-dismiss="offcanvas">Modal</a>
        <a href="../ui-kit.html#tooltips" class="d-block mb-2" data-bs-dismiss="offcanvas">Tooltips</a>
        <a href="../ui-kit.html#badges" class="d-block mb-2" data-bs-dismiss="offcanvas">Badges</a>
        <a href="../ui-kit.html#buttons-mud" class="d-block mb-2" data-bs-dismiss="offcanvas">Botones Variados</a>
        <a href="./index.php" class="d-block mb-2" data-bs-dismiss="offcanvas">Agenda</a>
      </nav>
    </div>
  </div>
  <div class="backdrop" aria-hidden="true"></div>

  <main>
    <div class="container">
      <h2 class="mb-4 text-center">Agregar Evento</h2>
      <form method="post" class="mb-5">
        <div class="row justify-content-center">
          <div class="col-12 col-md-10 col-lg-8">
            <div class="row g-3 align-items-end">
              <div class="col-md-3">
                <input type="date" name="fecha" class="form-control" required>
              </div>
              <div class="col-md-4">
                <input type="text" name="titulo" placeholder="Título" class="form-control" required>
              </div>
              <div class="col-md-4">
                <textarea name="descripcion" placeholder="Descripción" class="form-control" rows="2" style="resize:vertical;"></textarea>
              </div>
              <div class="col-md-1 d-grid">
                <button type="submit" name="guardar" class="btn btn-primary">+</button>
              </div>
            </div>
          </div>
        </div>
      </form>

      <h2 class="mb-3">Calendario - <?php echo date('F Y'); ?></h2>
      <div class="weekdays">
        <div>Dom</div><div>Lun</div><div>Mar</div><div>Mié</div><div>Jue</div><div>Vie</div><div>Sáb</div>
      </div>
      <div class="calendar" id="calendar"></div>
    </div>
  </main>

  <!-- Bootstrap JS (bundle includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

  <script>
document.querySelectorAll('[data-bs-dismiss="offcanvas"]').forEach(link => {
    link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if(href && href !== '#') {
            e.preventDefault(); // evita que Bootstrap bloquee
            const offcanvasEl = this.closest('.offcanvas');
            const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
            offcanvas.hide(); // cierra el offcanvas
            // esperar a que termine la animación
            setTimeout(() => { window.location.href = href; }, 300);
        }
    });
});
</script>

<script>
const events = <?php echo json_encode($event_array); ?>;

// Aplica colores a las variables CSS
function setPrimaryColor(color){ document.documentElement.style.setProperty('--primary-color', color); }
function setSecondaryColor(color){ document.documentElement.style.setProperty('--secondary-color', color); }

// Carga ajustes desde localStorage
function loadSettings(){
  const p = localStorage.getItem('agendaPrimary');
  const s = localStorage.getItem('agendaSecondary');
  const dark = localStorage.getItem('agendaDark') === '1';
  if(p){ const el = document.getElementById('primaryColorPicker'); if(el) el.value = p; setPrimaryColor(p); }
  if(s){ const el2 = document.getElementById('secondaryColorPicker'); if(el2) el2.value = s; setSecondaryColor(s); }
  const darkToggle = document.getElementById('darkModeToggle'); if(darkToggle) darkToggle.checked = dark;
  document.body.classList.toggle('dark', dark);
}

const pc = document.getElementById('primaryColorPicker');
const sc = document.getElementById('secondaryColorPicker');
const dt = document.getElementById('darkModeToggle');

if(pc) pc.addEventListener('input', (e)=>{ setPrimaryColor(e.target.value); localStorage.setItem('agendaPrimary', e.target.value); });
if(sc) sc.addEventListener('input', (e)=>{ setSecondaryColor(e.target.value); localStorage.setItem('agendaSecondary', e.target.value); });
if(dt) dt.addEventListener('change', (e)=>{ document.body.classList.toggle('dark', e.target.checked); localStorage.setItem('agendaDark', e.target.checked ? '1' : '0'); });

function generateCalendar() {
  const calendar = document.getElementById('calendar');
  calendar.innerHTML = '';

  const now = new Date();
  const year = now.getFullYear();
  const month = now.getMonth();

  const firstDay = new Date(year, month, 1).getDay();
  const lastDate = new Date(year, month + 1, 0).getDate();

  const safeEvents = Array.isArray(events) ? events : [];

  for(let i=0; i<firstDay; i++){
    const empty = document.createElement('div');
    empty.classList.add('day-card','empty');
    calendar.appendChild(empty);
  }

  for(let d=1; d<=lastDate; d++){
    const dayCard = document.createElement('div');
    dayCard.classList.add('day-card');

    const dayNum = document.createElement('div');
    dayNum.classList.add('day-number');
    dayNum.textContent = d;
    dayCard.appendChild(dayNum);

    const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;

    safeEvents.forEach((ev, idx) => {
      if(!ev || !ev.fecha) return;
      const evDate = String(ev.fecha).split(' ')[0];
        if(evDate === dateStr){
          const evDiv = document.createElement('div');
          evDiv.classList.add('event');
          evDiv.textContent = ev.titulo || '(sin título)';
          evDiv.setAttribute('data-desc', ev.descripcion || '');
          evDiv.setAttribute('data-title', ev.titulo || '');
          evDiv.setAttribute('data-fecha', evDate);
          if(ev.id) evDiv.setAttribute('data-id', ev.id);
          if(ev.done && Number(ev.done) === 1) evDiv.classList.add('finished');
          dayCard.appendChild(evDiv);
        }
    });

    calendar.appendChild(dayCard);
  }
}

// Modal functions for event details
let currentEventEl = null;
let bootstrapModal = null;

function openEventModal(title, desc, evEl){
  const modalEl = document.getElementById('eventModal');
  if(!modalEl) return;
  if(!bootstrapModal){ bootstrapModal = new bootstrap.Modal(modalEl); }
  currentEventEl = evEl || null;
  const titleLabel = modalEl.querySelector('#eventModalLabel');
  const descEl = modalEl.querySelector('.event-desc');
  const dateInput = modalEl.querySelector('#changeDateInput');
  if(titleLabel) titleLabel.textContent = title || '(sin título)';
  if(descEl) descEl.textContent = desc || '';
  // Pre-fill date input if event has data-id or data-fecha
  if(currentEventEl){
    const parentDay = currentEventEl.closest('.day-card');
    // try to infer date from parent index (not robust) - better to store data-fecha
    const dataDate = currentEventEl.getAttribute('data-fecha');
    if(dataDate) dateInput.value = dataDate;
    else dateInput.value = '';
  }
  bootstrapModal.show();
}

function closeEventModal(){ if(bootstrapModal) bootstrapModal.hide(); currentEventEl = null; }

// Delegated click handler: abrir modal al tocar un evento
document.addEventListener('click', function(e){
  const evEl = e.target.closest && e.target.closest('.event');
  if(!evEl) return;
  const title = evEl.getAttribute('data-title') || evEl.textContent;
  const desc = evEl.getAttribute('data-desc') || '';
  openEventModal(title, desc, evEl);
});

// Close modal when clicking .close inside modal or clicking outside modal-content
document.addEventListener('click', function(e){
  const modal = document.getElementById('eventModal');
  if(!modal) return;
  if(e.target.matches('#eventModal .close')){ closeEventModal(); }
  if(e.target === modal){ closeEventModal(); }
});

loadSettings();
generateCalendar();

// Handlers for modal buttons
document.addEventListener('DOMContentLoaded', () => {
  const markBtn = document.getElementById('markDoneBtn');
  const changeBtn = document.getElementById('changeDateBtn');
  const dateInput = document.getElementById('changeDateInput');

  if(markBtn){
    markBtn.addEventListener('click', async () => {
      if(!currentEventEl) return;
      const id = currentEventEl.getAttribute('data-id');
      const payload = { action: 'mark_done' };
      if(id) payload.id = id;
      else {
        payload.titulo = currentEventEl.getAttribute('data-title');
        // try to get fecha from parent day's generated date attribute
        const dayIndex = Array.from(currentEventEl.closest('.calendar').children).indexOf(currentEventEl.closest('.day-card'));
      }
      const res = await fetch('update_event.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const json = await res.json();
      if(json.success && json.event){
        // Update local events array
        const updated = json.event;
        let replaced = false;
        for(let i=0;i<events.length;i++){
          if(events[i].id && updated.id && Number(events[i].id) === Number(updated.id)){
            events[i] = updated; replaced = true; break;
          }
          if(!events[i].id && events[i].titulo === updated.titulo && events[i].fecha === updated.fecha){ events[i] = updated; replaced = true; break; }
        }
        if(!replaced) events.push(updated);
        generateCalendar();
      } else if(json.success){
        // fallback: just add finished class
        if(currentEventEl) currentEventEl.classList.add('finished');
      }
      closeEventModal();
    });
  }

  if(changeBtn){
    changeBtn.addEventListener('click', async () => {
      if(!currentEventEl) return;
      const newFecha = dateInput.value;
      if(!newFecha) return alert('Selecciona una fecha');
      const id = currentEventEl.getAttribute('data-id');
      const payload = { action: 'change_date', new_fecha: newFecha };
      if(id) payload.id = id;
      else {
        payload.titulo = currentEventEl.getAttribute('data-title');
      }
      const res = await fetch('update_event.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const json = await res.json();
      if(json.success && json.event){
        const updated = json.event;
        let replaced = false;
        for(let i=0;i<events.length;i++){
          if(events[i].id && updated.id && Number(events[i].id) === Number(updated.id)){
            events[i] = updated; replaced = true; break;
          }
        }
        if(!replaced) events.push(updated);
        generateCalendar();
      } else {
        alert('Error al cambiar la fecha');
      }
      closeEventModal();
    });
  }
});
</script>
<script src="../main.js"></script>

<!-- Event detail modal (Bootstrap) -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventModalLabel">Detalle del evento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="event-desc" style="white-space:pre-wrap;"></p>
        <div class="mt-3">
          <label for="changeDateInput" class="form-label">Cambiar fecha</label>
          <input type="date" id="changeDateInput" class="form-control" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="markDoneBtn" class="btn btn-success">Terminado</button>
        <button type="button" id="changeDateBtn" class="btn btn-secondary">Cambiar Fecha</button>
        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>
