<style>
    /* Profil Navigasyon Butonları */
    .profile-nav {
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap; /* Butonların da sarması için */
        gap: 10px; /* Butonlar arası boşluk */
    }
    .profile-nav .btn {
        border-radius: 5px;
        padding: 8px 15px;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    .profile-nav .btn-primary {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
    .profile-nav .btn-secondary {
        background-color: #f0f0f0;
        color: #333;
        border-color: #ddd;
    }
    .profile-nav .btn:hover {
        opacity: 0.9;
    }

    /* Genel Kart Stili */
    .profile-card {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        padding: 30px;
        margin-bottom: 30px;
    }

    /* Profil Başlığı ve Fotoğrafı */
    .profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    .profile-header img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 25px;
        border: 3px solid #007bff;
        padding: 3px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .profile-header h2 {
        margin: 0;
        font-size: 1.8em;
        color: #333;
    }
    .profile-header p {
        margin: 5px 0 0;
        color: #777;
        font-size: 0.95em;
    }

    /* Form Grupları */
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
    }
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 1em;
        color: #333;
    }
    .form-group input[readonly] {
        background-color: #e9ecef;
        cursor: not-allowed;
    }
    .form-group input[type="file"] {
        padding: 5px 0;
    }
    .form-group button[type="submit"] {
        background-color: #28a745;
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1.1em;
        font-weight: 600;
        transition: background-color 0.2s ease;
    }
    .form-group button[type="submit"]:hover {
        background-color: #218838;
    }

    /* Rol Bazlı Bilgi Kutuları */
    .role-specific-info h3 {
        color: #007bff;
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 1.5em;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 10px;
    }
    .role-specific-info h4 {
        color: #555;
        font-size: 1.2em;
        margin-top: 20px;
        margin-bottom: 10px;
    }
    .role-specific-info ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .role-specific-info ul li {
        padding: 8px 0;
        border-bottom: 1px dashed #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .role-specific-info ul li:last-child {
        border-bottom: none;
    }
    .role-specific-info ul li a {
        color: #007bff;
        text-decoration: none;
    }
    .role-specific-info ul li a:hover {
        text-decoration: underline;
    }
    .role-specific-info p {
        color: #666;
        margin-top: 10px;
    }

    /* Mesaj Kutuları */
    .alert {
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        font-weight: bold;
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-color: #c3e6cb;
    }
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }

    /* İkiye Bölünmüş Düzen için Flexbox ve Responsive Ayarlar */
    .profile-container {
        display: flex;
        flex-wrap: wrap; /* Küçük ekranlarda alt alta sarması için */
        gap: 30px; /* Bölümler arası boşluk */
    }

    .profile-section {
        flex: 1; /* Esneklik ver, alanı doldursun */
        min-width: 300px; /* En küçük genişlik (mobil uyum için) */
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        padding: 30px;
    }

    /* Mobil Uyum */
    @media (max-width: 768px) {
        .profile-section {
            flex-basis: 100%; /* Tam genişlik */
            min-width: unset; /* Minimum genişliği kaldır */
        }
    }
</style>

<h2>Profilim</h2>

<div class="profile-nav">
    <a href="index.php?module=profile&action=index" class="btn <?= (($_GET['action'] ?? 'index') == 'index') ? 'btn-primary' : 'btn-secondary' ?>">Genel Bilgiler</a>
    <a href="index.php?module=profile&action=security" class="btn <?= (($_GET['action'] ?? '') == 'security') ? 'btn-primary' : 'btn-secondary' ?>">Güvenlik</a>
</div>

<?php if (isset($_GET['status_message'])): ?>
    <p class="alert alert-success">
        <?= htmlspecialchars_decode($_GET['status_message']) ?>
    </p>
<?php endif; ?>
<?php if (isset($_GET['error_message'])): ?>
    <p class="alert alert-danger">
        <?= htmlspecialchars_decode($_GET['error_message']) ?>
    </p>
<?php endif; ?>

