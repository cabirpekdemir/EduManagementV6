<?php
/**
 * =====================================================
 * EĞİTİM YÖNETİM SİSTEMİ - WIDGET SINIF KÜTÜPHANESİ
 * 20 Widget Sınıfı
 * =====================================================
 * 
 * KURULUM:
 * Bu dosyadaki her sınıfı ayrı dosya olarak kaydedin:
 * models/TotalStudentsWidget.php
 * models/TotalTeachersWidget.php
 * ... vs
 * 
 * VEYA:
 * Bu dosyayı models/AllWidgets.php olarak kaydedin
 * ve ihtiyacınız olan yerde require_once edin
 */

require_once 'BaseWidget.php';

// =====================================================
// ADMIN WIDGET'LARI
// =====================================================

/**
 * 1. Toplam Öğrenci Widget
 */
class TotalStudentsWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) as count FROM students WHERE status=1";
        $stmt = $db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Toplam Öğrenci',
            'fas fa-user-graduate',
            'primary',
            '?page=students'
        );
    }
}

/**
 * 2. Toplam Öğretmen Widget
 */
class TotalTeachersWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) as count FROM teachers WHERE status=1";
        $stmt = $db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Toplam Öğretmen',
            'fas fa-chalkboard-teacher',
            'success',
            '?page=teachers'
        );
    }
}

/**
 * 3. Toplam Veli Widget
 */
class TotalParentsWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) as count FROM parents WHERE status=1";
        $stmt = $db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Toplam Veli',
            'fas fa-users',
            'info',
            '?page=parents'
        );
    }
}

/**
 * 4. Bugünkü Yoklama Widget
 */
class AttendanceTodayWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) as count FROM attendance WHERE DATE(created_at) = CURDATE()";
        $stmt = $db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Bugünkü Yoklama',
            'fas fa-clipboard-check',
            'warning',
            '?page=attendance'
        );
    }
}

/**
 * 5. Aktif Dersler Widget
 */
class ActiveCoursesWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) as count FROM courses WHERE status=1";
        $stmt = $db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Aktif Dersler',
            'fas fa-book-open',
            'danger',
            '?page=courses'
        );
    }
}

/**
 * 6. Yeni Kayıtlar Widget
 */
class NewRegistrationsWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) as count FROM students 
                  WHERE MONTH(created_at) = MONTH(CURDATE()) 
                  AND YEAR(created_at) = YEAR(CURDATE())";
        $stmt = $db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Yeni Kayıtlar (Bu Ay)',
            'fas fa-user-plus',
            'teal',
            '?page=students&filter=new'
        );
    }
}

/**
 * 7. Sistem Durumu Widget
 */
class SystemStatusWidget extends BaseWidget {
    public function render() {
        return $this->renderActionBox(
            'Sistem Durumu',
            'Sistem Bilgileri',
            '?page=system',
            'fas fa-server',
            'secondary'
        );
    }
}

// =====================================================
// ÖĞRETMEN WIDGET'LARI
// =====================================================

/**
 * 8. Benim Öğrencilerim Widget
 */
class MyStudentsWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT COUNT(DISTINCT s.id) as count 
                  FROM students s 
                  JOIN enrollments e ON s.id = e.student_id 
                  JOIN courses c ON e.course_id = c.id 
                  WHERE c.teacher_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Benim Öğrencilerim',
            'fas fa-user-graduate',
            'primary',
            '?page=my-students'
        );
    }
}

/**
 * 9. Bugünkü Derslerim Widget
 */
class TodayClassesWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT c.name as title, s.start_time as info 
                  FROM schedules s 
                  JOIN courses c ON s.course_id = c.id 
                  WHERE s.teacher_id = ? 
                  AND s.day_of_week = DAYOFWEEK(CURDATE()) 
                  ORDER BY s.start_time 
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->renderListBox(
            'Bugünkü Derslerim',
            $items,
            'fas fa-calendar-day',
            'success',
            '?page=schedule'
        );
    }
}

