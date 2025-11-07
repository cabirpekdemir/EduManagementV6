<?php
// modules/view_as/view_ascontroller.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/view_as_helper.php';

class View_asController {
    
    private $db;
    
    public function __construct() {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: index.php?module=login');
            exit;
        }
        
        // Sadece admin erişebilir
        $user = $_SESSION['original_user'] ?? $_SESSION['user'];
        if ($user['role'] !== 'admin') {
            $_SESSION['flash_error'] = 'Bu işlem için yetkiniz yok!';
            header('Location: index.php?module=dashboard');
            exit;
        }
        
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Kullanıcı olarak görüntülemeyi başlat
     */
    public function start() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=dashboard');
            exit;
        }
        
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        
        if ($targetUserId > 0) {
            if (ViewAsHelper::startViewAs($targetUserId)) {
                $userName = ViewAsHelper::getViewAsUserName();
                $userRole = ViewAsHelper::getViewAsUserRole();
                $_SESSION['flash_success'] = "Artık <strong>{$userName}</strong> ({$userRole}) olarak görüntülüyorsunuz.";
            } else {
                $_SESSION['flash_error'] = 'Kullanıcı bulunamadı veya erişim hatası!';
            }
        } else {
            $_SESSION['flash_error'] = 'Geçersiz kullanıcı ID!';
        }
        
        header('Location: index.php?module=dashboard');
        exit;
    }
    
    /**
     * Normal görünüme dön
     */
    public function exit() {
        $viewingAsName = ViewAsHelper::getViewAsUserName();
        
        if (ViewAsHelper::exitViewAs()) {
            $_SESSION['flash_success'] = "Normal görünüme döndünüz. (Önceki: {$viewingAsName})";
        } else {
            $_SESSION['flash_error'] = 'View As modunda değilsiniz!';
        }
        
        header('Location: index.php?module=dashboard');
        exit;
    }
    
    /**
     * Index - Varsayılan action
     */
    public function index() {
        header('Location: index.php?module=dashboard');
        exit;
    }
}