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
            <div class="card-header" style="background-color: #6f42c1; color: white;">
                <h5 class="mb-0">
                    <i class="fa fa-user-friends"></i> Rehberlik Seansları İçe Aktar
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">📋 CSV Formatı</h6>
                    <p class="mb-2"><strong>Zorunlu Alanlar:</strong></p>
                    <ul class="mb-3">
                        <li><strong>Öğrenci</strong> (Ad Soyad veya Öğrenci Numarası)</li>
                        <li><strong>Görüşme Tarihi</strong></li>
                        <li><strong>Konu</strong> (Görüşme başlığı)</li>
                        <li><strong>Görüşme Notları</strong></li>
                    </ul>
                    <p class="mb-2"><strong>Opsiyonel Alanlar:</strong></p>
                    <ul class="mb-0">
                        <li>Rehber/Danışman (Öğretmen adı)</li>
                        <li>Sonraki Adımlar</li>
                    </ul>
                </div>

                <form action="index.php?module=import&action=processGuidance" 
                      method="post" 
                      enctype="multipart/form-data">
                    
                    <div class="mb-4">
                        <label class="form-label">CSV Dosyası <span class="text-danger">*</span></label>
                        <input type="file" 
                               name="csv_file" 
                               class="form-control" 
                               accept=".csv"
                               required>
                        <small class="text-muted">
                            Excel'den "Farklı Kaydet" → "CSV (Noktalı virgülle ayrılmış)" formatında kaydedin
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn text-white" style="background-color: #6f42c1;">
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
                <h6 class="mb-0">📝 Örnek CSV Formatı</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded small" style="overflow-x:auto;">Öğrenci;Görüşme Tarihi;Konu;Görüşme Notları;Rehber
Ahmet Yılmaz;15.01.2024;Akademik Destek;Matematik dersinde zorlanıyor. Ek çalışma planı yapıldı.;Ali Veli
12345;20.01.2024;Sosyal Uyum;Arkadaş ilişkilerinde gelişme var. Takip edilecek.;Ayşe Kaya
Mehmet Demir;25.01.2024;Kariyer Danışmanlığı;Lise tercihleri konuşuldu. Rehberlik testleri önerildi.;Fatma Öz</pre>
                <hr>
                <h6>Tarih Formatları:</h6>
                <ul class="small mb-0">
                    <li><code>15.01.2024</code> - Türkçe format (GG.AA.YYYY)</li>
                    <li><code>2024-01-15</code> - ISO format (YYYY-MM-DD)</li>
                    <li><code>15/01/2024</code> - Slash format (GG/AA/YYYY)</li>
                </ul>
            </div>
        </div>
    </div>
</div>