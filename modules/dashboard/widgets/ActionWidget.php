<?php
/**
 * ActionWidget - Aksiyon Butonu Widget'ı
 * Hızlı erişim butonları için
 */

class ActionWidget extends BaseWidget {
    
    /**
     * Render - Aksiyon butonu
     */
    public function render($data) {
        $buttonText = $this->config['button_text'] ?? 'Tıkla';
        $link = $this->config['link'] ?? '#';
        
        // Icon (opsiyonel)
        $iconClass = $this->icon;
        if (strpos($iconClass, 'fa-') !== false && strpos($iconClass, 'fas ') === false && strpos($iconClass, 'fa ') === false) {
            $iconClass = 'fas ' . $iconClass;
        }
        
        // Açıklama metni (opsiyonel)
        $descriptionHtml = '';
        if (!empty($this->config['description'])) {
            $descriptionHtml = "<p class='text-muted small mb-3'>{$this->config['description']}</p>";
        }
        
        return "
        <div class='col-lg-3 col-md-4 col-sm-6 col-12 mb-3'>
            <div class='card hover-shadow' data-widget-key='{$this->widgetKey}' style='height: calc(100% - 1rem);'>
                <div class='card-body text-center d-flex flex-column justify-content-center' style='min-height: 200px;'>
                    <div class='mb-3'>
                        <i class='{$iconClass} fa-3x text-{$this->color}'></i>
                    </div>
                    <h5 class='card-title mb-3'><strong>{$this->title}</strong></h5>
                    {$descriptionHtml}
                    <a href='{$link}' class='btn btn-{$this->color} btn-lg btn-block mt-auto'>
                        <i class='fas fa-plus-circle'></i> {$buttonText}
                    </a>
                </div>
            </div>
        </div>
        ";
    }
}
