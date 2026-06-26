<?php
/**
 * Branch Repository
 * Data access layer for branches
 */

require_once __DIR__ . '/Connection.php';
require_once __DIR__ . '/../security/Auth.php';

class BranchRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all branches
     */
    public function getAll() {
        $sql = "SELECT * FROM branches ORDER BY name ASC";
        return $this->db->getRows($sql);
    }

    /**
     * Get branch by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM branches WHERE id = ?";
        return $this->db->getRow($sql, [$id]);
    }

    /**
     * Get branch by name
     */
    public function getByName($name) {
        $sql = "SELECT * FROM branches WHERE name = ?";
        return $this->db->getRow($sql, [$name]);
    }

    /**
     * Create new branch
     */
    public function create($data) {
        $sql = "INSERT INTO branches (name, address, phone, email, opening_hours, capacity, latitude, longitude, subtitle, summary, long_description, seo_keywords, hero_image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['name'],
            $data['address'],
            $data['phone'],
            $data['email'],
            $data['opening_hours'] ?? null,
            $data['capacity'] ?? 50,
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['subtitle'] ?? null,
            $data['summary'] ?? null,
            $data['long_description'] ?? null,
            $data['seo_keywords'] ?? null,
            $data['hero_image'] ?? null
        ]);
    }

    /**
     * Update branch
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'address', 'phone', 'email', 'opening_hours', 'capacity', 'latitude', 'longitude', 'subtitle', 'summary', 'long_description', 'seo_keywords', 'hero_image'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE branches SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $this->db->query($sql, $params);
        return true;
    }

    /**
     * Delete branch
     */
    public function delete($id) {
        $sql = "DELETE FROM branches WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Get branch capacity
     */
    public function getCapacity($id) {
        $sql = "SELECT capacity FROM branches WHERE id = ?";
        $result = $this->db->getRow($sql, [$id]);
        return $result ? $result['capacity'] : 0;
    }

    /**
     * Count total branches
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM branches";
        $result = $this->db->getRow($sql);
        return $result['total'];
    }
}
