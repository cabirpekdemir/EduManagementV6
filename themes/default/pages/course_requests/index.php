<h2>Ders ve Grup İstekleri Yönetimi</h2>
<p class="lead">Öğrencilerin ders veya grup taleplerini yönetin.</p>

<?php if (($userRole ?? 'guest') === 'admin'): ?>
    <a href="index.php?module=course_requests&action=assign_item_form" class="btn btn-primary mb-3">
        <i class="fa fa-plus"></i> Öğrenciye Doğrudan Ders/Grup Ata
    </a>
<?php endif; ?>

<!-- Bildirim Mesajları -->
<?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm İstekler</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Öğrenci</th>
                    <th>İstenen (Ders/Grup)</th>
                    <th>Tür</th>
                    <th>İstek Tarihi</th>
                    <th>Durum</th>
                    <th style="width: 25%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="6" class="text-center p-4">İşlem bekleyen veya yönetilecek istek bulunmamaktadır.</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Öğrenci">
                                <strong><?= htmlspecialchars($request['student_name']) ?></strong><br>
                                <small class="text-muted">ID: <?= htmlspecialchars($request['student_id']) ?></small>
                            </td>
                            <td data-label="İstenen"><?= htmlspecialchars($request['item_name']) ?></td>
                            <td data-label="Tür"><?= htmlspecialchars($request['item_type'] === 'course' ? 'Ders' : 'Grup') ?></td>
                            <td data-label="İstek Tarihi"><small><?= htmlspecialchars(date('d.m.Y H:i', strtotime($request['request_date']))) ?></small></td>
                            <td data-label="Durum">
                                <?php
                                    $status = $request['request_status'] ?? 'bilinmiyor';
                                    $status_map = [
                                        'pending' => ['class' => 'badge-warning', 'text' => 'Bekliyor'],
                                        'teacher_approved' => ['class' => 'badge-info', 'text' => 'Öğretmen Onayladı'],
                                        'admin_approved' => ['class' => 'badge-success', 'text' => 'Onaylandı'],
                                        'rejected' => ['class' => 'badge-danger', 'text' => 'Reddedildi']
                                    ];
                                    $s = $status_map[$status] ?? ['class' => 'badge-secondary', 'text' => ucfirst($status)];
                                ?>
                                <span class="badge <?= $s['class'] ?>"><?= $s['text'] ?></span>
                            </td>
                            <td data-label="İşlemler">
                                <?php 
                                $can_teacher_process = ($userRole === 'teacher' && $status === 'pending' && $request['item_type'] === 'course' && $request['course_teacher_id'] == $currentUserId);
                                $can_admin_process = ($userRole === 'admin' && ($status === 'pending' || $status === 'teacher_approved'));
                                ?>

                                <?php if ($can_admin_process || $can_teacher_process): ?>
                                    <form method="post" action="index.php?module=course_requests&action=process_request">
                                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['request_id']) ?>">
                                        
                                        <?php if ($can_admin_process): ?>
                                            <button type="submit" name="new_status" value="admin_approved" class="btn btn-sm btn-success mb-2">Yönetici Onayla</button>
                                        <?php elseif ($can_teacher_process): ?>
                                            <button type="submit" name="new_status" value="teacher_approved" class="btn btn-sm btn-info mb-2">Öğretmen Onayla</button>
                                        <?php endif; ?>
                                        
                                        <div class="input-group">
                                            <textarea name="notes" rows="2" placeholder="Reddetme notu..." class="form-control form-control-sm"></textarea>
                                            <div class="input-group-append">
                                                <button type="submit" name="new_status" value="rejected" class="btn btn-sm btn-danger">Reddet</button>
                                            </div>
                                        </div>
                                    </form>
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
