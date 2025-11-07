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
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fa fa-book"></i> Ders İçe Aktar
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">📋 CSV Formatı</h6>
                    <p class="mb-2"><strong>Zorunlu Alanlar:</strong></p>
                    <ul class="mb-3">
                        <li><strong>Ders Adı</strong></li>
                    </ul>
                    <p class="mb-2"><strong>Opsiyonel Alanlar:</strong></p>
                    <ul class="mb-0">
                        <li>Dönem (örn: 2024-2025 Güz)</li>
                        <li>Ders Kodu (örn: MAT101)</li>
                        <li>Tipi (Zorunlu / Seçmeli)</li>
                        <li>Kategori (İlkokul / Ortaokul / Ortaokul I / Ortaokul II / Lise)</li>
                        <li>Eğitmen (öğretmen adı - sistem otomatik eşleştirir)</li>
                        <li>Kademe (örn: 5-6, 7-8, 9-12)</li>
                        <li>Gün (Pazartesi, Salı, vb.)</li>
                        <li>Başlangıç Zamanı (örn: 09:00)</li>
                        <li>Bitiş Zamanı (örn: 10:30)</li>
                        <li>Kontenjan (sayı)</li>
                    </ul>
                </div>

                <form action="index.php?module=import&action=processCourses" 
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
                            Excel'den "Farklı Kaydet" → "CSV (Noktalı virgülle ayrılmış)" olarak kaydedin
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fa fa-upload"></i> İçe Aktar
                        </button>
                        <a href="index.php?module=import&action=index" class="btn btn-outline-secondary">
                            İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Örnek Format - TAM -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h6 class="mb-0">📝 Örnek CSV Formatı (Tüm Alanlar)</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded small" style="overflow-x:auto;">Dönem;Ders Kodu;Ders Adı;Tipi;Kategori;Eğitmen;Kademe;Gün;Başlangıç Zamanı;Bitiş Zamanı;Kontenjan
2024-2025 Güz;MAT101;Matematik;Zorunlu;Ortaokul;Ali Veli;5-6;Pazartesi;09:00;10:30;25
2024-2025 Güz;TUR101;Türkçe;Zorunlu;İlkokul;Ayşe Yılmaz;3-4;Salı;10:45;12:15;30
2024-2025 Güz;FEN201;Fen Bilgisi;Zorunlu;Ortaokul I;Mehmet Demir;5-6;Çarşamba;13:00;14:30;20
2024-2025 Güz;AST301;Astronomi;Seçmeli;Ortaokul II;Fatma Kaya;7-8;Perşembe;14:45;16:15;15</pre>
            </div>
        </div>

        <!-- Örnek Format - Minimal -->
        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h6 class="mb-0">📝 Örnek CSV Formatı (Sadece Zorunlu)</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded small" style="overflow-x:auto;">Ders Adı
Matematik
Türkçe
Fen Bilgisi
Astronomi</pre>
            </div>
        </div>

        <!-- Alan Açıklamaları -->
        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h6 class="mb-0">📖 Alan Açıklamaları</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Alan</th>
                            <th>Açıklama</th>
                            <th>Örnek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Dönem</strong></td>
                            <td>Akademik dönem</td>
                            <td>2024-2025 Güz, 2024-2025 Bahar</td>
                        </tr>
                        <tr>
                            <td><strong>Ders Kodu</strong></td>
                            <td>Dersin benzersiz kodu</td>
                            <td>MAT101, TUR201</td>
                        </tr>
                        <tr>
                            <td><strong>Tipi</strong></td>
                            <td>Zorunlu veya Seçmeli</td>
                            <td>Zorunlu, Seçmeli</td>
                        </tr>
                        <tr>
                            <td><strong>Kategori</strong></td>
                            <td>Eğitim seviyesi</td>
                            <td>İlkokul, Ortaokul, Ortaokul I, Ortaokul II, Lise</td>
                        </tr>
                        <tr>
                            <td><strong>Kademe</strong></td>
                            <td>Sınıf aralığı</td>
                            <td>1-4, 5-6, 7-8, 9-12</td>
                        </tr>
                        <tr>
                            <td><strong>Gün</strong></td>
                            <td>Ders günü</td>
                            <td>Pazartesi, Salı, Çarşamba, ...</td>
                        </tr>
                        <tr>
                            <td><strong>Başlangıç/Bitiş</strong></td>
                            <td>Ders saatleri (HH:MM)</td>
                            <td>09:00, 10:30, 14:45</td>
                        </tr>
                        <tr>
                            <td><strong>Kontenjan</strong></td>
                            <td>Maksimum öğrenci sayısı</td>
                            <td>20, 25, 30</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>