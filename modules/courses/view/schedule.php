<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$schedules = $schedules ?? [];
$currentView = $currentView ?? 'week';
$currentDate = $currentDate ?? date('Y-m-d');
?>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        <a href="index.php?module=courses&action=list" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Dersler
        </a>
    </div>
    <div class="btn-group">
        <button class="btn btn-sm btn-outline-primary" onclick="changeView('timeGridDay')">
            Günlük
        </button>
        <button class="btn btn-sm btn-outline-primary active" onclick="changeView('timeGridWeek')">
            Haftalık
        </button>
        <button class="btn btn-sm btn-outline-primary" onclick="changeView('dayGridMonth')">
            Aylık
        </button>
    </div>
</div>

<!-- Takvim -->
<div class="card shadow-sm">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- Event Detay Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetails"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/tr.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'tr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        slotMinTime: '07:00:00',
        slotMaxTime: '19:00:00',
        allDaySlot: false,
        height: 'auto',
        weekends: true,
        firstDay: 1, // Pazartesi
        
        // Event'leri API'den çek
        events: function(info, successCallback, failureCallback) {
            fetch('index.php?module=courses&action=scheduleJson&start=' + info.startStr + '&end=' + info.endStr)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => {
                    console.error('Error:', error);
                    failureCallback(error);
                });
        },
        
        // Event tıklandığında
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            
            document.getElementById('eventTitle').textContent = event.title;
            document.getElementById('eventDetails').innerHTML = `
                <p><strong>Ders Kodu:</strong> ${props.course_code || '-'}</p>
                <p><strong>Öğretmen:</strong> ${props.teacher || '-'}</p>
                <p><strong>Saat:</strong> ${event.start.toLocaleTimeString('tr-TR', {hour: '2-digit', minute: '2-digit'})} - 
                   ${event.end.toLocaleTimeString('tr-TR', {hour: '2-digit', minute: '2-digit'})}</p>
                <p><strong>Sınıf/Atölye:</strong> ${props.location || '-'}</p>
                <hr>
                <a href="index.php?module=courses&action=view&id=${props.course_id}" class="btn btn-sm btn-primary">
                    Ders Detayları
                </a>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        },
        
        // Görünüm değiştiğinde
        viewDidMount: function(info) {
            document.querySelectorAll('.btn-group button').forEach(btn => {
                btn.classList.remove('active');
            });
        }
    });
    
    calendar.render();
    
    // Global değişken olarak kaydet
    window.courseCalendar = calendar;
});

function changeView(viewName) {
    window.courseCalendar.changeView(viewName);
}
</script>

<style>
/* FullCalendar özelleştirme */
.fc-event {
    cursor: pointer;
    border-radius: 4px;
    padding: 2px 4px;
}

.fc-event:hover {
    opacity: 0.8;
}

.fc-daygrid-day-number {
    font-size: 14px;
    font-weight: 600;
}

.fc-col-header-cell {
    background-color: #f8f9fa;
    font-weight: 600;
}

.fc-timegrid-slot {
    height: 3em;
}
</style>