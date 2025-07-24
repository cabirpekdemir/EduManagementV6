#!/bin/bash

# Bu betik, eski view dosyası yapısını yeni yapıya taşır.
# Eski Yapı: themes/default/pages/modul_adi/index.php
# Yeni Yapı: modules/modul_adi/view/index.php

# --- Ayarlar ---
# Projenizin ana dizininde olduğunuzu varsayar.
# Gerekirse yolları kendi projenize göre düzenleyin.
OLD_VIEWS_DIR="themes/default/pages"
NEW_MODULES_DIR="modules"

# --- Betik Başlangıcı ---
echo "View dosyası taşıma işlemi başlatılıyor..."
echo "Kaynak Dizin: $OLD_VIEWS_DIR"
echo "Hedef Dizin: $NEW_MODULES_DIR"
echo "-------------------------------------------"

# Kaynak dizin var mı diye kontrol et
if [ ! -d "$OLD_VIEWS_DIR" ]; then
    echo "Hata: Kaynak dizin '$OLD_VIEWS_DIR' bulunamadı."
    echo "Lütfen betiği projenizin ana dizininde çalıştırdığınızdan emin olun."
    exit 1
fi

# Kaynak dizindeki her bir modül klasörü için döngü başlat
for module_path in "$OLD_VIEWS_DIR"/*; do
    # Sadece klasörleri işleme al
    if [ -d "$module_path" ]; then
        
        # Modül adını al (klasör adı)
        module_name=$(basename "$module_path")
        
        # Yeni hedef yolu belirle
        target_dir="$NEW_MODULES_DIR/$module_name/view"
        
        echo "İşleniyor: '$module_name' modülü"

        # Hedef dizini ve içindeki view klasörünü oluştur (eğer yoksa)
        # -p parametresi, üst dizinlerin de gerekirse oluşturulmasını sağlar.
        mkdir -p "$target_dir"
        echo "  -> Hedef dizin oluşturuldu/kontrol edildi: $target_dir"
        
        # Eski view klasöründeki tüm dosyaları yeni hedefe taşı
        # `mv` komutu, dosyaları taşır (kopyalayıp siler).
        # `2>/dev/null` olası "dosya bulunamadı" hatalarını gizler.
        mv "$module_path"/* "$target_dir" 2>/dev/null
        
        if [ $? -eq 0 ]; then
            echo "  -> Dosyalar başarıyla taşındı."
        else
            echo "  -> Bu klasörde taşınacak dosya bulunamadı veya bir hata oluştu."
        fi

        # İşlem sonrası boş kalan eski modül klasörünü sil
        rmdir "$module_path"
        echo "  -> Eski kaynak dizin silindi: $module_path"
        echo ""

    fi
done

# Artık boş olan 'pages' klasörünü de silelim
if [ -d "$OLD_VIEWS_DIR" ] && [ -z "$(ls -A $OLD_VIEWS_DIR)" ]; then
    rmdir "$OLD_VIEWS_DIR"
    echo "Ana kaynak dizin '$OLD_VIEWS_DIR' boş olduğu için silindi."
fi

echo "-------------------------------------------"
echo "Taşıma işlemi tamamlandı!"

