<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}
?>

<div class="mb-3">
    <a href="index.php?module=students&action=list" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Listeye Dön
    </a>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fa fa-upload"></i> Öğrenci İçe Aktar
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">📋 Bilgilendirme</h6>
                    <ul class="mb-0">
                        <li>CSV dosyası <strong>noktalı virgül (;)</strong> ile ayrılmış olmalı</li>
                        <li>İlk satır başlık satırı olmalı</li>
                        <li>Zorunlu alanlar: <strong>Adı Soyadı, T.C. Kimlik No</strong></li>
                        <li>TC Kimlik numarası zaten kayıtlı olanlar atlanacak</li>
                        <li>Varsayılan şifre: <code>123456</code></li>
                    </ul>
                </div>

                <form action="index.php?module=students&action=processImport" 
                      method="post" 
                      enctype="multipart/form-data">
                    
                    <div class="mb-4">
                        <label class="form-label">CSV Dosyası Seçin <span class="text-danger">*</span></label>
                        <input type="file" 
                               name="csv_file" 
                               class="form-control" 
                               accept=".csv,.txt"
                               required>
                        <small class="text-muted">
                            Excel'den "Farklı Kaydet" → "CSV (Noktalı virgülle ayrılmış)" formatında kaydedin
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-upload"></i> İçe Aktar
                        </button>
                        <a href="index.php?module=students&action=list" class="btn btn-outline-secondary">
                            İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Örnek CSV Formatı -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h6 class="mb-0">Beklenen CSV Formatı</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded" style="font-size:12px;overflow-x:auto;">Adı Soyadı;T.C. Kimlik No;Öğrenci No;Sınıf;Durum Açıklama;Okul Adı;Öğretim Türü;Doğum Yeri;Doğum Tarihi;Tel-1;Tel-2;Tel-3;Özel Yetenek
Ahmet Yılmaz;12345678901;1001;5-A;Aktif;Örnek İlkokulu;Tam Gün;İstanbul;2015-05-20;05551234567;05552345678;;Y
Ayşe Demir;98765432109;1002;5-B;Ön Kayıt;Örnek Ortaokulu;Sabahçı;Ankara;2014-08-15;05559876543;;;N</pre>
            </div>
        </div>
    </div>
</div>