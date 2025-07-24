<h2>Aktivite Logları (Arşiv)</h2>
<p class="lead">Sistemde gerçekleşen tüm kullanıcı eylemlerinin kaydı.</p>

<!-- Filtreleme Formu -->
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Kayıtları Filtrele</h3>
    </div>
    <form method="get" action="index.php">
        <div class="card-body">
            <input type="hidden" name="module" value="activity_log">
            <input type="hidden" name="action" value="index">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kullanıcı</label>
                        <select name="user_id" class="form-control">
                            <option value="">Tüm Kullanıcılar</option>
                            <?php foreach($users as $user): ?>
                                <option value="<?= htmlspecialchars($user['id']) ?>" <?= (($filters['user_id'] ?? '') == $user['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                     <div class="form-group">
                        <label>Modül Adı</label>
                        <input type="text" name="module_filter" class="form-control" placeholder="Örn: users, courses" value="<?= htmlspecialchars($filters['module'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tarih (Bu tarihten sonra)</label>
                        <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($filters['date_start'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info"><i class="fa fa-filter"></i> Filtrele</button>
            <a href="index.php?module=activity_log" class="btn btn-secondary">Filtreyi Temizle</a>
        </div>
    </form>
</div>

<!-- Log Kayıtları Tablosu -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Log Kayıtları</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th>Kullanıcı</th>
                    <th>Eylem</th>
                    <th>Modül</th>
                    <th>Açıklama</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td data-label="Tarih"><small><?= htmlspecialchars(date('d.m.Y H:i:s', strtotime($log['created_at']))) ?></small></td>
                        <td data-label="Kullanıcı"><?= htmlspecialchars($log['user_name']) ?> <small>(ID: <?= htmlspecialchars($log['user_id']) ?>)</small></td>
                        <td data-label="Eylem"><span class="badge badge-info"><?= htmlspecialchars($log['action']) ?></span></td>
                        <td data-label="Modül"><?= htmlspecialchars($log['module']) ?></td>
                        <td data-label="Açıklama"><?= htmlspecialchars($log['description']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center p-4">Filtreye uygun kayıt bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Arşiv Yönetimi -->
<h3>Arşiv Yönetimi</h3>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fa fa-download"></i> Arşivi İndir</h3></div>
            <div class="card-body">
                <p>Tüm log kayıtlarını CSV formatında yedekleyin.</p>
                <form method="post" action="index.php?module=activity_log&action=export">
                    <button type="submit" class="btn btn-primary">Arşivi İndir (.csv)</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-danger card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fa fa-exclamation-triangle"></i> Kayıtları Kalıcı Sil</h3></div>
            <form method="post" action="index.php?module=activity_log&action=purge" onsubmit="return confirm('DİKKAT! Belirtilen tarihten önceki TÜM log kayıtları kalıcı olarak silinecektir. Bu işlem geri alınamaz. Emin misiniz?')">
                <div class="card-body">
                    <p>Belirli bir tarihten önceki tüm log kayıtlarını kalıcı olarak silerek veritabanını temizleyin.</p>
                    <div class="form-group">
                        <label>Bu tarihten öncekileri sil:</label>
                        <input type="date" name="purge_date" required class="form-control">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger">Kayıtları Kalıcı Olarak Sil</button>
                </div>
            </form>
        </div>
    </div>
</div>
