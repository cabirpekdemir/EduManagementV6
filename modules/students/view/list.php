<?php
// modules/students/view/list.php
$students = $students ?? [];
$search = $search ?? '';
$sort = $sort ?? 'name';
$order = $order ?? 'asc';

// Telefon Formatlama Fonksiyonu
if (!function_exists('formatPhone')) {
    function formatPhone($phone) {
        if (empty($phone)) return '—';
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) < 10) return $phone;
        return '(' . substr($phone, 0, 3) . ') ' . 
               substr($phone, 3, 3) . ' ' . 
               substr($phone, 6, 2) . ' ' . 
               substr($phone, 8, 2);
    }
}

// Sıralama URL helper fonksiyonu
if (!function_exists('sortUrl')) {
    function sortUrl($column, $currentSort, $currentOrder, $search) {
        $newOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
        $url = "index.php?module=students&action=list&sort={$column}&order={$newOrder}";
        if ($search) {
            $url .= "&search=" . urlencode($search);
        }
        return $url;
    }
}

// Sıralama ikon helper
if (!function_exists('sortIcon')) {
    function sortIcon($column, $currentSort, $currentOrder) {
        if ($currentSort !== $column) {
            return '<i class="fa fa-sort text-muted"></i>';
        }
        return $currentOrder === 'asc' ? '<i class="fa fa-sort-up text-primary"></i>' : '<i class="fa fa-sort-down text-primary"></i>';
    }
}
?>

<style>
.bulk-actions-bar {
    position: sticky;
    top: 60px;
    z-index: 999;
    background: #fff;
    padding: 15px;
    border-bottom: 2px solid #e3e6f0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: none;
}

