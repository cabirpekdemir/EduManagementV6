<h2>Sınav Takvimi</h2>
<p>Aktif ve tamamlanmış sınavları buradan takip edebilirsiniz.</p>
<a href="index.php?module=exams&action=index" class="btn" style="margin-bottom:15px;">&laquo; Sınav Listesine Dön</a>

<div id="examCalendar" style="margin-top:20px; max-width: 900px; margin: 20px auto; height: 650px;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('examCalendar');
    if (calendarEl && typeof FullCalendar !== 'undefined') {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'tr', 
            buttonText: { 
                today:    'Bugün',
                month:    'Ay',
                week:     'Hafta',
                day:      'Gün',
                list:     'Liste'
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            events: 'index.php?module=exams&action=get_calendar_exams', // YENİ ENDPOINT
            eventTimeFormat: { 
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false,
                hour12: false
            },
            displayEventEnd: false, // Sınavlar genellikle tek bir tarih, bitiş göstermeyelim
            eventClick: function(info) {
                info.jsEvent.preventDefault(); 
                if (info.event.url) { 
                    window.location.href = info.event.url; 
                } else {
                    alert('Sınav: ' + info.event.title + '\nTarih: ' + info.event.start.toLocaleDateString('tr-TR'));
                }
            }
        });
        calendar.render();
    } else {
        if (!calendarEl) console.error("Takvim elementi (#examCalendar) HTML'de bulunamadı!");
        if (typeof FullCalendar === 'undefined') console.error("FullCalendar kütüphanesi yüklenemedi veya tanımlı değil!");
    }
});
</script>