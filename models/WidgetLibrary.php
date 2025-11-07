<?php
/**
 * =====================================================
 * WidgetLibrary.php - GÜNCELLENMIŞ getAvailableWidgets()
 * (name kolonu kaldırıldı)
 * =====================================================
 * 
 * Bu fonksiyonu mevcut WidgetLibrary.php dosyanızdaki
 * getAvailableWidgets() fonksiyonu ile değiştirin
 */

public static function getAvailableWidgets() {
    return [
        // =====================================================
        // ADMIN WIDGET'LARI
        // =====================================================
        'total_students' => [
            'title' => 'Toplam Öğrenci',
            'description' => 'Sistemdeki aktif öğrenci sayısını gösterir',
            'type' => 'stat',
            'class' => 'TotalStudentsWidget',
            'icon' => 'fas fa-user-graduate',
            'color' => 'primary',
            'roles' => ['admin']
        ],
        
        'total_teachers' => [
            'title' => 'Toplam Öğretmen',
            'description' => 'Sistemdeki aktif öğretmen sayısını gösterir',
            'type' => 'stat',
            'class' => 'TotalTeachersWidget',
            'icon' => 'fas fa-chalkboard-teacher',
            'color' => 'success',
            'roles' => ['admin']
        ],
        
        'total_parents' => [
            'title' => 'Toplam Veli',
            'description' => 'Sistemdeki aktif veli sayısını gösterir',
            'type' => 'stat',
            'class' => 'TotalParentsWidget',
            'icon' => 'fas fa-users',
            'color' => 'info',
            'roles' => ['admin']
        ],
        
        'attendance_today' => [
            'title' => 'Bugünkü Yoklama',
            'description' => 'Bugün alınan yoklama sayısını gösterir',
            'type' => 'stat',
            'class' => 'AttendanceTodayWidget',
            'icon' => 'fas fa-clipboard-check',
            'color' => 'warning',
            'roles' => ['admin', 'teacher']
        ],
        
        'active_courses' => [
            'title' => 'Aktif Dersler',
            'description' => 'Sistemdeki aktif ders sayısını gösterir',
            'type' => 'stat',
            'class' => 'ActiveCoursesWidget',
            'icon' => 'fas fa-book-open',
            'color' => 'danger',
            'roles' => ['admin']
        ],
        
        'new_registrations' => [
            'title' => 'Yeni Kayıtlar (Bu Ay)',
            'description' => 'Bu ay yapılan yeni kayıt sayısını gösterir',
            'type' => 'stat',
            'class' => 'NewRegistrationsWidget',
            'icon' => 'fas fa-user-plus',
            'color' => 'teal',
            'roles' => ['admin']
        ],
        
        'system_status' => [
            'title' => 'Sistem Durumu',
            'description' => 'Sistem bilgilerine erişim sağlar',
            'type' => 'action',
            'class' => 'SystemStatusWidget',
            'icon' => 'fas fa-server',
            'color' => 'secondary',
            'roles' => ['admin']
        ],
        
        // =====================================================
        // ÖĞRETMEN WIDGET'LARI
        // =====================================================
        'my_students' => [
            'title' => 'Benim Öğrencilerim',
            'description' => 'Derslerinize kayıtlı öğrenci sayısını gösterir',
            'type' => 'stat',
            'class' => 'MyStudentsWidget',
            'icon' => 'fas fa-user-graduate',
            'color' => 'primary',
            'roles' => ['teacher']
        ],
        
        'today_classes' => [
            'title' => 'Bugünkü Derslerim',
            'description' => 'Bugünkü ders programınızı gösterir',
            'type' => 'list',
            'class' => 'TodayClassesWidget',
            'icon' => 'fas fa-calendar-day',
            'color' => 'success',
            'roles' => ['teacher']
        ],
        
        'pending_attendance' => [
            'title' => 'Tamamlanmamış Yoklamalar',
            'description' => 'Alınması gereken yoklama sayısını gösterir',
            'type' => 'stat',
            'class' => 'PendingAttendanceWidget',
            'icon' => 'fas fa-clipboard-list',
            'color' => 'warning',
            'roles' => ['teacher']
        ],
        
        'pending_exams' => [
            'title' => 'Notlandırılacak Sınavlar',
            'description' => 'Notlandırılmayı bekleyen sınav sayısını gösterir',
            'type' => 'stat',
            'class' => 'PendingExamsWidget',
            'icon' => 'fas fa-file-alt',
            'color' => 'danger',
            'roles' => ['teacher']
        ],
        
        'pending_tasks' => [
            'title' => 'Bekleyen Görevler',
            'description' => 'Görev yönetimine erişim sağlar',
            'type' => 'action',
            'class' => 'PendingTasksWidget',
            'icon' => 'fas fa-tasks',
            'color' => 'info',
            'roles' => ['teacher']
        ],
        
        // =====================================================
        // ÖĞRENCİ WIDGET'LARI
        // =====================================================
        'my_grades' => [
            'title' => 'Notlarım',
            'description' => 'Son aldığınız notları gösterir',
            'type' => 'list',
            'class' => 'MyGradesWidget',
            'icon' => 'fas fa-star',
            'color' => 'primary',
            'roles' => ['student']
        ],
        
        'my_attendance' => [
            'title' => 'Devamsızlık Durumum',
            'description' => 'Toplam devamsızlık sayınızı gösterir',
            'type' => 'stat',
            'class' => 'MyAttendanceWidget',
            'icon' => 'fas fa-user-check',
            'color' => 'warning',
            'roles' => ['student']
        ],
        
        'upcoming_exams' => [
            'title' => 'Yaklaşan Sınavlar',
            'description' => 'Gelecek sınavlarınızı gösterir',
            'type' => 'list',
            'class' => 'UpcomingExamsWidget',
            'icon' => 'fas fa-graduation-cap',
            'color' => 'danger',
            'roles' => ['student']
        ],
        
        'my_homework' => [
            'title' => 'Ödevlerim',
            'description' => 'Bekleyen ödev sayınızı gösterir',
            'type' => 'stat',
            'class' => 'MyHomeworkWidget',
            'icon' => 'fas fa-book',
            'color' => 'info',
            'roles' => ['student']
        ],
        
        'my_schedule' => [
            'title' => 'Ders Programım',
            'description' => 'Ders programınıza erişim sağlar',
            'type' => 'action',
            'class' => 'MyScheduleWidget',
            'icon' => 'fas fa-calendar-alt',
            'color' => 'success',
            'roles' => ['student']
        ],
        
        // =====================================================
        // VELİ WIDGET'LARI
        // =====================================================
        'child_grades' => [
            'title' => 'Çocuğumun Notları',
            'description' => 'Çocuğunuzun son notlarını gösterir',
            'type' => 'list',
            'class' => 'ChildGradesWidget',
            'icon' => 'fas fa-star',
            'color' => 'primary',
            'roles' => ['parent']
        ],
        
        'child_attendance' => [
            'title' => 'Devamsızlık Durumu',
            'description' => 'Çocuğunuzun devamsızlık sayısını gösterir',
            'type' => 'stat',
            'class' => 'ChildAttendanceWidget',
            'icon' => 'fas fa-user-check',
            'color' => 'warning',
            'roles' => ['parent']
        ],
        
        'teacher_messages' => [
            'title' => 'Öğretmen Mesajları',
            'description' => 'Okunmamış mesaj sayısını gösterir',
            'type' => 'stat',
            'class' => 'TeacherMessagesWidget',
            'icon' => 'fas fa-envelope',
            'color' => 'info',
            'roles' => ['parent']
        ],
    ];
}

?>
