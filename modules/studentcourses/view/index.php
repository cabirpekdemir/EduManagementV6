<h2>Öğrenci - Ders Başvuruları</h2>
<p class="lead">Öğrencilerin derslere yaptığı başvuruları onaylayın veya reddedin.</p>

<!-- Bildirim Mesajları -->
<?php if (!empty($status_message)): ?>
    <div class="alert alert-info">
        <?= htmlspecialchars($status_message) ?>
    </div>
<?php endif; ?>
<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Ders Başvuruları</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Öğrenci</th>
                    <th>Ders</th>
                    <th style="width: 15%;">Durum</th>
                    <th style="width: 20%;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entries)): ?>
                    <tr>
                        <td colspan="4" class="text-center p-4">Henüz ders başvuru kaydı bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Öğrenci"><strong><?= htmlspecialchars($entry['student_name'] ?? '') ?></strong></td>
                            <td data-label="Ders"><?= htmlspecialchars($entry['course_name'] ?? '') ?></td>
                            <td data-label="Durum">
                                <?php
                                    $status = $entry['status'] ?? 'bilinmiyor';
                                    $status_class = 'badge-secondary';
                                    if ($status === 'onaylandı') $status_class = 'badge-success';
                                    if ($status === 'bekliyor') $status_class = 'badge-warning';
                                    if ($status === 'reddedildi') $status_class = 'badge-danger';
                                ?>
                                <span class="badge <?= $status_class ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
                            </td>
                            <td data-label="İşlem">
                                <?php if (($entry['status'] ?? '') === 'bekliyor'): ?>
                                    <a href="?module=studentcourses&action=updateStatus&id=<?= htmlspecialchars($entry['id'] ?? '') ?>&status=onayla" class="btn btn-sm btn-success">
                                        <i class="fa fa-check"></i> Onayla
                                    </a>
                                    <a href="?module=studentcourses&action=updateStatus&id=<?= htmlspecialchars($entry['id'] ?? '') ?>&status=reddet" class="btn btn-sm btn-danger ml-1">
                                        <i class="fa fa-times"></i> Reddet
                                    </a>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
