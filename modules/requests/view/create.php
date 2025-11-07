<?php
/** @var array $currentUser */
$currentUser = $currentUser ?? ['role'=>'guest','id'=>0];
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

$base = htmlspecialchars($_SERVER['PHP_SELF'] ?? '/index.php', ENT_QUOTES, 'UTF-8');
?>
<section class="content-header">
  <div class="d-flex justify-content-between align-items-center">
    <h1>Talepte Bulun</h1>
    <div class="btn-group">
      <a href="<?= $base ?>?module=requests&action=index" class="btn btn-default">
        <i class="fa fa-list"></i> Listeye Dön
      </a>
      <a href="<?= $base ?>?module=requests&action=create&ts=<?= time() ?>" class="btn btn-outline-secondary">
        <i class="fa fa-refresh"></i> Yeniden Yükle
      </a>
    </div>
  </div>
</section>

<section class="content">
  <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?>">
      <?= htmlspecialchars($flash['message'] ?? '') ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <form method="post" action="<?= $base ?>?module=requests&action=store" enctype="application/x-www-form-urlencoded">
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="type">Tür</label>
              <select class="form-control" id="type" name="type">
                <option value="">— Seçiniz —</option>
                <option value="izin">İzin</option>
                <option value="rehberlik">Rehberlik</option>
                <option value="ders">Ders</option>
                <option value="genel">Genel</option>
              </select>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label for="priority">Öncelik</label>
              <select class="form-control" id="priority" name="priority">
                <option value="">Normal</option>
                <option value="low">Düşük</option>
                <option value="high">Yüksek</option>
                <option value="urgent">Acil</option>
              </select>
            </div>
          </div>

          <!-- Öğrenci ID yerine serbest metin ad-soyad -->
          <div class="col-md-3">
            <div class="form-group">
              <label for="student_name">Öğrenci Adı Soyadı (ops.)</label>
              <input type="text" class="form-control" id="student_name" name="student_name" placeholder="Örn: Ali Yılmaz">
              <small class="text-muted">Şema uygunsa <code>student_name</code> kolonuna yazılır.</small>
            </div>
          </div>

          <!-- MUHATAP -->
          <div class="col-md-3">
            <div class="form-group">
              <label for="recipient">Kime?</label>
              <select class="form-control" id="recipient" name="recipient">
                <option value="">— Seçiniz —</option>
                <option value="admin">Yönetici (Admin)</option>
                <option value="advisor_teacher">Danışman/Öğretmen (öğrencinin/velinin)</option>
                <option value="other">Diğer</option>
              </select>
              <small class="text-muted">Şema uygunsa <code>assigned_role</code> olarak <em>advisor_teacher</em> saklanır.</small>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="title">Başlık</label>
          <input type="text" class="form-control" id="title" name="title" required maxlength="255">
        </div>

        <div class="form-group">
          <label for="body">Açıklama</label>
          <textarea class="form-control" id="body" name="body" rows="6" required></textarea>
        </div>
      </div>

      <div class="card-footer d-flex gap-2">
        <a href="<?= $base ?>?module=requests&action=index" class="btn btn-default">İptal</a>
        <button type="submit" class="btn btn-primary">Gönder</button>
      </div>
    </form>
  </div>
</section>
