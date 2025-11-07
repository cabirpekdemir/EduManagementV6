<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}
?>

<div class="row g-4">
    <!-- Öğrenci İçe Aktar -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fa fa-users fa-3x text-primary"></i>
                </div>
                <h5>Öğrenci İçe Aktar</h5>
                <p class="text-muted small">
                    Toplu öğrenci kaydı yapın
                </p>
                <a href="index.php?module=students&action=import" class="btn btn-primary">
                    <i class="fa fa-upload"></i> Başla
                </a>
            </div>
        </div>
    </div>

    <!-- Öğretmen İçe Aktar -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fa fa-chalkboard-teacher fa-3x text-success"></i>
                </div>
                <h5>Öğretmen İçe Aktar</h5>
                <p class="text-muted small">
                    Toplu öğretmen kaydı yapın
                </p>
                <a href="index.php?module=import&action=teachers" class="btn btn-success">
                    <i class="fa fa-upload"></i> Başla
                </a>
            </div>
        </div>
    </div>

    <!-- Ders İçe Aktar -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fa fa-book fa-3x text-info"></i>
                </div>
                <h5>Ders İçe Aktar</h5>
                <p class="text-muted small">
                    Toplu ders kaydı yapın
                </p>
                <a href="index.php?module=import&action=courses" class="btn btn-info text-white">
                    <i class="fa fa-upload"></i> Başla
                </a>
            </div>
        </div>
    </div>

    <!-- Rehberlik Seansları İçe Aktar - YENİ -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-purple">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fa fa-user-friends fa-3x text-purple"></i>
                </div>
                <h5>Rehberlik Seansları</h5>
                <p class="text-muted small">
                    Geçmiş rehberlik seanslarını içe aktar
                </p>
                <a href="index.php?module=import&action=guidance" class="btn btn-purple">
                    <i class="fa fa-upload"></i> Başla
                </a>
            </div>
        </div>
    </div>

    <!-- Randevu Kayıtları İçe Aktar - YENİ -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-teal">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fa fa-calendar-check fa-3x text-teal"></i>
                </div>
                <h5>Randevu Kayıtları</h5>
                <p class="text-muted small">
                    Geçmiş randevu kayıtlarını içe aktar
                </p>
                <a href="index.php?module=import&action=appointments" class="btn btn-teal">
                    <i class="fa fa-upload"></i> Başla
                </a>
            </div>
        </div>
    </div>
</div>

<!-- CSV Format Kuralları -->
<div class="card shadow-sm mt-4">
    <div class="card-header">
        <h6 class="mb-0">📋 CSV Format Kuralları</h6>
    </div>
    <div class="card-body">
        <ul class="mb-0">
            <li>CSV dosyası <strong>noktalı virgül (;)</strong> veya <strong>virgül (,)</strong> ile ayrılmış olabilir</li>
            <li>İlk satır başlık satırı olmalıdır</li>
            <li>Excel'den "Farklı Kaydet" → "CSV (Noktalı virgülle ayrılmış)" formatında kaydedin</li>
            <li>Türkçe karakterler desteklenir</li>
        </ul>
    </div>
</div>

<!-- Örnek Formatlar -->
<div class="row g-4 mt-2">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header bg-light">
                <strong>Öğrenci CSV Örneği</strong>
            </div>
            <div class="card-body">
                <pre class="small mb-0" style="font-size:11px;">Ad Soyad;T.C. Kimlik;Sınıf;Durum
Ahmet Y.;12345678901;5-A;Aktif
Ayşe D.;98765432109;5-B;Ön Kayıt</pre>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-header bg-light">
                <strong>Öğretmen CSV Örneği</strong>
            </div>
            <div class="card-body">
                <pre class="small mb-0" style="font-size:11px;">Ad Soyad;E-posta;Telefon;Branş
Ali Veli;ali@okul.com;5551234567;Matematik
Fatma K.;fatma@okul.com;5559876543;Türkçe</pre>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-header bg-light">
                <strong>Ders CSV Örneği</strong>
            </div>
            <div class="card-body">
                <pre class="small mb-0" style="font-size:11px;">Ders Adı;Kademe;Öğretmen
Matematik;Ortaokul;Ali Veli
Türkçe;İlkokul;Fatma K.</pre>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-header bg-light">
                <strong>Rehberlik CSV Örneği</strong>
            </div>
            <div class="card-body">
                <pre class="small mb-0" style="font-size:11px;">Öğrenci;Görüşme Tarihi;Konu
Ahmet Y.;15.01.2024;Akademik Destek
Ayşe D.;20.01.2024;Sosyal Uyum</pre>
            </div>
        </div>
    </div>
</div>

<style>
/* Yeni renkler için CSS */
.btn-purple {
    background-color: #6f42c1;
    color: white;
    border-color: #6f42c1;
}
.btn-purple:hover {
    background-color: #5a32a3;
    color: white;
    border-color: #5a32a3;
}
.btn-teal {
    background-color: #20c997;
    color: white;
    border-color: #20c997;
}
.btn-teal:hover {
    background-color: #1aa179;
    color: white;
    border-color: #1aa179;
}
.text-purple {
    color: #6f42c1 !important;
}
.text-teal {
    color: #20c997 !important;
}
.border-purple {
    border-left: 4px solid #6f42c1 !important;
}
.border-teal {
    border-left: 4px solid #20c997 !important;
}
</style>