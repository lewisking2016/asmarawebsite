<?php
/**
 * Menu Repository
 * Data access layer for menu items
 */

require_once __DIR__ . '/Connection.php';

class MenuRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all menu items
     */
    public function getAll($available_only = false) {
        $sql = "SELECT * FROM menu_items";
        if ($available_only) {
            $sql .= " WHERE is_available = TRUE";
        }
        $sql .= " ORDER BY category ASC, name ASC";
        return $this->db->getRows($sql);
    }

    /**
     * Get menu items by category
     */
    public function getByCategory($category, $available_only = false) {
        $sql = "SELECT * FROM menu_items WHERE category = ?";
        if ($available_only) {
            $sql .= " AND is_available = TRUE";
        }
        $sql .= " ORDER BY name ASC";
        return $this->db->getRows($sql, [$category]);
    }

    /**
     * Get menu item by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM menu_items WHERE id = ?";
        return $this->db->getRow($sql, [$id]);
    }

    /**
     * Get menu items by branch
     */
    public function getByBranch($branch_id = null, $available_only = false) {
        $all = $this->getAll($available_only);
        if ($branch_id === null) {
            return $all;
        }
        return array_values(array_filter($all, function($item) use ($branch_id) {
            if (empty($item['available_branches'])) {
                return true;
            }
            $branches = json_decode($item['available_branches'], true);
            if (!is_array($branches)) {
                return true;
            }
            return in_array((string)$branch_id, $branches) || in_array((int)$branch_id, $branches);
        }));
    }

    /**
     * Create new menu item
     */
    public function create($data) {
        $sql = "INSERT INTO menu_items (name, description, category, price, image_url, branch_id, is_available, available_branches) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['name'],
            $data['description'] ?? null,
            $data['category'],
            $data['price'],
            $data['image_url'] ?? null,
            $data['branch_id'] ?? null,
            $data['is_available'] ?? true,
            $data['available_branches'] ?? null
        ]);
    }

    /**
     * Update menu item
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'description', 'category', 'price', 'image_url', 'branch_id', 'is_available', 'available_branches'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE menu_items SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $this->db->query($sql, $params);
        return true;
    }

    /**
     * Delete menu item
     */
    public function delete($id) {
        $sql = "DELETE FROM menu_items WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Toggle availability
     */
    public function toggleAvailability($id) {
        $sql = "UPDATE menu_items SET is_available = NOT is_available WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Get all categories
     */
    public function getCategories() {
        $sql = "SELECT DISTINCT category FROM menu_items ORDER BY category ASC";
        $rows = $this->db->getRows($sql);
        $categories = [];
        foreach ($rows as $row) {
            $categories[] = $row['category'];
        }
        return $categories;
    }

    /**
     * Count menu items
     */
    public function count($available_only = false) {
        $sql = "SELECT COUNT(*) as total FROM menu_items";
        if ($available_only) {
            $sql .= " WHERE is_available = TRUE";
        }
        $result = $this->db->getRow($sql);
        return $result['total'];
    }

    /**
     * Count menu items by category
     */
    public function countByCategory($category) {
        $sql = "SELECT COUNT(*) as total FROM menu_items WHERE category = ?";
        $result = $this->db->getRow($sql, [$category]);
        return $result['total'];
    }

    /**
     * Get most expensive items
     */
    public function getMostExpensive($limit = 5) {
        $sql = "SELECT * FROM menu_items ORDER BY price DESC LIMIT ?";
        return $this->db->getRows($sql, [$limit]);
    }

    /**
     * Get least expensive items
     */
    public function getLeastExpensive($limit = 5) {
        $sql = "SELECT * FROM menu_items ORDER BY price ASC LIMIT ?";
        return $this->db->getRows($sql, [$limit]);
    }

    /**
     * Search menu items
     */
    public function search($query) {
        $search = '%' . $query . '%';
        $sql = "SELECT * FROM menu_items WHERE name LIKE ? OR description LIKE ? ORDER BY name ASC";
        return $this->db->getRows($sql, [$search, $search]);
    }
}
