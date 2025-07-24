<h2>Etkinlik Yoklama Raporları</h2>
<a href="index.php?module=activity_attendance&action=index" class="btn" style="margin-bottom:15px;">&laquo; Etkinlik Yoklama Ana Sayfasına Dön</a>

<form method="GET" action="index.php">
    <input type="hidden" name="module" value="activity_attendance">
    <input type="hidden" name="action" value="report">
    <fieldset style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; padding:15px; border:1px solid #eee; border-radius:5px;">
        <legend>Filtreler</legend>
        
        <div style="flex:1; min-width:150px;">
            <label for="filter_activity_id">Etkinlik:</label><br>
            <select name="filter_activity_id" id="filter_activity_id" style="width:100%;">
                <option value="">Tümü</option>
                <?php if(!empty($all_activities)): foreach($all_activities as $activity): ?>
                <option value="<?= e($activity['id']) ?>" <?= (($filters['filter_activity_id'] ?? '') == $activity['id']) ? 'selected' : '' ?>><?= e($activity['name']) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>
        
        <div style="flex:1; min-width:150px;">
            <label for="filter_class_id">Sınıf/Şube:</label><br>
            <select name="filter_class_id" id="filter_class_id" style="width:100%;">
                <option value="">Tümü</option>
                 <?php if(!empty($all_classes)): foreach($all_classes as $cl): ?>
                <option value="<?= e($cl['id']) ?>" <?= (($filters['filter_class_id'] ?? '') == $cl['id']) ? 'selected' : '' ?>><?= e($cl['name']) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div style="flex:1; min-width:150px;">
            <label for="filter_student_id">Öğrenci:</label><br>
            <select name="filter_student_id" id="filter_student_id" style="width:100%;">
                <option value="">Tümü</option>
                 <?php if(!empty($all_students)): foreach($all_students as $s): ?>
                <option value="<?= e($s['id']) ?>" <?= (($filters['filter_student_id'] ?? '') == $s['id']) ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>
        <div style="flex:1; min-width:150px;">
            <label for="filter_status">Durum:</label><br>
            <select name="filter_status" id="filter_status" style="width:100%;">
                <option value="">Tümü</option>
                <?php if(!empty($attendance_statuses_for_filter)): foreach($attendance_statuses_for_filter as $status): ?>
                <option value="<?= e($status) ?>" <?= (($filters['filter_status'] ?? '') == $status) ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div style="flex:1; min-width:150px;">
            <label for="filter_date_start">Başlangıç Tarihi:</label><br>
            <input type="date" name="filter_date_start" id="filter_date_start" value="<?= e($filters['filter_date_start'] ?? '') ?>" style="width:98%;">
        </div>
        <div style="flex:1; min-width:150px;">
            <label for="filter_date_end">Bitiş Tarihi:</label><br>
            <input type="date" name="filter_date_end" id="filter_date_end" value="<?= e($filters['filter_date_end'] ?? '') ?>" style="width:98%;">
        </div>
        <div style="align-self: flex-end;">
            <button type="submit" class="btn">Raporu Getir</button>
        </div>
    </fieldset>
</form>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
    <thead>
        <tr>
            <th>Tarih</th>
            <th>Etkinlik Adı</th>
            <th>Öğrenci</th>
            <th>Sınıfı</th>
            <th>Durum</th>
            <th>Notlar</th>
            <th>Yoklamayı Giren</th>
        </tr>
    </thead>
    <tbody>
    <?php if(!empty($attendance_records)): ?>
        <?php foreach($attendance_records as $record): ?>
        <tr>
            <td><?= e(date('d.m.Y', strtotime($record['attendance_date']))) ?></td>
            <td><?= e($record['activity_name']) ?></td>
            <td><?= e($record['student_name']) ?></td>
            <td><?= e($record['class_name'] ?? 'N/A') ?></td>
            <td style="font-weight:bold; color: <?= $record['status']==='Geldi' ? 'green' : ($record['status']==='Gelmedi' ? 'red' : ($record['status']==='Geç Geldi' ? 'orange' : 'blue')) ?>">
                <?= e($record['status']) ?>
            </td>
            <td><?= e($record['notes'] ?? '') ?></td>
            <td><?= e($record['entry_teacher_name']) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7">Belirtilen kriterlere uygun etkinlik yoklama kaydı bulunamadı.</td></tr>
    <?php endif; ?>
    </tbody>
</table>