<?php
/**
 * StatWidget - İstatistik Widget'ı
 * Sayı ve metrik gösterimi için
 */

class StatWidget extends BaseWidget {
    
    /**
     * Render - İstatistik kartı
     */
    public function render($data) {
        $value = $this->getValue($data);
        $formattedValue = $this->formatNumber($value);
        
        // Trend gösterimi (opsiyonel)
        $trendHtml = '';
        if (!empty($this->config['show_trend']) && isset($data['trend'])) {
            $trend = $data['trend'];
            $trendIcon = $trend > 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
            $trendColor = $trend > 0 ? 'success' : 'danger';
            $trendHtml = "<div class='mt-2'><span class='badge badge-{$trendColor}'><i class='{$trendIcon}'></i> {$trend}%</span></div>";
        }
        
        // Yüzde gösterimi (opsiyonel)
        $percentageHtml = '';
        if (!empty($this->config['show_percentage']) && isset($data['percentage'])) {
            $percentage = $data['percentage'];
            $percentageHtml = "
            <div class='progress mt-3' style='height: 8px;'>
                <div class='progress-bar bg-{$this->color}' role='progressbar' 
                     style='width: {$percentage}%' aria-valuenow='{$percentage}' 
                     aria-valuemin='0' aria-valuemax='100'></div>
            </div>
            <small class='text-muted d-block mt-1'>{$percentage}%</small>";
        }
        
        return $this->renderCardStart() . "
            <div class='text-center py-2'>
                <h1 class='display-4 text-{$this->color} mb-0'><strong>{$formattedValue}</strong></h1>
                {$trendHtml}
                {$percentageHtml}
            </div>
        " . $this->renderCardEnd();
    }
    
    /**
     * Data'dan değeri çıkar
     */
    private function getValue($data) {
        // Data source'a göre hangi key'i kullanacağımızı belirle
        $valueKeys = [
            'students' => 'total_students',
            'teachers' => 'total_teachers',
            'courses' => 'total_courses',
            'attendance' => 'today_total',
            'grades' => 'average_grade'
        ];
        
        // Config'den data source al
        $dataSource = $this->config['data_source'] ?? null;
        $valueKey = $valueKeys[$dataSource] ?? 'value';
        
        // Önce spesifik key'i dene
        if (isset($data[$valueKey])) {
            return $data[$valueKey];
        }
        
        // Yoksa genel 'value' key'ini dene
        if (isset($data['value'])) {
            return $data['value'];
        }
        
        // Hiçbiri yoksa 0
        return 0;
    }
}
