<h2>Bildirim Yönetimi</h2>
<p class="lead">Tüm sisteme veya belirli kullanıcı/rol gruplarına bildirim gönderin.</p>

<!-- Bildirim Mesajları -->
<?php if (isset($_GET['status_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_GET['status_message']) ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error_message'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error_message']) ?>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Yeni Bildirim Oluşturma Formu -->
    <div class="col-lg-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-plus-circle"></i> Yeni Bildirim Oluştur</h3>
            </div>
            <form method="post" action="index.php?module=notifications&action=store">
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Başlık:</label>
                        <input type="text" id="title" name="title" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="message">Mesaj:</label>
                        <textarea id="message" name="message" required rows="3" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="url">URL (İsteğe Bağlı):</label>
                        <input type="text" id="url" name="url" placeholder="https://..." class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="target">Hedef:</label>
                        <select name="target" id="target" required class="form-control">
                            <option value="all">Tüm Kullanıcılar</option>
                            <option value="students">Tüm Öğrenciler</option>
                            <option value="teachers">Tüm Öğretmenler</option>
                            <option value="parents">Tüm Veliler</option>
                            <optgroup label="Tek Kullanıcı">
                                <?php foreach($users as $user): ?>
                                <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['role']) ?>)</option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i> Bildirimi Gönder</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Gönderilmiş Bildirimler Listesi -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-history"></i> Gönderilmiş Bildirimler</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Başlık</th>
                            <th>Hedef</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notifications)): ?>
                            <tr><td colspan="3" class="text-center p-4">Henüz gönderilmiş bildirim yok.</td></tr>
                        <?php else: ?>
                            <?php foreach($notifications as $n): ?>
                            <tr>
                                <td data-label="Başlık"><strong><?= htmlspecialchars($n['title']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($n['message']) ?></small></td>
                                <td data-label="Hedef">
                                    <?php 
                                    if ($n['target_user_id']) echo 'Tek Kullanıcı (ID: ' . htmlspecialchars($n['target_user_id']) . ')';
                                    elseif ($n['target_role']) echo 'Rol: ' . htmlspecialchars($n['target_role']);
                                    else echo 'Tüm Kullanıcılar';
                                    ?>
                                </td>
                                <td data-label="Tarih"><small><?= htmlspecialchars(date('d.m.Y H:i', strtotime($n['created_at']))) ?></small></td>
                                <td class="text-end">
  <a href="index.php?module=notifications&action=show&id=<?= (int)$row['id'] ?>"
     class="btn btn-sm btn-outline-primary me-1">Gör</a>

  <a href="index.php?module=notifications&action=edit&id=<?= (int)$row['id'] ?>"
     class="btn btn-sm btn-warning me-1">Düzenle</a>

  <form action="index.php?module=notifications&action=destroy&id=<?= (int)$row['id'] ?>"
        method="post" class="d-inline">
    <button type="submit" class="btn btn-sm btn-danger"
            onclick="return confirm('Bu dersi silmek istediğinize emin misiniz?');">
      Sil
    </button>
  </form>
</td>

                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
