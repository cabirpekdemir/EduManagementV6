/**
 * MODERN EDU MANAGEMENT - JavaScript
 * Animasyonlar, Dark Mode, Smooth Effects
 */

(function($) {
    'use strict';

    // ============================================
    // SAYFA YÜKLEME ANİMASYONU
    // ============================================
    $(window).on('load', function() {
        $('body').addClass('loaded');
        
        // Kartları sırayla göster
        $('.card').each(function(index) {
            $(this).css({
                'animation-delay': (index * 0.1) + 's'
            });
        });
    });

    // ============================================
    // DARK MODE TOGGLE
    // ============================================
    
    // Dark mode durumunu kontrol et
    const darkMode = localStorage.getItem('darkMode');
    
    if (darkMode === 'enabled') {
        $('body').addClass('dark-mode');
    }

    // Dark mode toggle butonu oluştur
    if ($('#darkModeToggle').length === 0) {
        $('body').append(`
            <button id="darkModeToggle" title="Karanlık/Aydınlık Mod">
                <i class="fa fa-moon-o"></i>
            </button>
        `);
    }

    // Dark mode toggle
    $(document).on('click', '#darkModeToggle', function() {
        $('body').toggleClass('dark-mode');
        
        const isDark = $('body').hasClass('dark-mode');
        
        if (isDark) {
            localStorage.setItem('darkMode', 'enabled');
            $(this).html('<i class="fa fa-sun-o"></i>');
        } else {
            localStorage.removeItem('darkMode');
            $(this).html('<i class="fa fa-moon-o"></i>');
        }
        
        // Animasyon
        $(this).addClass('rotate-animation');
        setTimeout(() => {
            $(this).removeClass('rotate-animation');
        }, 300);
    });

    // İkon güncelle (sayfa yüklendiğinde)
    if ($('body').hasClass('dark-mode')) {
        $('#darkModeToggle').html('<i class="fa fa-sun-o"></i>');
    }

    // ============================================
    // SMOOTH SCROLL
    // ============================================
    
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 800, 'swing');
        }
    });

    // ============================================
    // SIDEBAR ACCORDION - GELİŞMİŞ
    // ============================================
    
    // Treeview menü açma/kapama
    $('.sidebar-menu .treeview > a').on('click', function(e) {
        e.preventDefault();
        
        const $parent = $(this).parent();
        const $siblings = $parent.siblings('.treeview');
        
        // Kardeşleri kapat
        $siblings.removeClass('menu-open');
        $siblings.find('.treeview-menu').slideUp(300);
        
        // Kendini aç/kapat
        $parent.toggleClass('menu-open');
        $parent.find('> .treeview-menu').slideToggle(300);
        
        // LocalStorage'a kaydet
        saveSidebarState();
    });

    // Sidebar durumunu kaydet
    function saveSidebarState() {
        const openMenus = [];
        $('.sidebar-menu .treeview.menu-open').each(function() {
            const menuId = $(this).data('menu-id') || $(this).index();
            openMenus.push(menuId);
        });
        localStorage.setItem('sidebarState', JSON.stringify(openMenus));
    }

    // Sidebar durumunu yükle
    function loadSidebarState() {
        const savedState = localStorage.getItem('sidebarState');
        if (savedState) {
            const openMenus = JSON.parse(savedState);
            openMenus.forEach(function(menuId) {
                const $menu = $('.sidebar-menu .treeview').eq(menuId);
                $menu.addClass('menu-open');
                $menu.find('> .treeview-menu').show();
            });
        }
    }

    // Sayfa yüklendiğinde durumu yükle
    loadSidebarState();

    // ============================================
    // KART HOVER EFEKTLERİ
    // ============================================
    
    $('.card').hover(
        function() {
            $(this).addClass('card-hover');
        },
        function() {
            $(this).removeClass('card-hover');
        }
    );

    // ============================================
    // TABLO SATIRLARI - ANIMASYON
    // ============================================
    
    $('.table tbody tr').each(function(index) {
        $(this).css({
            'animation': 'fadeInUp 0.5s ease-out ' + (index * 0.05) + 's both'
        });
    });

    // ============================================
    // FORM VALİDASYONU - GÖRSELLEŞTİRME
    // ============================================
    
    $('form').on('submit', function() {
        $(this).find('.btn[type="submit"]').addClass('btn-loading');
    });

    // Input focus animasyonu
    $('.form-control, .form-select').on('focus', function() {
        $(this).parent().addClass('input-focused');
    }).on('blur', function() {
        $(this).parent().removeClass('input-focused');
    });

    // ============================================
    // BİLDİRİM SİSTEMİ
    // ============================================
    
    let notificationCount = 0;

    function updateNotificationCount() {
        $('.notification-count').text(notificationCount);
        
        if (notificationCount > 0) {
            $('.notification-count').show();
        } else {
            $('.notification-count').hide();
        }
    }

    // Bildirim zili tıklama
    $('#notificationsBell').on('click', function() {
        // Bildirim panelini aç/kapat
        if ($('#notificationPanel').length === 0) {
            createNotificationPanel();
        } else {
            $('#notificationPanel').toggle();
        }
    });

    function createNotificationPanel() {
        const panel = `
            <div id="notificationPanel" style="
                position: fixed;
                top: 60px;
                right: 20px;
                width: 320px;
                max-height: 400px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.15);
                z-index: 9999;
                overflow: hidden;
            ">
                <div style="padding: 1rem; border-bottom: 1px solid #e0e6ed;">
                    <h6 style="margin: 0;">Bildirimler</h6>
                </div>
                <div style="padding: 1rem; text-align: center; color: #6c757d;">
                    <i class="fa fa-bell-o" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                    <p>Henüz bildiriminiz yok</p>
                </div>
            </div>
        `;
        
        $('body').append(panel);
    }

    // Dışarı tıklayınca paneli kapat
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#notificationsBell, #notificationPanel').length) {
            $('#notificationPanel').hide();
        }
    });

    // ============================================
    // LOADING OVERLAY
    // ============================================
    
    window.showLoading = function(message = 'Yükleniyor...') {
        if ($('#loadingOverlay').length === 0) {
            $('body').append(`
                <div id="loadingOverlay" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 99999;
                ">
                    <div style="
                        background: white;
                        padding: 2rem;
                        border-radius: 12px;
                        text-align: center;
                        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
                    ">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p style="margin-top: 1rem; margin-bottom: 0;">${message}</p>
                    </div>
                </div>
            `);
        }
    };

    window.hideLoading = function() {
        $('#loadingOverlay').fadeOut(300, function() {
            $(this).remove();
        });
    };

    // ============================================
    // TOOLTIP AKTİFLEŞTİRME
    // ============================================
    
    if (typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // ============================================
    // BACK TO TOP BUTTON
    // ============================================
    
    if ($('#backToTop').length === 0) {
        $('body').append(`
            <button id="backToTop" style="
                position: fixed;
                bottom: 80px;
                right: 20px;
                width: 45px;
                height: 45px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                cursor: pointer;
                display: none;
                z-index: 9998;
                transition: all 0.3s;
            ">
                <i class="fa fa-arrow-up"></i>
            </button>
        `);
    }

    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#backToTop').fadeIn();
        } else {
            $('#backToTop').fadeOut();
        }
    });

    $('#backToTop').on('click', function() {
        $('html, body').animate({ scrollTop: 0 }, 800);
    });

    // ============================================
    // SAYFA GEÇİŞ ANİMASYONU
    // ============================================
    
    $(document).on('click', 'a:not([target="_blank"])', function(e) {
        const href = $(this).attr('href');
        
        // Harici linkler veya # ile başlayanlar hariç
        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
            // Sayfayı kaydet
            if (href.startsWith('index.php') || href.startsWith('/')) {
                e.preventDefault();
                $('body').addClass('page-transition');
                
                setTimeout(() => {
                    window.location.href = href;
                }, 300);
            }
        }
    });

    // ============================================
    // CONSOLE LOGO (BONUS)
    // ============================================
    
    console.log('%c🎓 EduManagement System', 'color: #667eea; font-size: 20px; font-weight: bold;');
    console.log('%cModern UI Paketi Aktif', 'color: #764ba2; font-size: 12px;');

})(jQuery);

// ============================================
// EK