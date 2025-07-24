<h2>
    <?php if ($isEdit): ?>
        Rehberlik Seansını Düzenle
    <?php elseif ($isMultiple): ?>
        Birden Çok Rehberlik Seansı Ekle
    <?php else: ?>
        Yeni Rehberlik Seansı Ekle
    <?php endif; ?>
</h2>

<?php if (isset($_GET['error_message'])): ?>
    <p style="color: red; border:1px solid red; padding:10px; margin-bottom:15px;">
        <?= htmlspecialchars($_GET['error_message']) ?>
    </p>
<?php endif; ?>

<?php if (!$isMultiple): // Tekli form (Düzenleme için) ?>
    <form method="post" action="<?= htmlspecialchars($formAction) ?>">
        <label for="student_id">Öğrenci:</label><br>
        <select name="student_id" id="student_id" required>
            <option value="">Seçiniz</option>
            <?php 
            $selected_student_id = $session['student_id'] ?? ($_POST['student_id'] ?? ''); 
            foreach ($students as $s): ?>
                <option value="<?= e($s['id']) ?>" <?= ((string)$selected_student_id === (string)$s['id']) ? "selected" : "" ?>>
                    <?= e($s['name']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="session_date">Görüşme Tarihi:</label><br>
        <input type="date" name="session_date" id="session_date" value="<?= e($session['session_date'] ?? ($_POST['session_date'] ?? date('Y-m-d'))) ?>" required><br><br>

        <label for="title">Konu:</label><br>
        <input type="text" name="title" id="title" value="<?= e($session['title'] ?? ($_POST['title'] ?? '')) ?>" required><br><br>
        
        <label for="notes">Görüşme Notları:</label><br>
        <textarea name="notes" id="notes" rows="8" cols="50" required><?= e($session['notes'] ?? ($_POST['notes'] ?? '')) ?></textarea><br><br>

        <button type="submit"><?= $isEdit ? "Güncelle" : "Oluştur" ?></button>
        <a href="index.php?module=guidance&action=index" class="btn">Vazgeç</a>
    </form>
<?php else: // Çoklu ekleme formu (yalnızca 'create' aksiyonunda isMultiple true olduğunda) ?>
    <form method="post" action="<?= htmlspecialchars($formAction) ?>" id="multipleSessionForm">
        <div id="sessionEntries">
            </div>

        <button type="button" id="addSessionBtn" class="btn" style="margin-top:15px;">+ Yeni Seans Alanı Ekle</button>
        <button type="submit" class="btn" style="background-color: #5cb85c; margin-left:10px;">Tüm Seansları Kaydet</button>
        <a href="index.php?module=guidance&action=index" class="btn">Vazgeç</a>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sessionEntriesDiv = document.getElementById('sessionEntries');
        const addSessionBtn = document.getElementById('addSessionBtn');
        const studentsData = <?= json_encode($students) ?>; 

        let sessionCount = 0;

        function addSessionEntry(initialData = {}) {
            const index = sessionCount++;
            const entryDiv = document.createElement('div');
            entryDiv.classList.add('session-entry');
            entryDiv.style.border = '1px solid #ccc';
            entryDiv.style.padding = '15px';
            entryDiv.style.marginBottom = '20px';
            entryDiv.style.borderRadius = '5px';
            entryDiv.style.backgroundColor = '#f9f9f9';

            let studentOptions = '<option value="">Seçiniz</option>';
            studentsData.forEach(student => {
                const selected = (initialData.student_id == student.id) ? 'selected' : '';
                studentOptions += `<option value="${student.id}" ${selected}>${escapeHtml(student.name)}</option>`;
            });

            entryDiv.innerHTML = `
                <h4 style="margin-top:0;">Seans #${index + 1}</h4>
                <label for="sessions[${index}][student_id]">Öğrenci:</label><br>
                <select name="sessions[${index}][student_id]" id="sessions[${index}][student_id]" required style="width:100%;">
                    ${studentOptions}
                </select><br><br>

                <label for="sessions[${index}][session_date]">Görüşme Tarihi:</label><br>
                <input type="date" name="sessions[${index}][session_date]" id="sessions[${index}][session_date]" value="${escapeHtml(initialData.session_date || '<?= date('Y-m-d') ?>')}" required><br><br>

                <label for="sessions[${index}][title]">Konu:</label><br>
                <input type="text" name="sessions[${index}][title]" id="sessions[${index}][title]" value="${escapeHtml(initialData.title || '')}" required style="width:100%;"><br><br>
                
                <label for="sessions[${index}][notes]">Görüşme Notları:</label><br>
                <textarea name="sessions[${index}][notes]" id="sessions[${index}][notes]" rows="6" cols="50" required style="width:100%;">${escapeHtml(initialData.notes || '')}</textarea><br><br>
                
                <button type="button" class="btn btn-danger remove-session-btn" style="background-color:#dc3545; color:white; border:none;">Bu Seansı Kaldır</button>
            `;
            sessionEntriesDiv.appendChild(entryDiv);

            entryDiv.querySelector('.remove-session-btn').addEventListener('click', function() {
                entryDiv.remove();
            });
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        addSessionBtn.addEventListener('click', function() {
            addSessionEntry();
        });

        // Sayfa yüklendiğinde en az bir seans alanı ekle (create aksiyonunda)
        <?php if ($isMultiple): ?>
            // Eğer URL'de bir student_id varsa, ilk seansı o öğrenci için önceden doldur
            const urlParams = new URLSearchParams(window.location.search);
            const prefillStudentId = urlParams.get('student_id');
            if (prefillStudentId) {
                addSessionEntry({ student_id: prefillStudentId });
            } else {
                addSessionEntry();
            }
        <?php endif; ?>
    });
    </script>
<?php endif; ?>