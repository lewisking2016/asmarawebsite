<?php
/**
 * Authentication Class
 * Handles admin login and session management
 */

require_once __DIR__ . '/../database/UserRepository.php';
require_once __DIR__ . '/../database/Connection.php';

class Auth {
    private static $session_timeout = 1800; // 30 minutes

    /**
     * Start session
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => self::$session_timeout,
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }

    /**
     * Login user
     */
    public static function login($username, $password) {
        self::startSession();
        
        $userRepo = new UserRepository();
        
        // Check if user exists and password is correct
        if (!$userRepo->verifyPassword($username, $password)) {
            return false;
        }

        $user = $userRepo->getByUsername($username);
        
        if (!$user) {
            return false;
        }

        // Set session variables
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        // Update last login
        $userRepo->updateLastLogin($user['id']);

        // Log activity
        self::logActivity($user['id'], 'login', 'admin_users', $user['id']);

        return true;
    }

    /**
     * Logout user
     */
    public static function logout() {
        self::startSession();
        
        if (isset($_SESSION['admin_id'])) {
            $admin_id = $_SESSION['admin_id'];
            self::logActivity($admin_id, 'logout', 'admin_users', $admin_id);
        }

        session_destroy();
        return true;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        self::startSession();
        
        // Check if session exists
        if (!isset($_SESSION['admin_id'])) {
            return false;
        }

        // Check session timeout
        if (time() - $_SESSION['last_activity'] > self::$session_timeout) {
            self::logout();
            return false;
        }

        // Update last activity
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * Require login
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /asmaraadmin/login');
            exit();
        }
    }

    /**
     * Require admin role
     */
    public static function requireAdmin() {
        self::requireLogin();
        
        if ($_SESSION['admin_role'] !== 'admin') {
            header('Location: /asmaraadmin/index');
            $_SESSION['error'] = 'Unauthorized access';
            exit();
        }
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        self::startSession();
        return $_SESSION['admin_id'] ?? null;
    }

    /**
     * Get current user info
     */
    public static function getCurrentUser() {
        self::startSession();
        
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'email' => $_SESSION['admin_email'],
            'role' => $_SESSION['admin_role']
        ];
    }

    /**
     * Is admin
     */
    public static function isAdmin() {
        self::startSession();
        return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
    }

    /**
     * Log activity
     */
    public static function logActivity($user_id, $action, $table_name, $record_id, $changes = null) {
        try {
            $db = Database::getInstance();
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $sql = "INSERT INTO activity_log (user_id, action, table_name, record_id, changes, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $db->query($sql, [
                $user_id,
                $action,
                $table_name,
                $record_id,
                $changes ? json_encode($changes) : null,
                $ip_address,
                substr($user_agent, 0, 255)
            ]);
        } catch (Exception $e) {
            error_log('Activity logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        self::startSession();
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        self::startSession();
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Get CSRF token
     */
    public static function getCSRFToken() {
        self::startSession();
        return $_SESSION['csrf_token'] ?? '';
    }
}
