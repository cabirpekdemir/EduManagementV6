<?php
// core/auth.php

// Oturum kontrolü
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Mevcut kullanıcının ID'sini döndürür
 * @return int
 */
function currentUserId(): int {
    return (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
}

/**
 * Mevcut kullanıcının rolünü döndürür
 * @return string 'admin', 'teacher', 'student', 'parent', 'guest'
 */
function currentRole(): string {
    return (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? 'guest');
}

/**
 * Belirtilen rollere sahip olmayan kullanıcıları engeller
 * @param array $allowed İzin verilen roller ['admin', 'teacher']
 * @return void
 */
function requireRoles(array $allowed): void {
    $role = currentRole();
    if (!in_array($role, $allowed, true)) {
        $_SESSION['form_error'] = 'Bu alana erişim yetkiniz yok.';
        header('Location: index.php?module=dashboard');
        exit;
    }
}

/**
 * Kullanıcının giriş yapmış olup olmadığını kontrol eder
 * @return bool
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user']['id']);
}

/**
 * Kullanıcının belirli bir role sahip olup olmadığını kontrol eder
 * @param string $role
 * @return bool
 */
function hasRole(string $role): bool {
    return currentRole() === $role;
}

/**
 * Kullanıcının admin olup olmadığını kontrol eder
 * @return bool
 */
function isAdmin(): bool {
    return currentRole() === 'admin';
}

/**
 * Kullanıcının öğretmen olup olmadığını kontrol eder
 * @return bool
 */
function isTeacher(): bool {
    return currentRole() === 'teacher';
}

/**
 * Kullanıcının öğrenci olup olmadığını kontrol eder
 * @return bool
 */
function isStudent(): bool {
    return currentRole() === 'student';
}

/**
 * Kullanıcının veli olup olmadığını kontrol eder
 * @return bool
 */
function isParent(): bool {
    return currentRole() === 'parent';
}

/**
 * CSRF token oluşturur ve session'a kaydeder
 * @return string
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrulaması yapar
 * @param string|null $token Doğrulanacak token
 * @return bool
 */
function validateCsrfToken(?string $token = null): bool {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    }
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Kullanıcı bilgilerini döndürür
 * @return array|null
 */
function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Oturumu sonlandırır
 * @return void
 */
function logout(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: index.php?module=login');
    exit;
}