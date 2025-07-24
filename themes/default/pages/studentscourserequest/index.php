<h2>Talep Edilen Dersler</h2>
<p class="lead">Öğrenciler tarafından talep edilen derslerin ve onay durumlarının listesi.</p>

<!-- Başarı veya Hata Mesajları için Alan -->
<?php if (!empty($_GET['status_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_GET['status_message']) ?>
    </div>
<?php endif; ?>
<?php if (!empty($_GET['error_message'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error_message']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Ders Talep Listesi</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Ders Adı</th>
                    <th>Talep Edilen Öğretmen</th>
                    <th style="width: 15%;">Durum</th>
                    <!-- Adminler için işlem sütunu eklenebilir -->
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="3" class="text-center p-4">Henüz yapılmış bir ders talebi bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Ders Adı"><strong><?= htmlspecialchars($r['course_name']) ?></strong></td>
                            <td data-label="Öğretmen"><?= htmlspecialchars($r['teacher_name']) ?></td>
                            <td data-label="Durum">
                                <?php
                                    $status_class = 'badge-secondary'; // default
                                    if (($r['status'] ?? '') === 'approved') $status_class = 'badge-success';
                                    if (($r['status'] ?? '') === 'pending') $status_class = 'badge-warning';
                                    if (($r['status'] ?? '') === 'rejected') $status_class = 'badge-danger';
                                ?>
                                <span class="badge <?= $status_class ?>">
                                    <?= htmlspecialchars(ucfirst($r['status'] ?? 'Bilinmiyor')) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
