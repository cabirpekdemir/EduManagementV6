<?php
/**
 * Bilim Merkezi Yönetim Sistemi - TODO List
 * Yeni Görev Ekleme Özellikli Versiyon
 */

// JSON dosya yolu
$jsonFile = __DIR__ . '/todo_tasks.json';

// Varsayılan veri yapısı
$defaultData = [
    'categories' => [
        'ogrenciler' => ['name' => '👨‍🎓 Öğrenciler Modülü', 'color' => '#3b82f6', 'icon' => 'fa-user-graduate', 'tasks' => []],
        'degerlendirmeler' => ['name' => '📊 Değerlendirmeler', 'color' => '#8b5cf6', 'icon' => 'fa-chart-line', 'tasks' => []],
        'dersler' => ['name' => '📚 Dersler', 'color' => '#10b981', 'icon' => 'fa-book', 'tasks' => []],
        'yoklama' => ['name' => '✅ Yoklama', 'color' => '#f59e0b', 'icon' => 'fa-calendar-check', 'tasks' => []],
        'anasayfa' => ['name' => '🏠 Anasayfa & Dashboard', 'color' => '#ef4444', 'icon' => 'fa-home', 'tasks' => []],
        'performans' => ['name' => '⚡ Performans & Genel', 'color' => '#6366f1', 'icon' => 'fa-rocket', 'tasks' => []],
        'etkinlik' => ['name' => '🎉 Etkinlik Yönetimi', 'color' => '#ec4899', 'icon' => 'fa-calendar-alt', 'tasks' => []],
    ]
];

// JSON dosyasını oku veya varsayılan veriyi kullan
if (file_exists($jsonFile)) {
    $todoData = json_decode(file_get_contents($jsonFile), true);
} else {
    $todoData = $defaultData;
    // İlk kez açılıyorsa, örnek verilerle doldur
    include 'initial_data.php'; // Aşağıda oluşturacağız
}

