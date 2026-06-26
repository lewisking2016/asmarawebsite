<?php
/**
 * User Repository
 * Data access layer for admin users
 */

require_once __DIR__ . '/Connection.php';

class UserRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all users
     */
    public function getAll() {
        $sql = "SELECT id, username, email, role, last_login, created_at FROM admin_users ORDER BY created_at DESC";
        return $this->db->getRows($sql);
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $sql = "SELECT id, username, email, role, last_login, created_at FROM admin_users WHERE id = ?";
        return $this->db->getRow($sql, [$id]);
    }

    /**
     * Get user by username
     */
    public function getByUsername($username) {
        $sql = "SELECT id, username, password, email, role FROM admin_users WHERE username = ?";
        return $this->db->getRow($sql, [$username]);
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $sql = "SELECT id, username, email, role FROM admin_users WHERE email = ?";
        return $this->db->getRow($sql, [$email]);
    }

    /**
     * Create new user
     */
    public function create($data) {
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO admin_users (username, password, email, role) VALUES (?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['username'],
            $hashed_password,
            $data['email'],
            $data['role'] ?? 'staff'
        ]);
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $fields[] = "password = ?";
                $params[] = password_hash($value, PASSWORD_BCRYPT);
            } elseif (in_array($key, ['username', 'email', 'role'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE admin_users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $this->db->query($sql, $params);
        return true;
    }

    /**
     * Delete user
     */
    public function delete($id) {
        $sql = "DELETE FROM admin_users WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Verify password
     */
    public function verifyPassword($username, $password) {
        $user = $this->getByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        return password_verify($password, $user['password']);
    }

    /**
     * Update last login
     */
    public function updateLastLogin($id) {
        $sql = "UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username, $exclude_id = null) {
        $sql = "SELECT id FROM admin_users WHERE username = ?";
        $params = [$username];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $result = $this->db->getRow($sql, $params);
        return $result ? true : false;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $exclude_id = null) {
        $sql = "SELECT id FROM admin_users WHERE email = ?";
        $params = [$email];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $result = $this->db->getRow($sql, $params);
        return $result ? true : false;
    }

    /**
     * Count total users
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM admin_users";
        $result = $this->db->getRow($sql);
        return $result['total'];
    }

    /**
     * Count admins
     */
    public function countAdmins() {
        $sql = "SELECT COUNT(*) as total FROM admin_users WHERE role = 'admin'";
        $result = $this->db->getRow($sql);
        return $result['total'];
    }

    /**
     * Get admins
     */
    public function getAdmins() {
        $sql = "SELECT id, username, email, role, last_login FROM admin_users WHERE role = 'admin' ORDER BY username ASC";
        return $this->db->getRows($sql);
    }

    /**
     * Get staff
     */
    public function getStaff() {
        $sql = "SELECT id, username, email, role, last_login FROM admin_users WHERE role = 'staff' ORDER BY username ASC";
        return $this->db->getRows($sql);
    }
}
