<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Yeni Öğrenci Ekle</h3>
    </div>
    <div class="card-body">
        <?php
        // Formun gönderileceği adresi (action) belirliyoruz.
        $formAction = '?module=students&action=store';
        // Ortak form dosyasını çağırıyoruz.
        require_once 'form.php';
        ?>
    </div>
</div>