/**
 * 10. Tamamlanmamış Yoklamalar Widget
 */
class PendingAttendanceWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT COUNT(*) as count 
                  FROM schedules s 
                  WHERE s.teacher_id = ? 
                  AND DATE(s.class_date) <= CURDATE() 
                  AND NOT EXISTS (
                      SELECT 1 FROM attendance a WHERE a.schedule_id = s.id
                  )";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Tamamlanmamış Yoklamalar',
            'fas fa-clipboard-list',
            'warning',
            '?page=attendance'
        );
    }
}

/**
 * 11. Notlandırılacak Sınavlar Widget
 */
class PendingExamsWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT COUNT(*) as count 
                  FROM exams e 
                  JOIN courses c ON e.course_id = c.id 
                  WHERE c.teacher_id = ? 
                  AND e.graded = 0";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Notlandırılacak Sınavlar',
            'fas fa-file-alt',
            'danger',
            '?page=exams'
        );
    }
}

/**
 * 12. Bekleyen Görevler Widget
 */
class PendingTasksWidget extends BaseWidget {
    public function render() {
        return $this->renderActionBox(
            'Bekleyen Görevler',
            'Görevleri Görüntüle',
            '?page=tasks',
            'fas fa-tasks',
            'info'
        );
    }
}

// =====================================================
// ÖĞRENCİ WIDGET'LARI
// =====================================================

/**
 * 13. Notlarım Widget
 */
class MyGradesWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT c.name as title, CONCAT(g.grade, '/100') as info 
                  FROM grades g 
                  JOIN courses c ON g.course_id = c.id 
                  WHERE g.student_id = ? 
                  ORDER BY g.created_at DESC 
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->renderListBox(
            'Notlarım',
            $items,
            'fas fa-star',
            'primary',
            '?page=grades'
        );
    }
}

/**
 * 14. Devamsızlık Durumum Widget
 */
class MyAttendanceWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT COUNT(*) as count 
                  FROM attendance 
                  WHERE student_id = ? 
                  AND status = 'absent'";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Devamsızlık Durumum',
            'fas fa-user-check',
            'warning',
            '?page=my-attendance'
        );
    }
}

/**
 * 15. Yaklaşan Sınavlar Widget
 */
class UpcomingExamsWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT c.name as title, 
                         DATE_FORMAT(e.exam_date, '%d.%m.%Y') as info 
                  FROM exams e 
                  JOIN courses c ON e.course_id = c.id 
                  JOIN enrollments en ON c.id = en.course_id 
                  WHERE en.student_id = ? 
                  AND e.exam_date >= CURDATE() 
                  ORDER BY e.exam_date 
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->renderListBox(
            'Yaklaşan Sınavlar',
            $items,
            'fas fa-graduation-cap',
            'danger',
            '?page=exams'
        );
    }
}

/**
 * 16. Ödevlerim Widget
 */
class MyHomeworkWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT COUNT(*) as count 
                  FROM homework h 
                  JOIN enrollments e ON h.course_id = e.course_id 
                  WHERE e.student_id = ? 
                  AND h.due_date >= CURDATE() 
                  AND h.status = 'pending'";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Ödevlerim',
            'fas fa-book',
            'info',
            '?page=homework'
        );
    }
}

/**
 * 17. Ders Programım Widget
 */
class MyScheduleWidget extends BaseWidget {
    public function render() {
        return $this->renderActionBox(
            'Ders Programım',
            'Program Görüntüle',
            '?page=schedule',
            'fas fa-calendar-alt',
            'success'
        );
    }
}

// =====================================================
// VELİ WIDGET'LARI
// =====================================================

/**
 * 18. Çocuğumun Notları Widget
 */
class ChildGradesWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT c.name as title, CONCAT(g.grade, '/100') as info 
                  FROM grades g 
                  JOIN courses c ON g.course_id = c.id 
                  JOIN students s ON g.student_id = s.id 
                  WHERE s.parent_id = ? 
                  ORDER BY g.created_at DESC 
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->renderListBox(
            'Çocuğumun Notları',
            $items,
            'fas fa-star',
            'primary',
            '?page=child-grades'
        );
    }
}

