<?php
/**
 * BaseWidget - Tüm Widget'ların Base Class'ı
 * Abstract class - Direkt kullanılamaz, extend edilmeli
 */

abstract class BaseWidget {
    
    protected $db;
    protected $config;
    protected $widgetKey;
    protected $title;
    protected $icon;
    protected $color;
    
    /**
     * Constructor
     */
    public function __construct($db, $widgetConfig) {
        $this->db = $db;
        $this->widgetKey = $widgetConfig['widget_key'];
        $this->title = $widgetConfig['title'];
        $this->icon = $widgetConfig['icon'] ?? 'fa-square';
        $this->color = $widgetConfig['color'] ?? 'primary';
        
        // Config JSON'ı parse et
        $this->config = json_decode($widgetConfig['config_json'] ?? '{}', true);
    }
    
    /**
     * Render - Her widget kendi render metodunu implement etmeli
     */
    abstract public function render($data);
    
    /**
     * Card başlangıç HTML'i
     */
    protected function renderCardStart($extraClass = '') {
        // Icon için fas veya fa class'ını kontrol et
        $iconClass = $this->icon;
        if (strpos($iconClass, 'fa-') !== false && strpos($iconClass, 'fas ') === false && strpos($iconClass, 'fa ') === false) {
            $iconClass = 'fas ' . $iconClass;
        }
        
        return "
        <div class='col-lg-3 col-md-4 col-sm-6 col-12 mb-3'>
            <div class='card {$extraClass}' data-widget-key='{$this->widgetKey}' style='height: calc(100% - 1rem);'>
                <div class='card-header bg-{$this->color} text-white'>
                    <h3 class='card-title'>
                        <i class='{$iconClass}'></i> {$this->title}
                    </h3>
                </div>
                <div class='card-body'>
        ";
    }
    
    /**
     * Card bitiş HTML'i
     */
    protected function renderCardEnd() {
        $link = $this->config['link'] ?? '#';
        
        return "
                </div>
                <div class='card-footer bg-light'>
                    <a href='{$link}' class='btn btn-sm btn-{$this->color} btn-block'>
                        Detaylar <i class='fas fa-arrow-right'></i>
                    </a>
                </div>
            </div>
        </div>
        ";
    }
    
    /**
     * Sayı formatla
     */
    protected function formatNumber($number) {
        if ($number >= 1000) {
            return number_format($number / 1000, 1) . 'K';
        }
        return number_format($number);
    }
    // models/BaseWidget.php - class içine ekle

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
}
