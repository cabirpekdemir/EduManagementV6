<?php
/**
 * =====================================================
 * BaseWidget.php'ye EKLENECEK YARDIMCI FONKSİYONLAR
 * =====================================================
 * 
 * Bu fonksiyonları BaseWidget.php dosyanızdaki
 * class BaseWidget { ... } bloğunun içine ekleyin
 */

    /**
     * İstatistik kutusu render eder (small-box)
     */
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

    /**
     * Liste kutusu render eder (card with list)
     */
    protected function renderListBox($title, $items, $icon, $color, $link) {
        $baseUrl = dirname($_SERVER['PHP_SELF']);
        
        $html = '<div class="card card-' . $color . ' card-outline">';
        $html .= '<div class="card-header">';
        $html .= '<h3 class="card-title"><i class="' . $icon . '"></i> ' . htmlspecialchars($title) . '</h3>';
        $html .= '</div>';
        $html .= '<div class="card-body p-0">';
        $html .= '<ul class="list-group list-group-flush">';
        
        if (empty($items)) {
            $html .= '<li class="list-group-item text-center text-muted py-3">';
            $html .= '<i class="fas fa-info-circle"></i> Kayıt bulunamadı';
            $html .= '</li>';
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
        $html .= '<div class="card-footer text-center">';
        $html .= '<a href="' . $baseUrl . $link . '" class="btn btn-sm btn-' . $color . '">';
        $html .= '<i class="fas fa-list"></i> Tümünü Gör';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Aksiyon kutusu render eder (card with button)
     */
    protected function renderActionBox($title, $buttonText, $link, $icon, $color) {
        $baseUrl = dirname($_SERVER['PHP_SELF']);
        
        $html = '<div class="card card-' . $color . ' card-outline">';
        $html .= '<div class="card-body text-center py-4">';
        $html .= '<i class="' . $icon . ' fa-3x mb-3 text-' . $color . '"></i>';
        $html .= '<h5 class="mb-3">' . htmlspecialchars($title) . '</h5>';
        $html .= '<a href="' . $baseUrl . $link . '" class="btn btn-' . $color . '">';
        $html .= '<i class="fas fa-arrow-right"></i> ' . htmlspecialchars($buttonText);
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Grafik kutusu render eder (ChartJS için hazır)
     */
    protected function renderChartBox($title, $chartId, $icon, $color, $link = null) {
        $baseUrl = dirname($_SERVER['PHP_SELF']);
        
        $html = '<div class="card card-' . $color . ' card-outline">';
        $html .= '<div class="card-header">';
        $html .= '<h3 class="card-title"><i class="' . $icon . '"></i> ' . htmlspecialchars($title) . '</h3>';
        $html .= '</div>';
        $html .= '<div class="card-body">';
        $html .= '<canvas id="' . $chartId . '" style="height: 200px;"></canvas>';
        $html .= '</div>';
        
        if ($link) {
            $html .= '<div class="card-footer text-center">';
            $html .= '<a href="' . $baseUrl . $link . '" class="btn btn-sm btn-' . $color . '">';
            $html .= '<i class="fas fa-chart-bar"></i> Detaylı Rapor';
            $html .= '</a>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Bilgi kutusu render eder (info-box)
     */
    protected function renderInfoBox($value, $title, $icon, $color, $progress = null) {
        $html = '<div class="info-box bg-' . $color . '">';
        $html .= '<span class="info-box-icon"><i class="' . $icon . '"></i></span>';
        $html .= '<div class="info-box-content">';
        $html .= '<span class="info-box-text">' . htmlspecialchars($title) . '</span>';
        $html .= '<span class="info-box-number">' . $value . '</span>';
        
        if ($progress !== null) {
            $html .= '<div class="progress">';
            $html .= '<div class="progress-bar" style="width: ' . $progress . '%"></div>';
            $html .= '</div>';
            $html .= '<span class="progress-description">%' . $progress . ' tamamlandı</span>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

?>
