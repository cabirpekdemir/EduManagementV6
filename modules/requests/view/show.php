<?php
/** DATA VARS (controller'dan gelmeli)
 * @var array $request
 * @var array $replies
 * @var array $currentUser
 */
$request     = $request     ?? [];
$replies     = $replies     ?? [];
$currentUser = $currentUser ?? ['role'=>'guest','id'=>0];

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

function val($arr, $k, $fallback='—'){
  return isset($arr[$k]) && $arr[$k]!=='' ? htmlspecialchars((string)$arr[$k]) : $fallback;
}

$role      = $currentUser['role'] ?? 'guest';
$userId    = $currentUser['id']   ?? 0;
$canReply  = in_array($role, ['teacher','admin'], true) || $userId === ($request['created_by'] ?? -1);

$hasStatus   = array_key_exists('status', $request);
$hasPriority = array_key_exists('priority', $request);
$hasType     = array_key_exists('type', $request);
$hasStudent  = array_key_exists('student_name', $request) || array_key_exists('student_id', $request);
$hasParent   = array_key_exists('parent_name',  $request) || array_key_exists('parent_id',  $request);
$hasAssigned = array_key_exists('assigned_to_name', $request) || array_key_exists('assigned_to', $request);

$base = htmlspecialchars($_SERVER['PHP_SELF'] ?? '/index.php', ENT_QUOTES, 'UTF-8');
?>
<section class="content-header">
  <div class="d-flex justify-content-between align-items-center">
    <h1>Talep #<?= htmlspecialchars((string)($request['id'] ?? '')) ?></h1>
    <div class="btn-group">
      <a href="<?= $base ?>?module=requests&action=index" class="btn btn-default">
        <i class="fa fa-list"></i> Listeye Dön
      </a>
      <a href="<?= $base ?>?module=requests&action=show&id=<?= urlencode($request['id'] ?? '') ?>&ts=<?= time() ?>"
         class="btn btn-outline-secondary">
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

  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header"><strong><?= val($request,'title') ?></strong></div>
        <div class="card-body">
          <?php if ($hasType || $hasPriority || $hasStatus || $hasAssigned): ?>
            <div class="mb-3">
              <?php if ($hasType): ?><span class="badge bg-info mr-1"><?= val($request,'type') ?></span><?php endif; ?>
              <?php if ($hasPriority): ?><span class="badge bg-primary mr-1"><?= val($request,'priority') ?></span><?php endif; ?>
              <?php if ($hasStatus): ?>
                <span class="badge bg-<?= ($request['status']??'')==='closed'?'secondary':(($request['status']??'')==='in_progress'?'warning':'success') ?> mr-1">
                  <?= val($request,'status') ?>
                </span>
              <?php endif; ?>
              <?php if ($hasAssigned): ?>
                <span class="badge bg-dark">
                  Atanan: <?= htmlspecialchars($request['assigned_to_name'] ?? ('#'.($request['assigned_to']??'—'))) ?>
                </span>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <p class="mb-0" style="white-space:pre-wrap;"><?= val($request,'body') ?></p>
          <hr>
          <div class="text-muted">
            Oluşturan: <?= htmlspecialchars($request['created_by_name'] ?? ('#'.($request['created_by']??'—'))) ?>
            · Oluşturma: <?= val($request,'created_at') ?>
            <?php if (!empty($request['updated_at'])): ?> · Güncelleme: <?= val($request,'updated_at') ?><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><strong>Cevaplar</strong></div>
        <div class="card-body">
          <?php if (empty($replies)): ?>
            <div class="text-muted">Henüz cevap yok.</div>
          <?php else: foreach ($replies as $rep): ?>
            <div class="mb-3 p-3 border rounded">
              <div class="small text-muted mb-2">
                <?= htmlspecialchars($rep['user_name'] ?? ('#'.($rep['user_id']??'—'))) ?> · <?= val($rep,'created_at') ?>
              </div>
              <div style="white-space:pre-wrap;"><?= val($rep,'body') ?></div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <?php if ($canReply): ?>
        <div class="card"><form method="post" action="<?= $base ?>?module=requests&action=store_reply">

            <input type="hidden" name="request_id" value="<?= htmlspecialchars((string)($request['id'] ?? '')) ?>">
            <div class="card-header"><strong>Cevap Yaz</strong></div>
            <div class="card-body">
              <div class="form-group">
                <textarea name="body" rows="4" class="form-control" placeholder="Yanıtınız..." required></textarea>
              </div>
              <?php if ($hasStatus && in_array($role, ['teacher','admin'], true)): ?>
                <div class="form-group">
                  <label for="status">Durum (opsiyonel)</label>
                  <select id="status" name="status" class="form-control">
                    <option value="">— Değiştirme —</option>
                    <option value="open">Açık</option>
                    <option value="in_progress">Üzerinde Çalışılıyor</option>
                    <option value="closed">Kapalı</option>
                  </select>
                </div>
              <?php endif; ?>
            </div>
            <div class="card-footer d-flex gap-2">
              <button class="btn btn-primary" type="submit">Gönder</button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <div class="card-header"><strong>İlgili</strong></div>
        <div class="card-body">
          <?php if ($hasStudent): ?>
            <div>Öğrenci: <?= htmlspecialchars($request['student_name'] ?? ('#'.($request['student_id']??'—'))) ?></div>
          <?php endif; ?>
          <?php if ($hasParent): ?>
            <div>Veli: <?= htmlspecialchars($request['parent_name'] ?? ('#'.($request['parent_id']??'—'))) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
