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
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fa fa-chalkboard-teacher"></i> Öğretmen İçe Aktar
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">📋 Gerekli Alanlar</h6>
                    <ul class="mb-0">
                        <li><strong>Ad Soyad</strong> (zorunlu)</li>
                        <li><strong>E-posta</strong> (zorunlu, benzersiz)</li>
                        <li>Telefon (opsiyonel)</li>
                        <li>Branş (opsiyonel)</li>
                        <li>T.C. Kimlik No (opsiyonel)</li>
                    </ul>
                    <hr>
                    <small>Varsayılan şifre: <code>123456</code></small>
                </div>

                <form action="index.php?module=import&action=processTeachers" 
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
                        <button type="submit" class="btn btn-success">
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
                <h6 class="mb-0">Örnek CSV Formatı</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded" style="font-size:12px;">Ad Soyad;E-posta;Telefon;Branş;T.C. Kimlik No
Ali Veli;ali.veli@okul.com;05551234567;Matematik;12345678901
Ayşe Yılmaz;ayse.yilmaz@okul.com;05559876543;Türkçe;98765432109
Mehmet Demir;mehmet.demir@okul.com;05557654321;Fen Bilgisi;45678912345</pre>
            </div>
        </div>
    </div>
</div>