<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}
?>

<div class="mb-3">
    <a href="index.php?module=import&action=index" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Import Menüsü
    </a>
</div>

<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header" style="background-color: #20c997; color: white;">
                <h5 class="mb-0">
                    <i class="fa fa-calendar-check"></i> Randevu Kayıtları İçe Aktar
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">📋 CSV Formatı</h6>
                    <p class="mb-2"><strong>Zorunlu Alanlar:</strong></p>
                    <ul class="mb-3">
                        <li><strong>Öğrenci</strong> (Ad Soyad veya Numara)</li>
                        <li><strong>Talep Tarihi</strong></li>
                        <li><strong>Sebep/Konu</strong></li>
                    </ul>
                    <p class="mb-2"><strong>Opsiyonel Alanlar:</strong></p>
                    <ul class="mb-0">
                        <li>Talep Saati (varsayılan: 09:00)</li>
                        <li>Durum (Bekliyor/Onaylandı/Tamamlandı/Reddedildi/İptal)</li>
                        <li>Randevu Tarihi</li>
                        <li>Randevu Saati</li>
                        <li>Rehber/Danışman</li>
                        <li>Rehber Notları</li>
                    </ul>
                </div>

                <form action="index.php?module=import&action=processAppointments" 
                      method="post" 
                      enctype="multipart/form-data">
                    
                    <div class="mb-4">
                        <label class="form-label">CSV Dosyası <span class="text-danger">*</span></label>
                        <input type="file" 
                               name="csv_file" 
                               class="form-control" 
                               accept=".csv"
                               required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn text-white" style="background-color: #20c997;">
                            <i class="fa fa-upload"></i> İçe Aktar
                        </button>
                        <a href="index.php?module=import&action=index" class="btn btn-outline-secondary">
                            İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Örnek Format -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h6 class="mb-0">📝 Örnek CSV Formatı - TAM</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded small" style="overflow-x:auto;">Öğrenci;Talep Tarihi;Talep Saati;Sebep;Durum;Randevu Tarihi;Randevu Saati;Rehber;Rehber Notları
Ahmet Y.;10.01.2024;09:00;Sınav kaygısı;Tamamlandı;15.01.2024;10:00;Ali Veli;Başarılı görüşme yapıldı
12345;12.01.2024;14:00;Kariyer danışmanlığı;Onaylandı;18.01.2024;14:30;Ayşe Kaya;
Mehmet D.;15.01.2024;11:00;Arkadaş ilişkileri;Bekliyor;;;;;</pre>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h6 class="mb-0">📝 Örnek CSV Formatı - MİNİMAL</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded small" style="overflow-x:auto;">Öğrenci;Talep Tarihi;Sebep
Ahmet Yılmaz;10.01.2024;Sınav kaygısı danışmanlığı
12345;12.01.2024;Kariyer planlama görüşmesi
Mehmet Demir;15.01.2024;Arkadaş ilişkileri hakkında destek</pre>
            </div>
        </div>

        <!-- Durum Açıklamaları -->
        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h6 class="mb-0">📖 Durum Açıklamaları</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Durum</th>
                            <th>Açıklama</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-warning">Bekliyor</span></td>
                            <td>Randevu talebi onay bekliyor</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">Onaylandı</span></td>
                            <td>Randevu onaylandı, gerçekleştirilecek</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-info">Tamamlandı</span></td>
                            <td>Randevu gerçekleştirildi</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-danger">Reddedildi</span></td>
                            <td>Randevu talebi reddedildi</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-secondary">İptal</span></td>
                            <td>Randevu iptal edildi</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>