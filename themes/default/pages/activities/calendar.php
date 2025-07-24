<div class="card card-primary">
    <div class="card-body p-0">
        <div id="calendar"></div>
    </div>
</div>

<div class="modal fade" id="eventDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Etkinlik Detayları</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl>
                    <dt>Başlık</dt>
                    <dd id="eventTitle"></dd>
                    <dt>Açıklama</dt>
                    <dd id="eventDescription"></dd>
                    <dt>Başlangıç Tarihi</dt>
                    <dd id="eventStart"></dd>
                    <dt>Bitiş Tarihi</dt>
                    <dd id="eventEnd"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        initialView: 'dayGridMonth',
        locale: 'tr',
        events: '?module=activities&action=calendar_data',
        eventClick: function(info) {
            var title = info.event.title;
            var description = info.event.extendedProps.description;
            var startDate = info.event.start.toLocaleString('tr-TR', { dateStyle: 'long', timeStyle: 'short' });
            var endDate = info.event.end ? info.event.end.toLocaleString('tr-TR', { dateStyle: 'long', timeStyle: 'short' }) : 'Belirtilmemiş';

            document.getElementById('modalTitle').innerText = title;
            document.getElementById('eventTitle').innerText = title;
            document.getElementById('eventDescription').innerText = description;
            document.getElementById('eventStart').innerText = startDate;
            document.getElementById('eventEnd').innerText = endDate;

            $('#eventDetailModal').modal('show');
        }
    });
    calendar.render();
});
</script>