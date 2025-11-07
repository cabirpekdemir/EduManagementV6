<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?= $pageTitle ?></h1>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
                    <button class="btn btn-success" data-toggle="modal" data-target="#addWidgetModal">
                        <i class="fas fa-plus"></i> Yeni Widget
                    </button>
                    <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>?module=dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tüm Widget'lar</h3>
                <div class="card-tools">
                    <span class="badge badge-info"><?= count($widgets) ?> widget</span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Sıra</th>
                            <th style="width: 60px;"></th>
                            <th>Başlık</th>
                            <th style="width: 100px;">Tip</th>
                            <th>Roller</th>
                            <th style="width: 80px;">Durum</th>
                            <th style="width: 220px;" class="text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($widgets)): ?>
                            <?php foreach ($widgets as $index => $widget): ?>
                                <tr data-widget-id="<?= $widget['id'] ?>">
                                    <td><?= $widget['sort_order'] ?></td>
                                    <td>
                                        <i class="<?= $widget['icon'] ?> fa-2x text-<?= $widget['color'] ?>"></i>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($widget['title']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= $widget['icon'] ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $types = [
                                            'stat' => '<span class="badge badge-primary">İstatistik</span>',
                                            'action' => '<span class="badge badge-success">Aksiyon</span>',
                                            'list' => '<span class="badge badge-info">Liste</span>'
                                        ];
                                        echo $types[$widget['widget_type']] ?? $widget['widget_type'];
                                        ?>
                                    </td>
                                    <td>
                                        <div class="widget-roles" data-widget-id="<?= $widget['id'] ?>">
                                            <?php
                                            if (!empty($widget['roles'])) {
                                                $roles = explode(',', $widget['roles']);
                                                $roleNames = [
                                                    'admin' => 'Admin',
                                                    'teacher' => 'Öğretmen',
                                                    'student' => 'Öğrenci',
                                                    'parent' => 'Veli'
                                                ];
                                                foreach ($roles as $role) {
                                                    $roleName = $roleNames[$role] ?? $role;
                                                    echo '<span class="badge badge-secondary mr-1">' . $roleName . '</span>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">Rol yok</span>';
                                            }
                                            ?>
                                        </div>
                                        <button class="btn btn-xs btn-link edit-roles" 
                                                data-widget-id="<?= $widget['id'] ?>"
                                                data-current-roles="<?= $widget['roles'] ?? '' ?>">
                                            <i class="fas fa-edit"></i> Düzenle
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge">
                                            <?php if ($widget['is_active']): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Pasif</span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <!-- Yukarı/Aşağı -->
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($index > 0): ?>
                                                <button class="btn btn-default move-widget" 
                                                        data-widget-id="<?= $widget['id'] ?>"
                                                        data-direction="up"
                                                        title="Yukarı">
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($index < count($widgets) - 1): ?>
                                                <button class="btn btn-default move-widget" 
                                                        data-widget-id="<?= $widget['id'] ?>"
                                                        data-direction="down"
                                                        title="Aşağı">
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Düzenle -->
                                        <button class="btn btn-sm btn-info edit-widget"
                                                data-widget-id="<?= $widget['id'] ?>"
                                                data-widget-title="<?= htmlspecialchars($widget['title']) ?>"
                                                data-widget-type="<?= $widget['widget_type'] ?>"
                                                data-widget-icon="<?= htmlspecialchars($widget['icon']) ?>"
                                                data-widget-color="<?= $widget['color'] ?>"
                                                data-widget-config="<?= htmlspecialchars($widget['config']) ?>"
                                                title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <!-- Aktif/Pasif -->
                                        <button class="btn btn-sm btn-<?= $widget['is_active'] ? 'warning' : 'success' ?> toggle-widget"
                                                data-widget-id="<?= $widget['id'] ?>"
                                                title="<?= $widget['is_active'] ? 'Pasif Yap' : 'Aktif Yap' ?>">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                        
                                        <!-- Sil -->
                                        <button class="btn btn-sm btn-danger delete-widget"
                                                data-widget-id="<?= $widget['id'] ?>"
                                                data-widget-title="<?= htmlspecialchars($widget['title']) ?>"
                                                title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Henüz widget bulunmuyor.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<!-- Rol Düzenleme Modal -->
<div class="modal fade" id="editRolesModal" tabindex="-1">
    <div class="modal-dialog modal-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rolleri Düzenle</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-widget-id">
                <div class="form-group">
                    <label>Bu widget'ı hangi roller görsün?</label>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="role-admin" value="admin">
                        <label class="custom-control-label" for="role-admin">Admin</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="role-teacher" value="teacher">
                        <label class="custom-control-label" for="role-teacher">Öğretmen</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="role-student" value="student">
                        <label class="custom-control-label" for="role-student">Öğrenci</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="role-parent" value="parent">
                        <label class="custom-control-label" for="role-parent">Veli</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" id="saveRoles">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Widget Modal -->
<div class="modal fade" id="addWidgetModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Widget Ekle</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addWidgetForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Başlık *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tip *</label>
                                <select class="form-control" name="type" required>
                                    <option value="stat">İstatistik</option>
                                    <option value="action">Aksiyon</option>
                                    <option value="view_as">View As (Farklı Kullanıcı Olarak Görüntüle)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>İkon *</label>
                                <input type="text" class="form-control" name="icon" 
                                       value="fas fa-chart-line" required>
                                <small class="text-muted">Örnek: fas fa-user, fas fa-book</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Renk *</label>
                                <select class="form-control" name="color" required>
                                    <option value="primary">Mavi (Primary)</option>
                                    <option value="success">Yeşil (Success)</option>
                                    <option value="info">Açık Mavi (Info)</option>
                                    <option value="warning">Sarı (Warning)</option>
                                    <option value="danger">Kırmızı (Danger)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- İSTATİSTİK WIDGET AYARLARI -->
                    <div id="stat-config" class="widget-config-section">
                        <div class="form-group">
                            <label>SQL Sorgusu *</label>
                            <textarea class="form-control" name="stat_query" rows="3" placeholder="SELECT COUNT(*) as value FROM users WHERE role='student'"></textarea>
                            <small class="text-muted">Sorgu mutlaka "value" adında bir sütun döndürmelidir.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Link (Opsiyonel)</label>
                            <input type="text" class="form-control" name="stat_link" placeholder="index.php?module=students">
                            <small class="text-muted">Widget'a tıklandığında gidilecek sayfa. Boş bırakılırsa dashboard'a döner.</small>
                        </div>
                    </div>
                    
                    <!-- AKSİYON WIDGET AYARLARI -->
                    <div id="action-config" class="widget-config-section" style="display: none;">
                        <div class="form-group">
                            <label>Link *</label>
                            <input type="text" class="form-control" name="action_link" placeholder="index.php?module=students&action=new">
                            <small class="text-muted">Butona tıklandığında gidilecek sayfa.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Buton Yazısı *</label>
                            <input type="text" class="form-control" name="action_button_text" placeholder="Yeni Ekle" value="Tıkla">
                            <small class="text-muted">Butonun üzerinde görünecek yazı.</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>İpucu:</strong> 
                        <span id="tip-stat">İstatistik widget'ları sayısal değerler gösterir ve tıklanabilir.</span>
                        <span id="tip-action" style="display:none;">Aksiyon widget'ları bir butona sahiptir ve kullanıcıyı belirtilen sayfaya yönlendirir.</span>
                        <span id="tip-view-as" style="display:none;">View As widget'ı sadece admin kullanıcılar için görünür. Farklı kullanıcılar olarak sistemi görüntülemenizi sağlar. Ek ayar gerektirmez.</span>
                    </div>
                    
                    <div class="form-group">
                        <label>Roller *</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="new-role-admin" name="roles[]" value="admin">
                            <label class="custom-control-label" for="new-role-admin">Admin</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="new-role-teacher" name="roles[]" value="teacher">
                            <label class="custom-control-label" for="new-role-teacher">Öğretmen</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="new-role-student" name="roles[]" value="student">
                            <label class="custom-control-label" for="new-role-student">Öğrenci</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="new-role-parent" name="roles[]" value="parent">
                            <label class="custom-control-label" for="new-role-parent">Veli</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Widget Düzenle Modal -->
<div class="modal fade" id="editWidgetModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Widget Düzenle</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editWidgetForm">
                <input type="hidden" name="edit_widget_id" id="edit-widget-id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Başlık *</label>
                                <input type="text" class="form-control" name="edit_title" id="edit-title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tip *</label>
                                <select class="form-control" name="edit_type" id="edit-type" required disabled>
                                    <option value="stat">İstatistik</option>
                                    <option value="action">Aksiyon</option>
                                </select>
                                <small class="text-muted">Widget tipi değiştirilemez</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>İkon *</label>
                                <input type="text" class="form-control" name="edit_icon" id="edit-icon" required>
                                <small class="text-muted">Örnek: fas fa-user, fas fa-book</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Renk *</label>
                                <select class="form-control" name="edit_color" id="edit-color" required>
                                    <option value="primary">Mavi (Primary)</option>
                                    <option value="success">Yeşil (Success)</option>
                                    <option value="info">Açık Mavi (Info)</option>
                                    <option value="warning">Sarı (Warning)</option>
                                    <option value="danger">Kırmızı (Danger)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- İSTATİSTİK WIDGET AYARLARI -->
                    <div id="edit-stat-config" class="widget-config-section">
                        <div class="form-group">
                            <label>SQL Sorgusu *</label>
                            <textarea class="form-control" name="edit_stat_query" id="edit-stat-query" rows="3"></textarea>
                            <small class="text-muted">Sorgu mutlaka "value" adında bir sütun döndürmelidir.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Link (Opsiyonel)</label>
                            <input type="text" class="form-control" name="edit_stat_link" id="edit-stat-link" placeholder="index.php?module=students">
                            <small class="text-muted">Widget'a tıklandığında gidilecek sayfa.</small>
                        </div>
                    </div>
                    
                    <!-- AKSİYON WIDGET AYARLARI -->
                    <div id="edit-action-config" class="widget-config-section" style="display: none;">
                        <div class="form-group">
                            <label>Link *</label>
                            <input type="text" class="form-control" name="edit_action_link" id="edit-action-link">
                            <small class="text-muted">Butona tıklandığında gidilecek sayfa.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Buton Yazısı *</label>
                            <input type="text" class="form-control" name="edit_action_button_text" id="edit-action-button-text">
                            <small class="text-muted">Butonun üzerinde görünecek yazı.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tam URL ile baseUrl oluştur
const baseUrl = window.location.origin + window.location.pathname.replace(/\?.*$/, '').replace(/\/[^\/]*$/, '/') + 'index.php';

console.log('baseUrl:', baseUrl);

$(document).ready(function() {
    
    // Widget Aktif/Pasif (Event Delegation)
    $(document).on('click', '.toggle-widget', function() {
        const widgetId = $(this).data('widget-id');
        const row = $(`tr[data-widget-id="${widgetId}"]`);
        
        if (confirm('Widget durumunu değiştirmek istediğinizden emin misiniz?')) {
            $.post(baseUrl + '?module=dashboard&action=toggleWidget', 
                {widget_id: widgetId},
                function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Hata: ' + response.message);
                    }
                }, 'json')
                .fail(function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    alert('AJAX Hatası: ' + error);
                });
        }
    });
    
    // Widget Düzenle Butonuna Tıklama (Event Delegation)
    $(document).on('click', '.edit-widget', function() {
        const widgetId = $(this).data('widget-id');
        const title = $(this).data('widget-title');
        const type = $(this).data('widget-type');
        const icon = $(this).data('widget-icon');
        const color = $(this).data('widget-color');
        const configStr = $(this).data('widget-config');
        
        console.log('Editing widget:', widgetId, title, type);
        
        // Form alanlarını doldur
        $('#edit-widget-id').val(widgetId);
        $('#edit-title').val(title);
        $('#edit-type').val(type);
        $('#edit-icon').val(icon);
        $('#edit-color').val(color);
        
        // Config'i parse et
        let config = {};
        try {
            config = JSON.parse(configStr);
        } catch (e) {
            console.error('Config parse error:', e);
        }
        
        // Tip-e göre form alanlarını göster/gizle ve doldur
        if (type === 'stat') {
            $('#edit-stat-config').show();
            $('#edit-action-config').hide();
            $('#edit-stat-query').val(config.query || '');
            $('#edit-stat-link').val(config.link || '');
        } else if (type === 'action') {
            $('#edit-stat-config').hide();
            $('#edit-action-config').show();
            $('#edit-action-link').val(config.link || '');
            $('#edit-action-button-text').val(config.button_text || '');
        }
        
        // Modal'ı aç
        $('#editWidgetModal').modal('show');
    });
    
    // Widget Düzenle Form Submit
    $('#editWidgetForm').submit(function(e) {
        e.preventDefault();
        
        const widgetId = $('#edit-widget-id').val();
        const type = $('#edit-type').val();
        const config = {};
        
        // Tip-e göre config oluştur
        if (type === 'stat') {
            const query = $('#edit-stat-query').val();
            const link = $('#edit-stat-link').val();
            
            if (!query) {
                alert('SQL sorgusu zorunludur!');
                return false;
            }
            
            config.query = query;
            if (link) {
                config.link = link;
            }
        } else if (type === 'action') {
            const link = $('#edit-action-link').val();
            const buttonText = $('#edit-action-button-text').val();
            
            if (!link) {
                alert('Link zorunludur!');
                return false;
            }
            if (!buttonText) {
                alert('Buton yazısı zorunludur!');
                return false;
            }
            
            config.link = link;
            config.button_text = buttonText;
        }
        
        const formData = {
            widget_id: widgetId,
            title: $('#edit-title').val(),
            icon: $('#edit-icon').val(),
            color: $('#edit-color').val(),
            config: JSON.stringify(config)
        };
        
        console.log('Updating widget:', formData);
        
        $.post(baseUrl + '?module=dashboard&action=update',
            formData,
            function(response) {
                console.log('Update response:', response);
                if (response.success) {
                    $('#editWidgetModal').modal('hide');
                    location.reload();
                } else {
                    alert('Hata: ' + response.message);
                }
            }, 'json')
            .fail(function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                alert('AJAX Hatası: ' + error);
            });
    });
    
    // Widget Yukarı/Aşağı (Event Delegation)
    $(document).on('click', '.move-widget', function() {
        const widgetId = $(this).data('widget-id');
        const direction = $(this).data('direction');
        
        console.log('Moving widget:', widgetId, direction);
        
        $.post(baseUrl + '?module=dashboard&action=moveWidget',
            {widget_id: widgetId, direction: direction},
            function(response) {
                console.log('Move response:', response);
                if (response.success) {
                    location.reload();
                } else {
                    alert('Hata: ' + response.message);
                }
            }, 'json')
            .fail(function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                alert('AJAX Hatası: ' + error + '\nURL: ' + baseUrl + '?module=dashboard&action=moveWidget');
            });
    });
    
    // Rolleri Düzenle (Event Delegation)
    $(document).on('click', '.edit-roles', function() {
        const widgetId = $(this).data('widget-id');
        const currentRolesData = $(this).data('current-roles');
        
        console.log('Widget ID:', widgetId);
        console.log('Current Roles Data:', currentRolesData);
        
        $('#edit-widget-id').val(widgetId);
        
        // Checkbox'ları temizle
        $('[id^="role-"]').prop('checked', false);
        
        // Mevcut rolleri işaretle (eğer varsa)
        if (currentRolesData && currentRolesData !== '') {
            const currentRoles = currentRolesData.split(',');
            currentRoles.forEach(role => {
                const trimmedRole = role.trim();
                if (trimmedRole) {
                    console.log('Checking role:', trimmedRole);
                    $(`#role-${trimmedRole}`).prop('checked', true);
                }
            });
        }
        
        $('#editRolesModal').modal('show');
    });
    
    // Rolleri Kaydet
    $('#saveRoles').click(function() {
        const widgetId = $('#edit-widget-id').val();
        const roles = [];
        
        $('[id^="role-"]:checked').each(function() {
            roles.push($(this).val());
        });
        
        console.log('Saving roles:', widgetId, roles);
        
        $.post(baseUrl + '?module=dashboard&action=updateWidgetRoles',
            {widget_id: widgetId, roles: roles},
            function(response) {
                console.log('Save roles response:', response);
                if (response.success) {
                    $('#editRolesModal').modal('hide');
                    location.reload();
                } else {
                    alert('Hata: ' + response.message);
                }
            }, 'json')
            .fail(function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                alert('AJAX Hatası: ' + error + '\nURL: ' + baseUrl + '?module=dashboard&action=updateWidgetRoles');
            });
    });
    
    // Widget Sil (Event Delegation)
    $(document).on('click', '.delete-widget', function() {
        const widgetId = $(this).data('widget-id');
        const widgetTitle = $(this).data('widget-title');
        
        if (confirm(`"${widgetTitle}" widget'ını silmek istediğinizden emin misiniz?`)) {
            $.post(baseUrl + '?module=dashboard&action=delete',
                {widget_id: widgetId},
                function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Hata: ' + response.message);
                    }
                }, 'json')
                .fail(function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    alert('AJAX Hatası: ' + error);
                });
        }
    });
    
    
    // Widget Tipi Değiştiğinde Formu Güncelle
    $('[name="type"]').change(function() {
        const type = $(this).val();
        
        // Tüm config bölümlerini gizle
        $('.widget-config-section').hide();
        
        // Seçili tipe göre ilgili bölümü göster
        if (type === 'stat') {
            $('#stat-config').show();
            $('#tip-stat').show();
            $('#tip-action').hide();
            $('#tip-view-as').hide();
        } else if (type === 'action') {
            $('#action-config').show();
            $('#tip-stat').hide();
            $('#tip-action').show();
            $('#tip-view-as').hide();
        } else if (type === 'view_as') {
            // View as için config gerekmez
            $('#tip-stat').hide();
            $('#tip-action').hide();
            $('#tip-view-as').show();
        }
    });
    
    // Sayfa yüklendiğinde ilk tipi tetikle
    $('[name="type"]').trigger('change');
    
    // Yeni Widget Ekle
    $('#addWidgetForm').submit(function(e) {
        e.preventDefault();
        
        const type = $('[name="type"]').val();
        let config = {};
        
        // Tipi göre config oluştur
        if (type === 'stat') {
            const query = $('[name="stat_query"]').val();
            const link = $('[name="stat_link"]').val();
            
            if (!query) {
                alert('SQL Sorgusu zorunludur!');
                return false;
            }
            
            config.query = query;
            if (link) {
                config.link = link;
            }
        } else if (type === 'action') {
            const link = $('[name="action_link"]').val();
            const buttonText = $('[name="action_button_text"]').val();
            
            if (!link) {
                alert('Link zorunludur!');
                return false;
            }
            if (!buttonText) {
                alert('Buton yazısı zorunludur!');
                return false;
            }
            
            config.link = link;
            config.button_text = buttonText;
        } else if (type === 'view_as') {
            // View as için config boş (widget kendi içinde form render eder)
            config = {};
        }
        
        const formData = {
            title: $('[name="title"]').val(),
            type: type,
            icon: $('[name="icon"]').val(),
            color: $('[name="color"]').val(),
            config: JSON.stringify(config),
            roles: []
        };
        
        $('[name="roles[]"]:checked').each(function() {
            formData.roles.push($(this).val());
        });
        
        console.log('Gönderilen data:', formData);
        
        $.post(baseUrl + '?module=dashboard&action=store',
            formData,
            function(response) {
                if (response.success) {
                    $('#addWidgetModal').modal('hide');
                    location.reload();
                } else {
                    alert('Hata: ' + response.message);
                }
            }, 'json')
            .fail(function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                alert('AJAX Hatası: ' + error);
            });
    });
    
});
</script>