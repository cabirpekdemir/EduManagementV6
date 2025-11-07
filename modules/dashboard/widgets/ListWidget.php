<?php
/**
 * ListWidget - Liste Widget'ı
 * Duyurular, aktiviteler, son işlemler vs. için
 */

class ListWidget extends BaseWidget {
    
    /**
     * Render - Liste kartı
     */
    public function render($data) {
        $items = $data['items'] ?? $data['announcements'] ?? [];
        $limit = $this->config['limit'] ?? 5;
        
        // Icon için fas class'ını ekle
        $iconClass = $this->icon;
        if (strpos($iconClass, 'fa-') !== false && strpos($iconClass, 'fas ') === false && strpos($iconClass, 'fa ') === false) {
            $iconClass = 'fas ' . $iconClass;
        }
        
        // Liste boşsa
        if (empty($items)) {
            return "
            <div class='col-lg-6 col-md-12 mb-3'>
                <div class='card' data-widget-key='{$this->widgetKey}'>
                    <div class='card-header bg-{$this->color} text-white'>
                        <h3 class='card-title'>
                            <i class='{$iconClass}'></i> {$this->title}
                        </h3>
                    </div>
                    <div class='card-body'>
                        <div class='text-center text-muted py-4'>
                            <i class='fas fa-inbox fa-3x mb-3'></i>
                            <p>Henüz içerik bulunmuyor</p>
                        </div>
                    </div>
                </div>
            </div>
            ";
        }
        
        // İlk N öğeyi al
        $items = array_slice($items, 0, $limit);
        
        // Liste HTML'i
        $listHtml = '<ul class="list-group list-group-flush">';
        
        foreach ($items as $item) {
            $listHtml .= $this->renderListItem($item);
        }
        
        $listHtml .= '</ul>';
        
        $link = $this->config['link'] ?? '#';
        
        return "
        <div class='col-lg-6 col-md-12 mb-3'>
            <div class='card' data-widget-key='{$this->widgetKey}'>
                <div class='card-header bg-{$this->color} text-white'>
                    <h3 class='card-title'>
                        <i class='{$iconClass}'></i> {$this->title}
                    </h3>
                </div>
                <div class='card-body p-0'>
                    {$listHtml}
                </div>
                <div class='card-footer bg-light'>
                    <a href='{$link}' class='btn btn-sm btn-{$this->color} btn-block'>
                        Tümünü Gör <i class='fas fa-arrow-right'></i>
                    </a>
                </div>
            </div>
        </div>
        ";
    }
    
    /**
     * Liste öğesi render et
     */
    private function renderListItem($item) {
        // Başlık
        $title = $item['title'] ?? $item['name'] ?? $item['subject'] ?? 'Başlıksız';
        
        // Tarih (opsiyonel)
        $dateHtml = '';
        if (!empty($this->config['show_date'])) {
            $date = $item['created_at'] ?? $item['date'] ?? null;
            if ($date) {
                $formattedDate = date('d.m.Y', strtotime($date));
                $dateHtml = "<small class='text-muted float-right'><i class='fas fa-calendar-alt'></i> {$formattedDate}</small>";
            }
        }
        
        // Açıklama (opsiyonel)
        $descriptionHtml = '';
        if (!empty($this->config['show_description'])) {
            $description = $item['description'] ?? $item['content'] ?? null;
            if ($description) {
                $shortDesc = mb_substr(strip_tags($description), 0, 100) . '...';
                $descriptionHtml = "<div class='text-muted small mt-1'>{$shortDesc}</div>";
            }
        }
        
        // Link
        $link = $item['link'] ?? '#';
        
        return "
        <li class='list-group-item'>
            <div class='d-flex w-100 justify-content-between align-items-start'>
                <div class='flex-grow-1'>
                    <a href='{$link}' class='text-dark'>
                        <strong>{$title}</strong>
                    </a>
                    {$descriptionHtml}
                </div>
                {$dateHtml}
            </div>
        </li>
        ";
    }
}