<div class="profile-container">
    <div class="profile-section">
        <div class="profile-header">
            <img src="<?= e($user['profile_photo'] ?? 'themes/default/assets/img/default-profile.png') ?>" alt="Profil Fotoğrafı">
            <div>
                <h2><?= e($user['name'] ?? 'Kullanıcı Adı') ?></h2>
                <p><?= e($user['email'] ?? 'E-posta Bilgisi') ?></p>
                <p style="font-weight: bold; color: #007bff;"><?= e(ucfirst($userRole ?? 'Guest')) ?></p>
            </div>
        </div>

        <form method="post" action="index.php?module=profile&action=update" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= e($user['id'] ?? '') ?>">

            <div class="form-group">
                <label for="name">Ad Soyad:</label>
                <input type="text" id="name" name="name" value="<?= e($user['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">E-posta:</label>
                <input type="email" id="email" name="email" value="<?= e($user['email'] ?? '') ?>" required readonly>
            </div>

            <div class="form-group">
                <label for="tc_kimlik">TC Kimlik:</label>
                <input type="text" id="tc_kimlik" name="tc_kimlik" value="<?= e($user['tc_kimlik'] ?? '') ?>" maxlength="11" required>
            </div>

            <?php if (($user['role'] ?? '') === 'student'): // Sadece öğrenci ise sınıfını göster ?>
                <div class="form-group">
                    <label for="class_id">Sınıf:</label>
                    <select id="class_id" name="class_id">
                        <option value="">Seçiniz</option>
                        <?php 
                        if (isset($all_classes) && is_array($all_classes)):
                            $selected_class = $user['class_id'] ?? '';
                            foreach ($all_classes as $class): ?>
                                <option value="<?= e($class['id']) ?>" <?= ((string)$selected_class === (string)$class['id']) ? 'selected' : '' ?>>
                                    <?= e($class['name']) ?>
                                </option>
                            <?php endforeach; 
                        endif; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="profile_photo">Profil Fotoğrafı:</label>
                <input type="file" id="profile_photo" name="profile_photo">
                <?php if (!empty($user['profile_photo'])): ?>
                    <div style="margin-top: 10px; text-align: center;">
                        <img src="<?= e($user['profile_photo']) ?>" alt="Mevcut Profil Fotoğrafı" style="max-width: 150px; height: auto; border-radius: 8px; border: 1px solid #ddd; display: block; margin: 0 auto 10px;">
                        <a href="<?= e($user['profile_photo']) ?>" target="_blank" style="font-size: 0.9em; color: #007bff; text-decoration: none;">Mevcut Görseli Görüntüle</a>
                    </div>
                <?php else: ?>
                     <p style="font-size: 0.9em; color: #777; margin-top: 5px;">Mevcut fotoğraf yok.</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <button type="submit">Bilgileri Güncelle</button>
            </div>
        </form>
    </div>

    <?php if (!empty($extra_profile_data)): ?>
        <div class="profile-section role-specific-info">
            <h3>Ek Bilgiler (<?= ucfirst($userRole) ?>)</h3>

            <?php if ($userRole === 'admin'): ?>
                <h4>Sistem İstatistikleri</h4>
                <ul>
                    <li>Toplam Kullanıcı: <strong><?= e($extra_profile_data['total_users'] ?? 0) ?></strong></li>
                    <li>Toplam Öğrenci: <strong><?= e($extra_profile_data['total_students'] ?? 0) ?></strong></li>
                    <li>Toplam Öğretmen: <strong><?= e($extra_profile_data['total_teachers'] ?? 0) ?></strong></li>
                </ul>

                <h4>Onay Bekleyenler</h4>
                <?php if (!empty($extra_profile_data['pending_activities'])): ?>
                    <h5>Etkinlikler (<?= count($extra_profile_data['pending_activities']) ?>)</h5>
                    <ul>
                        <?php foreach ($extra_profile_data['pending_activities'] as $activity): ?>
                            <li>
                                <a href="index.php?module=activities&action=edit&id=<?= e($activity['id']) ?>">
                                    <?= e($activity['title']) ?> (<?= e(date('d.m.Y', strtotime($activity['activity_date']))) ?>)
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Onay bekleyen etkinlik bulunmamaktadır.</p>
                <?php endif; ?>

                <?php if (!empty($extra_profile_data['pending_course_requests'])): ?>
                    <h5>Ders Kayıt İstekleri (<?= count($extra_profile_data['pending_course_requests']) ?>)</h5>
                    <ul>
                        <?php foreach ($extra_profile_data['pending_course_requests'] as $request): ?>
                            <li>
                                <a href="index.php?module=course_requests&action=edit&id=<?= e($request['id']) ?>">
                                    <?= e($request['student_name']) ?> - <?= e($request['item_name']) ?> (<?= e($request['item_type']) ?>)
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Onay bekleyen ders kayıt isteği bulunmamaktadır.</p>
                <?php endif; ?>

            <?php elseif ($userRole === 'teacher'): ?>
                <h4>Atanmış Dersler</h4>
                <?php if (!empty($extra_profile_data['teacher_courses'])): ?>
                    <ul>
                        <?php foreach ($extra_profile_data['teacher_courses'] as $course): ?>
                            <li><a href="index.php?module=courses&action=edit&id=<?= e($course['id']) ?>"><?= e($course['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Atanmış dersiniz bulunmamaktadır.</p>
                <?php endif; ?>

                <h4>Sorumlu Olduğunuz Öğrenciler</h4>
                <?php if (!empty($extra_profile_data['teacher_students'])): ?>
                    <ul>
                        <?php foreach ($extra_profile_data['teacher_students'] as $student): ?>
                            <li><a href="index.php?module=students&action=edit&id=<?= e($student['id']) ?>"><?= e($student['name']) ?></a> (Sınıf: <?= e($student['class_name'] ?? 'N/A') ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Sorumlu olduğunuz öğrenci bulunmamaktadır.</p>
                <?php endif; ?>

            <?php elseif ($userRole === 'student'): ?>
                <p>Öğrenciye özel detaylı bilgiler (kayıtlı dersler, not dökümü özeti vb.) burada yer alabilir.</p>

            <?php elseif ($userRole === 'parent'): ?>
                <p>Veliye özel detaylı bilgiler (çocuklarının son notları, devamsızlıkları, rehberlik notları özeti vb.) burada yer alabilir.</p>

            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>