/**
 * 19. Devamsızlık Durumu Widget
 */
class ChildAttendanceWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT COUNT(*) as count 
                  FROM attendance a 
                  JOIN students s ON a.student_id = s.id 
                  WHERE s.parent_id = ? 
                  AND a.status = 'absent'";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Devamsızlık Durumu',
            'fas fa-user-check',
            'warning',
            '?page=child-attendance'
        );
    }
}

/**
 * 20. Öğretmen Mesajları Widget
 */
class TeacherMessagesWidget extends BaseWidget {
    public function render() {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $query = "SELECT COUNT(*) as count 
                  FROM messages m 
                  JOIN students s ON m.student_id = s.id 
                  WHERE s.parent_id = ? 
                  AND m.is_read = 0";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->renderStatBox(
            $result['count'],
            'Öğretmen Mesajları',
            'fas fa-envelope',
            'info',
            '?page=messages'
        );
    }
}

// =====================================================
// YARDIMCI RENDER FONKSİYONLARI (BaseWidget'a ekle)
// =====================================================

/**
 * NOT: Aşağıdaki fonksiyonları BaseWidget.php dosyanıza ekleyin
 */

/*
protected function renderStatBox($value, $title, $icon, $color, $link) {
    $baseUrl = dirname($_SERVER['PHP_SELF']);
    $html = '<div class="small-box bg-' . $color . '">';
    $html .= '<div class="inner">';
    $html .= '<h3>' . $value . '</h3>';
    $html .= '<p>' . htmlspecialchars($title) . '</p>';
    $html .= '</div>';
    $html .= '<div class="icon">';
    $html .= '<i class="' . $icon . '"></i>';
    $html .= '</div>';
    $html .= '<a href="' . $baseUrl . $link . '" class="small-box-footer">';
    $html .= 'Detaylar <i class="fas fa-arrow-circle-right"></i>';
    $html .= '</a>';
    $html .= '</div>';
    return $html;
}

protected function renderListBox($title, $items, $icon, $color, $link) {
    $baseUrl = dirname($_SERVER['PHP_SELF']);
    $html = '<div class="card card-' . $color . '">';
    $html .= '<div class="card-header">';
    $html .= '<h3 class="card-title"><i class="' . $icon . '"></i> ' . htmlspecialchars($title) . '</h3>';
    $html .= '</div>';
    $html .= '<div class="card-body p-0">';
    $html .= '<ul class="list-group list-group-flush">';
    
    if (empty($items)) {
        $html .= '<li class="list-group-item">Kayıt bulunamadı</li>';
    } else {
        foreach ($items as $item) {
            $html .= '<li class="list-group-item">';
            $html .= '<strong>' . htmlspecialchars($item['title']) . '</strong>';
            if (!empty($item['info'])) {
                $html .= '<br><small class="text-muted">' . htmlspecialchars($item['info']) . '</small>';
            }
            $html .= '</li>';
        }
    }
    
    $html .= '</ul>';
    $html .= '</div>';
    $html .= '<div class="card-footer">';
    $html .= '<a href="' . $baseUrl . $link . '" class="btn btn-sm btn-' . $color . '">Tümünü Gör</a>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

protected function renderActionBox($title, $buttonText, $link, $icon, $color) {
    $baseUrl = dirname($_SERVER['PHP_SELF']);
    $html = '<div class="card card-' . $color . '">';
    $html .= '<div class="card-body text-center">';
    $html .= '<i class="' . $icon . ' fa-3x mb-3"></i>';
    $html .= '<h5>' . htmlspecialchars($title) . '</h5>';
    $html .= '<a href="' . $baseUrl . $link . '" class="btn btn-' . $color . '">';
    $html .= $buttonText;
    $html .= '</a>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
*/
