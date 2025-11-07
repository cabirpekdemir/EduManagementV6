<?php
// /modules/courses/edit_rules.php
// Layout'a özel CSS ve JS göndermek için değişkenleri burada tanımlıyoruz.
$extraHead = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
$extraFoot = '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $(".select2-prerequisites").select2({
            placeholder: "Ön koşul olacak dersleri seçin",
            allowClear: true,
            theme: "default"
        });
    });
</script>';
?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><?= $pageTitle ?></h3>
    </div>
    <form action="/?module=courses&action=updateRules" method="POST">
        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
        <div class="card-body">

            <?php
            if (isset($_SESSION['flash_message'])) {
                $message = $_SESSION['flash_message'];
                echo '<div class="alert alert-' . htmlspecialchars($message['type']) . '">' . htmlspecialchars($message['message']) . '</div>';
                unset($_SESSION['flash_message']);
            }
            ?>

            <h4>Genel Kurallar</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="min_age">Minimum Yaş</label>
                        <input type="number" class="form-control" id="min_age" name="min_age" value="<?= htmlspecialchars($course['min_age'] ?? '') ?>" placeholder="Yoksa boş bırakın">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="max_age">Maksimum Yaş</label>
                        <input type="number" class="form-control" id="max_age" name="max_age" value="<?= htmlspecialchars($course['max_age'] ?? '') ?>" placeholder="Yoksa boş bırakın">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="capacity">Kontenjan</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" value="<?= htmlspecialchars($course['capacity'] ?? '') ?>" placeholder="Sınırsızsa boş bırakın">
                    </div>
                </div>
            </div>
            <hr>
            <h4>Ön Koşul Kuralları</h4>
            <div class="form-group">
                <label>Bu derse kaydolmadan önce tamamlanması gereken dersler:</label>
                <select class="form-control select2-prerequisites" name="prerequisites[]" multiple="multiple" style="width: 100%;">
                    <?php foreach ($allOtherCourses as $otherCourse): ?>
                        <option value="<?= $otherCourse['id'] ?>" <?= in_array($otherCourse['id'], $currentPrerequisites) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($otherCourse['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Kuralları Kaydet</button>
            <a href="?module=courses" class="btn btn-secondary">Ders Listesine Dön</a>
        </div>
    </form>
</div>