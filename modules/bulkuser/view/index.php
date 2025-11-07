<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$success_count = $success_count ?? 0;
$skipped_count = $skipped_count ?? 0;
$error_count = $error_count ?? 0;
$error_message = $error_message ?? null;
?>

<div class="mb-3">
    <a href="index.php?module=students&action=list" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Öğrenci Listesine Dön
    </a>
    <a href="index.php?module=bulkuser&action=download_template" class="btn btn-success btn-sm">
        <i class="fa fa-download"></i> Örnek Şablon İndir
    </a>
</div>

<h2 class="mb-4">📥 Toplu Kullanıcı Ekleme</h2>
<p class="lead text-muted">
    CSV dosyası yükleyerek veya Excel'den kopyala-yapıştır yaparak toplu kullanıcı ekleyin.
</p>

<!-- Bildirim Mesajları -->
<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Hata:</strong> <?= h($error_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success_count > 0 || $skipped_count > 0 || $error_count > 0): ?>
    <?php if ($success_count > 0): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <strong>✅ Başarılı!</strong> <?= (int)$success_count ?> kullanıcı başarıyla eklendi.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($skipped_count > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <strong>⚠️ Uyarı!</strong> <?= (int)$skipped_count ?> kullanıcı atlandı (geçersiz veri veya mükerrer kayıt).
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_count > 0): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>❌ Hata!</strong> <?= (int)$error_count ?> kullanıcı eklenirken hata oluştu.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row">
    <!-- CSV Yükleme -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fa fa-file-csv"></i> Yöntem 1: CSV Dosyası Yükle
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <strong>Önerilen yöntem.</strong> CSV dosyanızı hazırlayıp yükleyin.
                </p>
                
                <div class="alert alert-info">
                    <strong>💡 İpucu:</strong> Önce şablon dosyasını indirin, doldurun ve yükleyin.
                </div>

                <form action="index.php?module=bulkuser&action=csv_upload" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">CSV Dosyası Seçin</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                        <small class="text-muted">Sadece .csv veya .txt dosyaları kabul edilir</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-upload"></i> Dosyayı Yükle ve İşle
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kopyala-Yapıştır -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fa fa-paste"></i> Yöntem 2: Excel'den Yapıştır
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Excel veya Google Sheets'ten doğrudan kopyalayıp yapıştırın.
                </p>
                
                <div class="alert alert-warning">
                    <strong>⚠️ Dikkat:</strong> Sütunlar <strong>Tab</strong> ile ayrılmalı (Excel'den direkt kopyala-yapıştır).
                </div>

                <form action="index.php?module=bulkuser&action=paste_upload" method="post">
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Verilerini Yapıştırın</label>
                        <textarea name="user_data" rows="8" class="form-control" 
                                  style="font-family: monospace; font-size: 12px;" 
                                  placeholder="Excel'den seçip Ctrl+C ile kopyalayın, buraya Ctrl+V ile yapıştırın..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa fa-check"></i> Verileri İşle ve Ekle
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sütun Bilgileri -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">📋 CSV/Excel Sütun Sırası ve Açıklamaları</h5>
    </div>
    <div class="card-body">
        <p class="alert alert-info mb-3">
            <strong>Önemli:</strong> CSV dosyanızda veya Excel'de yapıştırdığınız veride sütunlar aşağıdaki sırada olmalıdır.
            İlk satır başlık ise otomatik atlanır.
        </p>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th style="width:180px">Sütun Adı</th>
                        <th>Açıklama</th>
                        <th style="width:150px">Örnek</th>
                        <th style="width:100px">Zorunlu</th>
                    </tr>
                </thead>
                <tbody style="font-size:13px;">
                    <tr>
                        <td>1</td>
                        <td><strong>Ad Soyad</strong></td>
                        <td>Kullanıcının tam adı</td>
                        <td>Ahmet Yılmaz</td>
                        <td><span class="badge bg-danger">Evet</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><strong>E-posta</strong></td>
                        <td>Benzersiz e-posta adresi</td>
                        <td>ahmet@ornek.com</td>
                        <td><span class="badge bg-danger">Evet</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><strong>Şifre</strong></td>
                        <td>Giriş şifresi (düz metin)</td>
                        <td>Parola123</td>
                        <td><span class="badge bg-danger">Evet</span></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><strong>Rol</strong></td>
                        <td>student, teacher, parent, admin</td>
                        <td>student</td>
                        <td><span class="badge bg-danger">Evet</span></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td><strong>TC Kimlik</strong></td>
                        <td>11 haneli TC kimlik numarası</td>
                        <td>12345678901</td>
                        <td><span class="badge bg-danger">Evet</span></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td><strong>Öğrenci No</strong></td>
                        <td>Benzersiz öğrenci numarası</td>
                        <td>2024001</td>
                        <td><span class="badge bg-secondary">Hayır</span></td>
                    </tr>
                    <tr>
                        <td>7-9</td>
                        <td><strong>Telefon 1,2,3</strong></td>
                        <td>İletişim telefonları</td>
                        <td>05321234567</td>
                        <td><span class="badge bg-secondary">Hayır</span></td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td><strong>Okul</strong></td>
                        <td>Okul adı</td>
                        <td>75 YIL İLKOKULU</td>
                        <td><span class="badge bg-secondary">Hayır</span></td>
                    </tr>
                    <tr>
                        <td>11</td>
                        <td><strong>Sınıf</strong></td>
                        <td>Sınıf/şube bilgisi</td>
                        <td>3-A</td>
                        <td><span class="badge bg-secondary">Hayır</span></td>
                    </tr>
                    <tr>
                        <td>12</td>
                        <td><strong>Sınıf ID</strong></td>
                        <td>Sistemdeki sınıf ID'si</td>
                        <td>1</td>
                        <td><span class="badge bg-secondary">Hayır</span></td>
                    </tr>
                    <tr>
                        <td>13</td>
                        <td><strong>Öğretim Türü</strong></td>
                        <td>tam_gun, sabahci, oglenci</td>
                        <td>tam_gun</td>
                        <td><span class="badge bg-secondary">Hayır</span></td>
                    </tr>
                    <tr>
                        <td>14</td>
                        <td><strong>Özel Yetenek</strong></td>
                        <td>evet/hayır veya 1/0</td>
                        <td>hayır</td>
                        <td><span class="badge bg-secondary">Hayır</span></td>
                    </tr>
                    <tr>
                        <td>15</td>
                        <td><strong>Durum</strong></td>
                        <td>on_kayit, aktif, mezun, vb</td>
                        <td>aktif</td>
                        <td><span class="badge bg-secondary">Hayır</span></td>
                    </tr>
                    <tr>
                        <td>16-22</td>
                        <td><strong>Diğer Bilgiler</strong></td>
                        <td>Doğum yeri, tarihi, cinsiyet, adres, anne/baba/veli adı</td>
                        <td>—</td>
                        <td><span class="badge bg-secondary">Hayır</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="alert alert-success mt-3">
            <strong>✅ En kolay yöntem:</strong>
            <ol class="mb-0">
                <li>Yukarıdaki "Örnek Şablon İndir" butonuna tıklayın</li>
                <li>İndirilen CSV dosyasını Excel ile açın</li>
                <li>Öğrenci bilgilerini doldurun</li>
                <li>Dosyayı kaydedin ve yükleyin</li>
            </ol>
        </div>
    </div>
</div>