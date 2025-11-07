<?php
// modules/activities/view/calendar.php
if (!function_exists('h')) { 
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } 
}
$activities = $activities ?? [];

// Etkinlikleri FullCalendar formatına çevir
$events = [];
foreach ($activities as $a) {
    $events[] = [
        'id' => (int)$a['id'],
        'title' => $a['title'] ?? '',
        'start' => $a['start_date'] ?? $a['activity_date'] ?? '',
        'end' => $a['end_date'] ?? '',
        'url' => 'index.php?module=activities&action=show&id=' . (int)$a['id'],
        'backgroundColor' => '#4285f4',
        'borderColor' => '#4285f4',
        'textColor' => '#ffffff'
    ];
}
?>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="fa fa-calendar"></i> Etkinlik Takvimi
    </h5>
    <div>
      <a href="index.php?module=activities&action=index" class="btn btn-sm btn-outline-secondary me-1">
        <i class="fa fa-list"></i> Liste
      </a>
      <?php if (in_array(currentRole(), ['admin', 'teacher'])): ?>
      <a href="index.php?module=activities&action=create" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Yeni Etkinlik
      </a>
      <?php endif; ?>
    </div>
  </div>
  <div class="card-body">
    <div id="calendar"></div>
  </div>
</div>

<!-- FullCalendar CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/tr.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'tr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
            today: 'Bugün',
            month: 'Ay',
            week: 'Hafta',
            day: 'Gün',
            list: 'Liste'
        },
        height: 'auto',
        navLinks: true,
        editable: false,
        dayMaxEvents: true,
        events: <?= json_encode($events, JSON_UNESCAPED_UNICODE) ?>,
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            if (info.event.url) {
                window.location.href = info.event.url;
            }
        },
        eventMouseEnter: function(info) {
            info.el.style.cursor = 'pointer';
        }
    });
    
    calendar.render();
});
</script>

<style>
#calendar {
    max-width: 100%;
    margin: 0 auto;
}

.fc {
    font-size: 0.9em;
}

.fc-event {
    cursor: pointer;
}

.fc-event:hover {
    opacity: 0.85;
}

.fc-toolbar-title {
    font-size: 1.5em !important;
    font-weight: 600;
}

.fc-button {
    text-transform: capitalize !important;
}

.fc-daygrid-event {
    white-space: normal !important;
    align-items: normal !important;
}
</style>