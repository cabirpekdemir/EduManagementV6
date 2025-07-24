<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Sınıfı Düzenle: <?= htmlspecialchars($class['name'] ?? '') ?></h3>
    </div>
    <div class="card-body">
        <?php
        // DÜZELTME: Formun gönderileceği adres, 'update' action'ı olarak doğru bir şekilde belirleniyor.
        $formAction = '?module=classes&action=update';

        // Ortak form dosyasını çağırıyoruz.
        require_once 'form.php';
        ?>
    </div>
    </div>