<h2>Değerlendirme Yoklama Girişi</h2>
<p>
    <strong>Değerlendirme:</strong> <?= e($exam['name']) ?><br>
    <strong>Tarih:</strong> <?= e(!empty($exam['exam_date']) ? date('d.m.Y', strtotime($exam['exam_date'])) : 'N/A') ?>
</p>
<a href="index.php?module=exam_attendance&action=index" class="btn" style="margin-bottom:15px;">&laquo; Değerlendirme Seçim Ekranına Dön</a>
<?php if (isset($_GET['status']) && $_GET['status'] === 'saved'): ?>
    <p style="color: green; border:1px solid green; padding:10px;">Yoklama başarıyla kaydedildi.</p>
<?php endif; ?>

<form method="POST" action="index.php?module=exam_attendance&action=save">
    <input type="hidden" name="exam_id" value="<?= e($exam['id']) ?>">
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%">
        <thead><tr><th style="width:50%">Öğrenci Adı</th><th style="width:35%">Durum</th><th style="width:15%">Notlar</th></tr></thead>
        <tbody>
            <?php if(!empty($students)): foreach($students as $student): ?>
                <input type="hidden" name="students[<?= e($student['id']) ?>]" value="1">
                <tr>
                    <td><?= e($student['name']) ?> (Sınıfı: <?= e($student['class_name'] ?? 'N/A') ?>)</td>
                    <td>
                        <div class="attendance-status-buttons">
                            <?php 
                            $current_status = $attendance_map[$student['id']]['status'] ?? 'Geldi'; 
                            foreach($statuses as $status_val): 
                                $is_checked = ($current_status === $status_val);
                            ?>
                            <label class="status-button <?= $is_checked ? 'active' : '' ?>" data-status="<?= e($status_val) ?>">
                                <input type="radio" name="status[<?= e($student['id']) ?>]" value="<?= e($status_val) ?>" <?= $is_checked ? 'checked' : '' ?> style="display:none;">
                                <?= e($status_val) ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td><input type="text" name="notes[<?= e($student['id']) ?>]" value="<?= e($attendance_map[$student['id']]['notes'] ?? '') ?>" style="width:95%;"></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="3">Bu değerlendirmeye ait öğrenci bulunamadı.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if(!empty($students)): ?>
    <div style="margin-top:20px;">
        <button type="button" class="btn" id="markAllPresentBtn">Tümünü Geldi İşaretle</button>
        <button type="submit" class="btn" style="background-color: #5cb85c; margin-left:10px;">Yoklamayı Kaydet</button>
    </div>
    <?php endif; ?>
</form>
<style>
    .attendance-status-buttons label.status-button { display: inline-block; padding: 6px 10px; margin: 2px; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; background-color: #f8f8f8; transition: all 0.2s; font-size:0.9em;}
    .attendance-status-buttons label.status-button:hover { background-color: #e9e9e9; }
    .attendance-status-buttons label.status-button.active { color: white; border-color: #2e6da4; }
    .attendance-status-buttons label.status-button[data-status="Geldi"].active { background-color: #5cb85c; border-color: #4cae4c;}
    .attendance-status-buttons label.status-button[data-status="Gelmedi"].active { background-color: #d9534f; border-color: #d43f3a;}
    .attendance-status-buttons label.status-button[data-status="Geç Geldi"].active { background-color: #f0ad4e; border-color: #eea236;}
    .attendance-status-buttons label.status-button[data-status="İzinli"].active { background-color: #5bc0de; border-color: #46b8da;}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.attendance-status-buttons').forEach(container => {
        const buttons = container.querySelectorAll('.status-button');
        buttons.forEach(label => {
            label.addEventListener('click', function() {
                container.querySelectorAll('.status-button').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
    });
    const markAllPresentButton = document.getElementById('markAllPresentBtn');
    if(markAllPresentButton){
        markAllPresentButton.addEventListener('click', function(){
            document.querySelectorAll('.attendance-status-buttons').forEach(container => {
                container.querySelectorAll('.status-button').forEach(btnLabel => {
                    btnLabel.classList.remove('active');
                    const radio = btnLabel.querySelector('input[type="radio"]');
                    if(radio) radio.checked = false;
                    if(btnLabel.dataset.status === 'Geldi'){ 
                        btnLabel.classList.add('active');
                        if(radio) radio.checked = true;
                    }
                });
            });
        });
    }
});
</script>