// Yeni görev ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_task') {
    $category = $_POST['category'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    
    if ($category && $title && isset($todoData['categories'][$category])) {
        // Yeni ID oluştur
        $prefix = strtoupper(substr($category, 0, 3));
        $existingIds = [];
        foreach ($todoData['categories'][$category]['tasks'] as $task) {
            if (preg_match('/' . strtolower($prefix) . '-(\d+)/i', $task['id'], $matches)) {
                $existingIds[] = (int)$matches[1];
            }
        }
        $nextId = empty($existingIds) ? 1 : max($existingIds) + 1;
        $newId = strtolower($prefix) . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        
        // Yeni görev
        $newTask = [
            'id' => $newId,
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'status' => 'todo',
            'date' => null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Kategorinin EN ALTINA ekle
        $todoData['categories'][$category]['tasks'][] = $newTask;
        
        // JSON'a kaydet
        file_put_contents($jsonFile, json_encode($todoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // Başarı mesajı
        $successMessage = "✅ Görev başarıyla eklendi: $newId - $title";
    } else {
        $errorMessage = "❌ Lütfen tüm alanları doldurun!";
    }
}

// Görev durumu güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $taskId = $_POST['task_id'] ?? '';
    $newStatus = $_POST['new_status'] ?? '';
    
    if ($taskId && $newStatus) {
        $updated = false;
        foreach ($todoData['categories'] as $catKey => &$category) {
            foreach ($category['tasks'] as &$task) {
                if ($task['id'] === $taskId) {
                    $task['status'] = $newStatus;
                    if ($newStatus === 'completed') {
                        $task['date'] = date('Y-m-d');
                    }
                    $updated = true;
                    break 2;
                }
            }
        }
        
        if ($updated) {
            file_put_contents($jsonFile, json_encode($todoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $successMessage = "✅ Görev durumu güncellendi!";
        }
    }
}
// GÖREV SİLME
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_task') {
    $taskId = $_POST['task_id'] ?? '';
    
    if ($taskId) {
        $deleted = false;
        $deletedTaskTitle = '';
        
        foreach ($todoData['categories'] as $catKey => &$category) {
            foreach ($category['tasks'] as $taskKey => $task) {
                if ($task['id'] === $taskId) {
                    $deletedTaskTitle = $task['title'];
                    // Görevi array'den kaldır
                    unset($category['tasks'][$taskKey]);
                    $deleted = true;
                    break 2; // Her iki döngüden de çık
                }
            }
            // Array'i yeniden indeksle (JSON'un bozulmaması için önemli)
            if (isset($category['tasks'])) { // tasks boş değilse
                $category['tasks'] = array_values($category['tasks']);
            }
        }
        
        if ($deleted) {
            file_put_contents($jsonFile, json_encode($todoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $successMessage = "🗑️ Görev başarıyla silindi: $deletedTaskTitle";
        }
    }
}

// İstatistikler hesaplama
// İstatistikler hesaplama
$stats = [
    'total' => 0,
    'completed' => 0,
    'in-progress' => 0,
    'todo' => 0,
    'critical' => 0,
    'high' => 0,
    'medium' => 0,
    'low' => 0
];

foreach ($todoData['categories'] as $category) {
    foreach ($category['tasks'] as $task) {
        $stats['total']++;
        $stats[$task['status']]++;
        $stats[$task['priority']]++;
    }
}

$completionRate = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geliştirme TODO Listesi - Bilim Merkezi YS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --purple-color: #8b5cf6;
            --pink-color: #ec4899;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        
        .container {
            max-width: 1400px;
        }
        
        .header-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .header-card h1 {
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-card.completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .stat-card.in-progress {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .stat-card.todo {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }
        
        .progress-bar-custom {
            height: 40px;
            border-radius: 20px;
            background: #e2e8f0;
            overflow: hidden;
            margin-top: 20px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            transition: width 0.5s ease;
        }
        
        /* Yeni Görev Butonu */
        .add-task-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            font-size: 1.5rem;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
            cursor: pointer;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .add-task-btn:hover {
            transform: scale(1.1);
        }
        
        /* Modal Stilleri */
        .modal-content {
            border-radius: 20px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 12px;
        }
        
        .btn-add-task {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
        }
        
        .category-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        
        .category-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .category-title {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .category-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .task-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #cbd5e1;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .task-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .task-card.completed {
            border-left-color: #10b981;
            background: #f0fdf4;
            opacity: 0.7;
        }
        
        .task-card.in-progress {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
        
        .task-card.todo {
            border-left-color: #3b82f6;
        }
        
        .task-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .task-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            flex: 1;
        }
        
        .task-badges {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }
        
        .badge-priority {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-priority.critical {
            background: #fecaca;
            color: #991b1b;
        }
        
        .badge-priority.high {
            background: #fed7aa;
            color: #9a3412;
        }
        
        .badge-priority.medium {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-priority.low {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }
        
        .badge-status.completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-status.in-progress {
            background: #fed7aa;
            color: #9a3412;
        }
        
        .badge-status.todo {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-delete {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #fee2e2;
            color: #b91c1c;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            height: 24px; /* Diğerleriyle aynı hizada olsun */
        }
        
        .badge-delete:hover {
            background: #b91c1c;
            color: white;
        }
        
        .task-description {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .task-id {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 10px;
        }
        
        .filter-bar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        
        .alert-success, .alert-danger {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .status-dropdown {
            position: absolute;
            top: 50px;
            right: 10px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            padding: 10px;
            z-index: 9999;
            display: none;
        }
        
        .task-card {
            position: relative;
            z-index: 1;
        }
        
        .task-card.dropdown-open {
            z-index: 10000;
        }
        
        .status-option {
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 6px;
            margin-bottom: 5px;
            transition: background 0.2s;
        }
        
        .status-option:hover {
            background: #f1f5f9;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .add-task-btn {
                bottom: 20px;
                right: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Flash Mesajları -->
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $successMessage ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $errorMessage ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- HEADER CARD -->
        <div class="header-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>🚀 Geliştirme TODO Listesi</h1>
                    <p class="subtitle">Bilim Merkezi Yönetim Sistemi - Tüm İstekler ve İyileştirmeler</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-muted">Son Güncelleme</div>
                    <div class="fs-5 fw-bold"><?= date('d.m.Y H:i') ?></div>
                </div>
            </div>
            
            <!-- İSTATİSTİKLER -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?= $stats['total'] ?></div>
                    <div class="label">Toplam Görev</div>
                </div>
                <div class="stat-card completed">
                    <div class="number"><?= $stats['completed'] ?></div>
                    <div class="label">✅ Tamamlandı</div>
                </div>
                <div class="stat-card in-progress">
                    <div class="number"><?= $stats['in-progress'] ?></div>
                    <div class="label">🔄 Devam Ediyor</div>
                </div>
                <div class="stat-card todo">
                    <div class="number"><?= $stats['todo'] ?></div>
                    <div class="label">📝 Yapılacak</div>
                </div>
            </div>
            
            <!-- İLERLEME ÇUBUĞU -->
            <div class="progress-bar-custom">
                <div class="progress-fill" style="width: <?= $completionRate ?>%">
                    %<?= $completionRate ?> Tamamlandı
                </div>
            </div>
        </div>
        
        <!-- FİLTRELEME -->
        <div class="filter-bar">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <select id="filterStatus" class="form-select">
                        <option value="">Tüm Durumlar</option>
                        <option value="completed">✅ Tamamlandı</option>
                        <option value="in-progress">🔄 Devam Ediyor</option>
                        <option value="todo">📝 Yapılacak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filterPriority" class="form-select">
                        <option value="">Tüm Öncelikler</option>
                        <option value="critical">🔴 Kritik</option>
                        <option value="high">🟠 Yüksek</option>
                        <option value="medium">🟡 Orta</option>
                        <option value="low">🟢 Düşük</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" id="searchTask" class="form-control" placeholder="🔍 Görev Ara...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" onclick="applyFilters()">
                        Filtrele
                    </button>
                </div>
            </div>
        </div>
        
        <!-- KATEGORİLER -->
        <?php foreach ($todoData['categories'] as $catKey => $category): ?>
            <?php 
            $catStats = ['completed' => 0, 'in-progress' => 0, 'todo' => 0, 'total' => count($category['tasks'])];
            foreach ($category['tasks'] as $task) {
                $catStats[$task['status']]++;
            }
            $catCompletion = $catStats['total'] > 0 ? round(($catStats['completed'] / $catStats['total']) * 100) : 0;
            ?>
            
            <div class="category-section" data-category="<?= $catKey ?>">
                <div class="category-header">
                    <div class="category-title">
                        <div class="category-icon" style="background: <?= $category['color'] ?>">
                            <i class="fas <?= $category['icon'] ?>"></i>
                        </div>
                        <div>
                            <?= $category['name'] ?>
                            <div style="font-size: 0.85rem; color: #64748b; font-weight: 400;">
                                <?= $catStats['total'] ?> görev
                            </div>
                        </div>
                    </div>
                    <div class="category-stats">
                        <span class="badge bg-success">✅ <?= $catStats['completed'] ?></span>
                        <span class="badge bg-warning">🔄 <?= $catStats['in-progress'] ?></span>
                        <span class="badge bg-primary">📝 <?= $catStats['todo'] ?></span>
                        <span class="badge" style="background: <?= $category['color'] ?>">%<?= $catCompletion ?></span>
                    </div>
                </div>
                
                <!-- GÖREVLER -->
                <?php foreach ($category['tasks'] as $task): ?>
                    <div class="task-card <?= $task['status'] ?>" 
                         data-status="<?= $task['status'] ?>" 
                         data-priority="<?= $task['priority'] ?>"
                         data-search="<?= strtolower($task['title'] . ' ' . $task['description']) ?>">
                        <div class="task-header">
                            <div class="task-title">
                                <?php if ($task['status'] == 'completed'): ?>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                <?php elseif ($task['status'] == 'in-progress'): ?>
                                    <i class="fas fa-spinner fa-spin text-warning me-2"></i>
                                <?php else: ?>
                                    <i class="far fa-circle text-muted me-2"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($task['title']) ?>
                            </div>
                            <div class="task-badges">
                                <span class="badge-priority <?= $task['priority'] ?>">
                                    <?php
                                    $priorities = [
                                        'critical' => '🔴 Kritik',
                                        'high' => '🟠 Yüksek',
                                        'medium' => '🟡 Orta',
                                        'low' => '🟢 Düşük'
                                    ];
                                    echo $priorities[$task['priority']];
                                    ?>
                                </span>
                                <span class="badge-status <?= $task['status'] ?>" 
                                      onclick="toggleStatusDropdown(this, '<?= $task['id'] ?>')">
                                    <?php
                                    $statuses = [
                                        'completed' => '✅ Tamam',
                                        'in-progress' => '🔄 Devam',
                                        'todo' => '📝 Yapılacak'
                                    ];
                                    echo $statuses[$task['status']];
                                    ?>
                                </span><span class="badge-delete" title="Görevi Sil" 
                                      onclick="deleteTask('<?= $task['id'] ?>', '<?= htmlspecialchars(addslashes($task['title'])) ?>')">
                                    <i class="fas fa-trash-alt"></i>
                                </span>
                                <div class="status-dropdown" id="dropdown-<?= $task['id'] ?>">
                                    <div class="status-option" onclick="updateStatus('<?= $task['id'] ?>', 'todo')">
                                        📝 Yapılacak
                                    </div>
                                    <div class="status-option" onclick="updateStatus('<?= $task['id'] ?>', 'in-progress')">
                                        🔄 Devam Ediyor
                                    </div>
                                    <div class="status-option" onclick="updateStatus('<?= $task['id'] ?>', 'completed')">
                                        ✅ Tamamlandı
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="task-description">
                            <?= htmlspecialchars($task['description']) ?>
                        </div>
                        <div class="task-id">
                            ID: <?= $task['id'] ?>
                            <?php if ($task['date']): ?>
                                | ✅ <?= date('d.m.Y', strtotime($task['date'])) ?>
                            <?php endif; ?>
                            <?php if (isset($task['created_at'])): ?>
                                | 📅 Oluşturulma: <?= date('d.m.Y H:i', strtotime($task['created_at'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <!-- FOOTER -->
        <div class="text-center text-white mt-5 mb-3">
            <p style="font-size: 0.9rem; opacity: 0.9;">
                💪 Birlikte harika bir sistem kuruyoruz! Her tamamlanan görev bir başarıdır.
            </p>
        </div>
    </div>
    <form id="updateStatusForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="task_id" id="updateTaskId">
        <input type="hidden" name="new_status" id="updateNewStatus">
    </form>
    
    <form id="deleteTaskForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete_task">
        <input type="hidden" name="task_id" id="deleteTaskId">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- YENİ GÖREV EKLEME BUTONU -->
    <button class="add-task-btn" data-bs-toggle="modal" data-bs-target="#addTaskModal" title="Yeni Görev Ekle">
        <i class="fas fa-plus"></i>
    </button>
    
    <!-- YENİ GÖREV MODAL -->
    <div class="modal fade" id="addTaskModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        Yeni Görev Ekle
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_task">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="">Kategori Seçin...</option>
                                <?php foreach ($todoData['categories'] as $catKey => $cat): ?>
                                    <option value="<?= $catKey ?>"><?= $cat['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Görev Başlığı <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" 
                                   placeholder="Örn: Öğrenci Listesi Filtreleme" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Açıklama <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="Görevin detaylı açıklaması..." required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Öncelik <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="low">🟢 Düşük - UI temizleme, küçük iyileştirmeler</option>
                                <option value="medium" selected>🟡 Orta - İyileştirmeler ve orta öncelikli özellikler</option>
                                <option value="high">🟠 Yüksek - Önemli özellikler ve kullanıcı deneyimi</option>
                                <option value="critical">🔴 Kritik - Acil çözülmesi gereken sorunlar</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Not:</strong> Yeni görev, seçilen kategorinin <strong>EN ALTINA</strong> eklenecektir.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> İptal
                        </button>
                        <button type="submit" class="btn btn-add-task">
                            <i class="fas fa-plus"></i> Görev Ekle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Durum Güncelleme Formu (Gizli) -->
    <form id="updateStatusForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="task_id" id="updateTaskId">
        <input type="hidden" name="new_status" id="updateNewStatus">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function applyFilters() {
            const status = document.getElementById('filterStatus').value;
            const priority = document.getElementById('filterPriority').value;
            const search = document.getElementById('searchTask').value.toLowerCase();
            
            const tasks = document.querySelectorAll('.task-card');
            
            tasks.forEach(task => {
                const taskStatus = task.getAttribute('data-status');
                const taskPriority = task.getAttribute('data-priority');
                const taskSearch = task.getAttribute('data-search');
                
                let show = true;
                
                if (status && taskStatus !== status) show = false;
                if (priority && taskPriority !== priority) show = false;
                if (search && !taskSearch.includes(search)) show = false;
                
                task.style.display = show ? 'block' : 'none';
            });
        }
        
        // Gerçek zamanlı arama
        document.getElementById('searchTask').addEventListener('input', applyFilters);
        
        // Durum dropdown toggle
        function toggleStatusDropdown(badge, taskId) {
            const dropdown = document.getElementById('dropdown-' + taskId);
            const taskCard = dropdown.closest('.task-card');
            
            // Diğer açık dropdownları kapat
            document.querySelectorAll('.status-dropdown').forEach(d => {
                if (d.id !== 'dropdown-' + taskId) {
                    d.style.display = 'none';
                    d.closest('.task-card').classList.remove('dropdown-open');
                }
            });
            
            // Toggle
            const isOpen = dropdown.style.display === 'block';
            dropdown.style.display = isOpen ? 'none' : 'block';
            
            // Parent task card'a class ekle/çıkar
            if (isOpen) {
                taskCard.classList.remove('dropdown-open');
            } else {
                taskCard.classList.add('dropdown-open');
            }
        }
        
        // Durum güncelle
        function updateStatus(taskId, newStatus) {
            document.getElementById('updateTaskId').value = taskId;
            document.getElementById('updateNewStatus').value = newStatus;
            document.getElementById('updateStatusForm').submit();
        }
        
        // --- BURAYA EKLENDİ ---
        // GÖREV SİLME FONKSİYONU
        function deleteTask(taskId, taskTitle) {
            const confirmation = confirm(
                "Görevi silmek istediğinizden emin misiniz?\n\n" +
                "Görev: " + taskTitle + "\n\n" +
                "Bu işlem geri alınamaz!"
            );
            
            if (confirmation) {
                document.getElementById('deleteTaskId').value = taskId;
                document.getElementById('deleteTaskForm').submit();
            }
        }
        // --- EKLENEN KISMIN SONU ---
        
        // Dışarı tıklanınca dropdown'ları kapat
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.badge-status')) {
                document.querySelectorAll('.status-dropdown').forEach(d => {
                    d.style.display = 'none';
                });
                // Tüm task cardlardan dropdown-open class'ını kaldır
                document.querySelectorAll('.task-card').forEach(card => {
                    card.classList.remove('dropdown-open');
                });
            }
        });
    </script>
</body>
</html>