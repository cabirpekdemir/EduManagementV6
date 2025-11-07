<div class="card"><div class="card-body">
  <h4 class="text-danger mb-2">Hata</h4>
  <p><?= htmlspecialchars($message ?? 'Doğrulama hatası.', ENT_QUOTES, 'UTF-8') ?></p>
  <a class="btn btn-secondary" href="index.php">Ana Sayfa</a>
</div></div>