.bulk-actions-bar.active {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.selected-count {
    font-weight: bold;
    color: #4e73df;
    font-size: 1.1rem;
}

.bulk-action-buttons {
    display: flex;
    gap: 10px;
}

.student-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

#selectAll {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

tr.selected {
    background-color: #e3f2fd !important;
}
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Öğrenci Listesi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php?module=dashboard">Ana Sayfa</a></li>
                    <li class="breadcrumb-item active">Öğrenciler</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        
        <!-- Flash Mesajlar -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-4">
                        <h5 class="mb-0">
                            <i class="fa fa-users"></i> Öğrenci Listesi
                            <span class="badge badge-primary"><?= count($students) ?></span>
                        </h5>
                    </div>
                    <div class="col-md-4">
                        <form method="GET" action="index.php">
                            <input type="hidden" name="module" value="students">
                            <input type="hidden" name="action" value="list">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       value="<?= htmlspecialchars($search) ?>"
                                       placeholder="İsim, TC No veya Telefon ile ara...">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search"></i> Ara
                                </button>
                                <?php if (!empty($search)): ?>
                                    <a href="index.php?module=students&action=list" class="btn btn-secondary">
                                        <i class="fa fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-success me-2" onclick="exportToExcel('all')">
                            <i class="fa fa-file-excel"></i> Excel
                        </button>
                        <a href="index.php?module=students&action=create" class="btn btn-success">
                            <i class="fa fa-plus"></i> Yeni Öğrenci
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- TOPLU İŞLEM BARI -->
            <div class="bulk-actions-bar" id="bulkActionsBar">
                <div>
                    <span class="selected-count">
                        <i class="fas fa-check-square"></i> 
                        <span id="selectedCount">0</span> öğrenci seçildi
                    </span>
                </div>
                <div class="bulk-action-buttons">
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('activate')">
                        <i class="fas fa-check-circle"></i> Aktif Yap
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="bulkAction('deactivate')">
                        <i class="fas fa-ban"></i> Pasif Yap
                    </button>
                    <button type="button" class="btn btn-info btn-sm" onclick="showBulkStatusModal()">
                        <i class="fas fa-exchange-alt"></i> Durum Değiştir
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="exportToExcel('selected')">
                        <i class="fas fa-file-excel"></i> Seçilenleri Excel'e
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                        <i class="fas fa-archive"></i> Arşive Taşı
                    </button>
                    <button type="button" class="btn btn-light btn-sm" onclick="clearSelection()">
                        <i class="fas fa-times"></i> İptal
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                <?php if (empty($students)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fa fa-users fa-3x mb-3"></i>
                        <p>
                            <?php if ($search): ?>
                                "<?= htmlspecialchars($search) ?>" araması için öğrenci bulunamadı.
                            <?php else: ?>
                                Henüz öğrenci eklenmemiş.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px">
                                        <input type="checkbox" id="selectAll" title="Tümünü Seç">
                                    </th>
                                    <th style="width:40px">#</th>
                                    <th style="width:50px">Fotoğraf</th>
                                    <th style="width:180px">
                                        <a href="<?= sortUrl('full_name', $sort, $order, $search) ?>" class="text-decoration-none text-dark">
                                            Ad Soyad <?= sortIcon('full_name', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <th style="width:120px">
                                        <a href="<?= sortUrl('tc_kimlik', $sort, $order, $search) ?>" class="text-decoration-none text-dark">
                                            TC Kimlik <?= sortIcon('tc_kimlik', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <th style="width:150px">
                                        <a href="<?= sortUrl('okul', $sort, $order, $search) ?>" class="text-decoration-none text-dark">
                                            Okul <?= sortIcon('okul', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <th style="width:110px">
                                        <a href="<?= sortUrl('phone', $sort, $order, $search) ?>" class="text-decoration-none text-dark">
                                            Telefon <?= sortIcon('phone', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <th style="width:100px">
                                        <a href="<?= sortUrl('birth_date', $sort, $order, $search) ?>" class="text-decoration-none text-dark">
                                            Doğum Tarihi <?= sortIcon('birth_date', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <th style="width:100px">
                                        <a href="<?= sortUrl('sinif', $sort, $order, $search) ?>" class="text-decoration-none text-dark">
                                            Sınıf <?= sortIcon('sinif', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <th style="width:120px" class="text-center">İlerleme</th>
                                    <th style="width:120px" class="text-center">Durum</th>
                                    <th class="text-end" style="width:180px">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <tr data-student-id="<?= $student['id'] ?>">
                                        <td>
                                            <input type="checkbox" class="student-checkbox" 
                                                   value="<?= $student['id'] ?>"
                                                   data-student-name="<?= htmlspecialchars($student['full_name'] ?? $student['name'] ?? '') ?>">
                                        </td>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <img src="<?= htmlspecialchars($student['profile_photo'] ?? 'assets/img/default-avatar.png') ?>" 
                                                 class="rounded-circle" 
                                                 style="width:40px;height:40px;object-fit:cover;" 
                                                 alt="<?= htmlspecialchars($student['full_name'] ?? $student['name'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($student['full_name'] ?? $student['name'] ?? '') ?></strong>
                                            <?php if (!empty($student['student_number'])): ?>
                                                <br><small class="text-muted">No: <?= htmlspecialchars($student['student_number']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($student['tc_kimlik'] ?? '—') ?></small>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($student['okul'] ?? '—') ?></small>
                                        </td>
                                        <td>
                                            <i class="fa fa-phone text-muted"></i>
                                            <small><?= formatPhone($student['phone'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($student['birth_date'])): ?>
                                                <small><?= date('d.m.Y', strtotime($student['birth_date'])) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">—</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($student['sinif'])): ?>
                                                <span class="badge badge-info">
                                                    <?= htmlspecialchars($student['sinif']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            $courseCount = (int)($student['course_count'] ?? 0);
                                            $totalCourses = 10;
                                            $percentage = min(100, ($courseCount / $totalCourses) * 100);
                                            ?>
                                            <div class="progress" style="height:20px;min-width:80px;">
                                                <div class="progress-bar <?= $percentage >= 100 ? 'bg-success' : 'bg-primary' ?>" 
                                                     role="progressbar" 
                                                     style="width: <?= $percentage ?>%">
                                                    <small><?= $courseCount ?>/<?= $totalCourses ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $statusBadges = [
                                                'on_kayit' => '<span class="badge badge-warning">📝 Ön Kayıt</span>',
                                                'aktif' => '<span class="badge badge-success">✅ Aktif</span>',
                                                'ders_secimi_yapan' => '<span class="badge badge-info">📚 Ders Seçimi</span>',
                                                'mezun' => '<span class="badge badge-secondary">🎓 Mezun</span>'
                                            ];
                                            echo $statusBadges[$student['enrollment_status'] ?? 'on_kayit'] ?? '<span class="badge badge-light">—</span>';
                                            ?>
                                            <br>
                                            <?php if (!empty($student['is_active'])): ?>
                                                <small class="text-success">● Aktif</small>
                                            <?php else: ?>
                                                <small class="text-muted">○ Pasif</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a href="index.php?module=students&action=view&id=<?= $student['id'] ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Görüntüle">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="index.php?module=students&action=edit&id=<?= $student['id'] ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="Düzenle">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="index.php?module=students&action=delete&id=<?= $student['id'] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Bu öğrenciyi arşive taşımak istediğinizden emin misiniz?')" 
                                                   title="Arşive Taşı">
                                                    <i class="fa fa-archive"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($students)): ?>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Toplam <strong><?= count($students) ?></strong> öğrenci
                        <?php if ($search): ?>
                            (<?= count($students) ?> sonuç bulundu)
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="index.php?module=students&action=create" class="btn btn-success btn-sm">
                            <i class="fa fa-plus"></i> Yeni Ekle
                        </a>
                        <a href="index.php?module=students&action=import" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-upload"></i> İçe Aktar
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="modal fade" id="bulkStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Toplu Durum Değiştir
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    <strong><span id="bulkStatusCount">0</span> öğrenci</strong> için yeni durum seçin:
                </p>
                <div class="form-group">
                    <label>Yeni Durum:</label>
                    <select id="bulkStatusSelect" class="form-control">
                        <option value="on_kayit">📝 Ön Kayıt</option>
                        <option value="sinav_secim">📋 Sınav Seçim</option>
                        <option value="sinav_secimi_yapti">✅ Sınav Seçimi Yaptı</option>
                        <option value="ders_secimi_yapan">📚 Ders Seçimi Yapan</option>
                        <option value="sinav_sonuc_girisi">📊 Sınav Sonuç Girişi</option>
                        <option value="sinavi_kazanamayan">❌ Sınavı Kazanamayan</option>
                        <option value="aktif">✅ Aktif Öğrenci</option>
                        <option value="kayit_dondurma">⏸️ Kayıt Dondurma</option>
                        <option value="kayit_silinen">🗑️ Kayıt Silinen</option>
                        <option value="mezun">🎓 Mezun</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-info" onclick="applyBulkStatus()">
                    <i class="fas fa-check"></i> Uygula
                </button>
            </div>
        </div>
    </div>
</div>

<form id="bulkActionForm" method="POST" action="index.php?module=students&action=bulk_action" style="display: none;">
    <input type="hidden" name="action_type" id="bulkActionType">
    <input type="hidden" name="student_ids" id="bulkStudentIds">
    <input type="hidden" name="new_status" id="bulkNewStatus">
</form>

<script>
let selectedStudents = new Set();

$(document).ready(function() {
    updateBulkActionsBar();
});

$('#selectAll').change(function() {
    const isChecked = $(this).prop('checked');
    $('.student-checkbox').prop('checked', isChecked);
    
    if (isChecked) {
        $('.student-checkbox').each(function() {
            selectedStudents.add($(this).val());
            $(this).closest('tr').addClass('selected');
        });
    } else {
        selectedStudents.clear();
        $('tr.selected').removeClass('selected');
    }
    
    updateBulkActionsBar();
});

$('.student-checkbox').change(function() {
    const studentId = $(this).val();
    const isChecked = $(this).prop('checked');
    
    if (isChecked) {
        selectedStudents.add(studentId);
        $(this).closest('tr').addClass('selected');
    } else {
        selectedStudents.delete(studentId);
        $(this).closest('tr').removeClass('selected');
        $('#selectAll').prop('checked', false);
    }
    
    updateBulkActionsBar();
});

function updateBulkActionsBar() {
    const count = selectedStudents.size;
    $('#selectedCount').text(count);
    
    if (count > 0) {
        $('#bulkActionsBar').addClass('active');
    } else {
        $('#bulkActionsBar').removeClass('active');
    }
}

function clearSelection() {
    selectedStudents.clear();
    $('.student-checkbox').prop('checked', false);
    $('#selectAll').prop('checked', false);
    $('tr.selected').removeClass('selected');
    updateBulkActionsBar();
}

function bulkAction(actionType) {
    if (selectedStudents.size === 0) {
        alert('Lütfen en az bir öğrenci seçin!');
        return;
    }
    
    let confirmMessage = '';
    
    switch(actionType) {
        case 'activate':
            confirmMessage = selectedStudents.size + ' öğrenciyi AKTİF yapmak istediğinizden emin misiniz?';
            break;
        case 'deactivate':
            confirmMessage = selectedStudents.size + ' öğrenciyi PASİF yapmak istediğinizden emin misiniz?';
            break;
        case 'delete':
            confirmMessage = selectedStudents.size + ' öğrenciyi ARŞİVE TAŞIMAK istediğinizden emin misiniz?';
            break;
    }
    
    if (confirm(confirmMessage)) {
        $('#bulkActionType').val(actionType);
        $('#bulkStudentIds').val(Array.from(selectedStudents).join(','));
        $('#bulkActionForm').submit();
    }
}

function showBulkStatusModal() {
    if (selectedStudents.size === 0) {
        alert('Lütfen en az bir öğrenci seçin!');
        return;
    }
    
    $('#bulkStatusCount').text(selectedStudents.size);
    $('#bulkStatusModal').modal('show');
}

function applyBulkStatus() {
    const newStatus = $('#bulkStatusSelect').val();
    
    if (confirm(selectedStudents.size + ' öğrencinin durumunu değiştirmek istediğinizden emin misiniz?')) {
        $('#bulkActionType').val('change_status');
        $('#bulkStudentIds').val(Array.from(selectedStudents).join(','));
        $('#bulkNewStatus').val(newStatus);
        $('#bulkStatusModal').modal('hide');
        $('#bulkActionForm').submit();
    }
}

function exportToExcel(type) {
    if (type === 'selected' && selectedStudents.size === 0) {
        alert('Lütfen en az bir öğrenci seçin!');
        return;
    }
    
    const url = 'index.php?module=students&action=export_excel&type=' + type;
    
    if (type === 'selected') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'student_ids';
        input.value = Array.from(selectedStudents).join(',');
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    } else {
        window.location.href = url;
    }
}
</script>