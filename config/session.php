<?php
/**
 * Gerenciamento de Sessões
 * FAP Pádua - Sistema de Autenticação
 */

// Configurações de segurança da sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Permitir HTTP e HTTPS
ini_set('session.cookie_samesite', 'Lax'); // Mais permissivo para AJAX

session_name('FAP_SESSION');
session_start();

// Regenerar ID da sessão periodicamente para segurança
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    // Regenerar após 30 minutos
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

class Session {
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    public static function destroy() {
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    public static function isLoggedIn() {
        return self::has('user_id') && self::has('user_type');
    }
    
    public static function isAdmin() {
        return self::isLoggedIn() && self::get('user_type') === 'admin';
    }
    
    public static function isEditor() {
        return self::isLoggedIn() && (self::get('user_type') === 'editor' || self::get('user_type') === 'admin');
    }
    
    public static function getUserId() {
        return self::get('user_id');
    }
    
    public static function getUserName() {
        return self::get('user_name');
    }
    
    public static function getUserType() {
        return self::get('user_type');
    }
    
    public static function setFlash($key, $message) {
        $_SESSION['flash'][$key] = $message;
    }
    
    public static function getFlash($key) {
        $message = $_SESSION['flash'][$key] ?? null;
        if ($message) {
            unset($_SESSION['flash'][$key]);
        }
        return $message;
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
}
