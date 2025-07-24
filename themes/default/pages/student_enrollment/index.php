<h2>Ders Seçimi ve İsteklerim</h2>
<p class="lead">Kayıt olmak istediğiniz dersleri veya ders gruplarını seçerek istek gönderebilir, mevcut isteklerinizin durumunu takip edebilirsiniz.</p>

<!-- Bildirim Mesajları -->
<?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="row">
    <!-- SOL BÖLÜM: Seçilebilecek Dersler ve Gruplar -->
    <div class="col-lg-8">
        <!-- Seçilebilecek Dersler Kartı -->
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title">Seçilebilecek Dersler</h3></div>
            <div class="card-body p-0">
                <?php if (!empty($all_courses)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($all_courses as $course): ?>
                            <li class="list-group-item d-md-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($course['name']) ?></strong>
                                    <p class="mb-0 text-muted"><small><?= htmlspecialchars($course['description'] ?? '') ?></small></p>
                                </div>
                                <div class="mt-2 mt-md-0">
                                    <?php 
                                        $is_requested = in_array($course['id'], array_column(array_filter($my_requests, fn($r) => $r['item_type'] == 'course'), 'item_id'));
                                        $is_enrolled = in_array($course['id'], $enrolled_course_ids);
                                    ?>
                                    <?php if ($is_enrolled): ?>
                                        <span class="badge badge-success"><i class="fa fa-check"></i> Kayıtlı</span>
                                    <?php elseif ($is_requested): ?>
                                        <span class="badge badge-warning">İstek Beklemede</span>
                                    <?php else: ?>
                                        <form method="post" action="index.php?module=student_enrollment&action=make_request" class="d-inline">
                                            <input type="hidden" name="item_id" value="<?= htmlspecialchars($course['id']) ?>">
                                            <input type="hidden" name="item_type" value="course">
                                            <button type="submit" class="btn btn-sm btn-primary">Bu Dersi İste</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="p-3">Şu anda seçilebilecek bireysel ders bulunmamaktadır.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seçilebilecek Ders Grupları Kartı -->
        <div class="card card-info card-outline mt-4">
            <div class="card-header"><h3 class="card-title">Seçilebilecek Ders Grupları</h3></div>
             <div class="card-body p-0">
                <?php if (!empty($all_groups)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($all_groups as $group): ?>
                             <li class="list-group-item">
                                <div class="d-md-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($group['name']) ?> (Grup)</strong>
                                        <p class="mb-1 text-muted"><small><?= htmlspecialchars($group['description'] ?? '') ?></small></p>
                                    </div>
                                    <div class="mt-2 mt-md-0">
                                        <?php 
                                            $is_group_requested = in_array($group['id'], array_column(array_filter($my_requests, fn($r) => $r['item_type'] == 'group'), 'item_id'));
                                            $is_group_enrolled = $group['is_student_enrolled_in_group']; 
                                        ?>
                                        <?php if ($is_group_enrolled): ?>
                                            <span class="badge badge-success"><i class="fa fa-check"></i> Bu Gruba Kayıtlı</span>
                                        <?php elseif ($is_group_requested): ?>
                                            <span class="badge badge-warning">Grup İsteği Beklemede</span>
                                        <?php else: ?>
                                            <form method="post" action="index.php?module=student_enrollment&action=make_request" class="d-inline">
                                                <input type="hidden" name="item_id" value="<?= htmlspecialchars($group['id']) ?>">
                                                <input type="hidden" name="item_type" value="group">
                                                <button type="submit" class="btn btn-sm btn-info">Bu Grubu İste</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($group['courses'])): ?>
                                    <ul class="list-unstyled mt-2 pl-3" style="font-size:0.9em;">
                                        <?php foreach($group['courses'] as $gc): ?>
                                            <li><i class="fa fa-book text-muted mr-2"></i><?= htmlspecialchars($gc['name']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="p-3">Şu anda seçilebilecek ders grubu bulunmamaktadır.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SAĞ BÖLÜM: Mevcut İsteklerim -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-history"></i> Mevcut İsteklerim</h3>
            </div>
             <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>İstek</th>
                            <th>Durum</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($my_requests)): ?>
                            <?php foreach ($my_requests as $request): ?>
                                <tr>
                                    <td data-label="İstek">
                                        <strong><?= htmlspecialchars($request['item_name']) ?></strong><br>
                                        <small><?= htmlspecialchars($request['item_type'] === 'course' ? 'Ders' : 'Grup') ?></small>
                                    </td>
                                    <td data-label="Durum">
                                        <?php
                                            $status = $request['status'] ?? 'bilinmiyor';
                                            $status_class = 'badge-secondary';
                                            if ($status === 'pending') $status_class = 'badge-warning';
                                            if ($status === 'admin_approved' || $status === 'teacher_approved') $status_class = 'badge-success';
                                            if ($status === 'rejected') $status_class = 'badge-danger';
                                        ?>
                                        <span class="badge <?= $status_class ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?></span>
                                    </td>
                                    <td data-label="İşlem">
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <a href="index.php?module=student_enrollment&action=cancel_request&request_id=<?= htmlspecialchars($request['id']) ?>" class="btn btn-xs btn-danger" onclick="return confirm('Bu isteği iptal etmek istediğinize emin misiniz?')">İptal Et</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                </table>
                 <?php else: ?>
                    <p class="p-3 text-center">Henüz bir ders veya grup isteğiniz bulunmamaktadır.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
