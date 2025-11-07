<?php
if(!function_exists('e')){ function e($v){ return htmlspecialchars((string)($v??''),ENT_QUOTES,'UTF-8'); } }
$base=e($_SERVER['PHP_SELF']??'index.php');
$users=$users??[]; $filter=$filter??['role'=>'','q'=>''];
$role=strtolower($filter['role'] ?? '');
?>
<div class="card">
  <div class="card-header"><strong>Kullanıcı Listesi</strong></div>
  <div class="card-body">
    <form class="row g-2 mb-3" method="get" action="<?= $base ?>">
      <input type="hidden" name="module" value="users"><input type="hidden" name="action" value="index">
      <div class="col-md-3">
        <select name="role" class="form-control">
          <option value="">Tüm Roller</option>
          <?php foreach(['student'=>'Öğrenci','teacher'=>'Öğretmen','parent'=>'Veli','admin'=>'Admin'] as $k=>$t): ?>
            <option value="<?= e($k) ?>" <?= $role===$k?'selected':'' ?>><?= e($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-5"><input name="q" class="form-control" value="<?= e($filter['q']??'') ?>" placeholder="Ad soyad/e-posta ara…"></div>
      <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filtrele</button></div>
      <div class="col-md-2"><a class="btn btn-outline-dark w-100" href="<?= $base ?>?module=users&action=index">Sıfırla</a></div>
    </form>

    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
        <tr>
          <th style="width:70px">ID</th>
          <th>Ad Soyad</th>
          <th style="width:220px">Email</th>
          <?php if($role==='teacher'): ?>
            <th style="width:200px">Branş</th>
            <th style="width:220px">Mezun Olduğu Okul</th>
            <th style="width:140px">Kademe</th>
          <?php elseif($role==='parent'): ?>
            <th style="width:220px">İşyeri</th>
            <th style="width:180px">Meslek</th>
          <?php elseif($role==='student'): ?>
            <th style="width:160px">Sınıf</th>
          <?php else: ?>
            <th style="width:140px">Rol</th>
          <?php endif; ?>
          <th style="width:150px">Telefon</th>
          <th style="width:200px">Okul</th>
          <th style="width:180px">İşlemler</th>
        </tr>
        </thead>
        <tbody>
        <?php if(!$users): ?>
          <tr><td colspan="9" class="text-muted">Kayıt yok.</td></tr>
        <?php else: foreach($users as $u): ?>
          <tr>
            <td>#<?= (int)$u['id'] ?></td>
            <td><?= e($u['display_name']??'—') ?></td>
            <td><?= e($u['email']??'') ?></td>
            <?php if($role==='teacher'): ?>
              <td><?= e($u['brans']??'') ?></td>
              <td><?= e($u['mezun_okul']??'') ?></td>
              <td><?= e($u['kademe']??'') ?></td>
            <?php elseif($role==='parent'): ?>
              <td><?= e($u['isyeri']??'') ?></td>
              <td><?= e($u['meslek']??'') ?></td>
            <?php elseif($role==='student'): ?>
              <td><?= e($u['sinif']??'') ?></td>
            <?php else: ?>
              <td><?= e($u['role']??'') ?></td>
            <?php endif; ?>
            <td><?= e($u['phone']??'') ?></td>
            <td><?= e($u['school']??'') ?></td>
            <td>
              <a class="btn btn-info btn-sm" href="<?= $base ?>?module=users&action=show&id=<?= (int)$u['id'] ?>">Gör</a>
              <a class="btn btn-warning btn-sm" href="<?= $base ?>?module=users&action=edit&id=<?= (int)$u['id'] ?>">Düzenle</a>
              <a class="btn btn-danger btn-sm" href="<?= $base ?>?module=users&action=delete&id=<?= (int)$u['id'] ?>" onclick="return confirm('Silinsin mi?')">Sil</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
