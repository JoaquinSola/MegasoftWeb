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
  <style>
    .calendar { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; }
    .day-card { min-height: 120px; border: 1px solid #ccc; padding: 5px; border-radius: 5px; position: relative; background-color: var(--secondary-color, #f8f9fa); }
    .day-number { font-weight: bold; }
    .event { background-color: var(--primary-color, #0d6efd); color: white; padding: 2px 6px; border-radius: 4px; margin: 2px 0; font-size: 0.85rem; cursor: pointer; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .event:hover::after {
        content: attr(data-desc);
        position: absolute;
        left: 0;
        top: 100%;
        background: var(--primary-color, #0d6efd);
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        white-space: normal;
        width: max-content;
        max-width: 200px;
        z-index: 10;
    }
    .weekdays { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-weight: bold; margin-bottom: 5px; }
    main { padding: 1rem; }
  </style>
</head>
<body>
  <header>
    <button class="menu-toggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="sidebar">☰</button>
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

  <aside id="sidebar" class="sidebar">
    <nav>
      <a href="../ui-kit.html">Inicio</a>
      <a href="#">Agenda</a>
      <!-- Más links según necesidad -->
    </nav>
  </aside>
  <div class="backdrop" aria-hidden="true"></div>

  <main>
    <div class="container">
      <h2 class="mb-4">Agregar Evento</h2>
      <form method="post" class="mb-5">
        <div class="row g-3">
          <div class="col-md-3">
            <input type="date" name="fecha" class="form-control" required>
          </div>
          <div class="col-md-4">
            <input type="text" name="titulo" placeholder="Título" class="form-control" required>
          </div>
          <div class="col-md-4">
            <input type="text" name="descripcion" placeholder="Descripción" class="form-control">
          </div>
          <div class="col-md-1">
            <button type="submit" name="guardar" class="btn btn-primary w-100">+</button>
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
    if(p){ document.getElementById('primaryColorPicker').value = p; setPrimaryColor(p); }
    if(s){ document.getElementById('secondaryColorPicker').value = s; setSecondaryColor(s); }
    document.getElementById('darkModeToggle').checked = dark;
    document.body.classList.toggle('dark', dark);
}

document.getElementById('primaryColorPicker').addEventListener('input', (e)=>{ setPrimaryColor(e.target.value); localStorage.setItem('agendaPrimary', e.target.value); });
document.getElementById('secondaryColorPicker').addEventListener('input', (e)=>{ setSecondaryColor(e.target.value); localStorage.setItem('agendaSecondary', e.target.value); });
document.getElementById('darkModeToggle').addEventListener('change', (e)=>{ document.body.classList.toggle('dark', e.target.checked); localStorage.setItem('agendaDark', e.target.checked ? '1' : '0'); });

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
                dayCard.appendChild(evDiv);
            }
        });

        calendar.appendChild(dayCard);
    }
}

loadSettings();
generateCalendar();
</script>
</body>
</html>
