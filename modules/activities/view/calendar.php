<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Etkinlik Takvimi</h3>
    </div>
    <div class="card-body p-0">
        <!-- FullCalendar burada oluşturulacak -->
        <div id="calendar"></div>
    </div>
</div>

<!-- Etkinlik Detaylarını Göstermek İçin Modal (Opsiyonel ama şık) -->
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
                <dl class="row">
                    <dt class="col-sm-4">Kategori:</dt>
                    <dd class="col-sm-8" id="eventCategory"></dd>
                    <dt class="col-sm-4">Tarih ve Saat:</dt>
                    <dd class="col-sm-8" id="eventStart"></dd>
                    <dt class="col-sm-4">Açıklama:</dt>
                    <dd class="col-sm-8" id="eventDescription"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <a href="#" id="editEventButton" class="btn btn-primary">Düzenle</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
// Bu script, layout.php'nin en sonunda, FullCalendar kütüphanesi yüklendikten sonra çalışır.
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if(calendarEl) { // Takvim elementinin var olduğundan emin ol
        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            initialView: 'dayGridMonth',
            locale: 'tr', // Türkçe dil dosyası layout'ta yüklendi
            
            // DÜZELTİLMİŞ KISIM: Veri kaynağı olarak controller'daki doğru eylemi çağırıyoruz.
            events: 'index.php?module=activities&action=calendar_data',

            eventClick: function(info) {
                // Tıklanan etkinliğin varsayılan URL'ine gitmesini engelle
                info.jsEvent.preventDefault(); 
                
                // Modal içindeki alanları doldur
                document.getElementById('modalTitle').innerText = info.event.title;
                document.getElementById('eventCategory').innerText = info.event.extendedProps.category;
                document.getElementById('eventDescription').innerText = info.event.extendedProps.description;
                document.getElementById('eventStart').innerText = info.event.start.toLocaleString('tr-TR', { dateStyle: 'long', timeStyle: 'short' });
                
                // Düzenle butonunun linkini ayarla
                var editButton = document.getElementById('editEventButton');
                if(info.event.url) {
                    editButton.href = info.event.url;
                    editButton.style.display = 'inline-block';
                } else {
                    editButton.style.display = 'none';
                }

                // Modal'ı göster (jQuery gerekli)
                $('#eventDetailModal').modal('show');
            }
        });
        calendar.render();
    }
});
</script>
