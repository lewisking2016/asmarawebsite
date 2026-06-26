<?php
/**
 * Contact Repository
 * Data access layer for contact inquiries
 */

require_once __DIR__ . '/Connection.php';

class ContactRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all inquiries
     */
    public function getAll($status = null) {
        $sql = "SELECT * FROM contact_inquiries";
        
        if ($status) {
            $sql .= " WHERE status = ?";
            $sql .= " ORDER BY created_at DESC";
            return $this->db->getRows($sql, [$status]);
        }
        
        $sql .= " ORDER BY created_at DESC";
        return $this->db->getRows($sql);
    }

    /**
     * Get inquiry by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM contact_inquiries WHERE id = ?";
        return $this->db->getRow($sql, [$id]);
    }

    /**
     * Create new inquiry
     */
    public function create($data) {
        $sql = "INSERT INTO contact_inquiries (name, email, subject, message) VALUES (?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['name'],
            $data['email'],
            $data['subject'],
            $data['message']
        ]);
    }

    /**
     * Update inquiry
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'email', 'subject', 'message', 'status', 'admin_response'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE contact_inquiries SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $this->db->query($sql, $params);
        return true;
    }

    /**
     * Update inquiry status
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE contact_inquiries SET status = ? WHERE id = ?";
        $this->db->query($sql, [$status, $id]);
        return true;
    }

    /**
     * Add admin response
     */
    public function addResponse($id, $response) {
        $sql = "UPDATE contact_inquiries SET admin_response = ?, status = 'replied' WHERE id = ?";
        $this->db->query($sql, [$response, $id]);
        return true;
    }

    /**
     * Delete inquiry
     */
    public function delete($id) {
        $sql = "DELETE FROM contact_inquiries WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Get new inquiries count
     */
    public function getNewCount() {
        $sql = "SELECT COUNT(*) as total FROM contact_inquiries WHERE status = 'new'";
        $result = $this->db->getRow($sql);
        return $result['total'];
    }

    /**
     * Get inquiries by status
     */
    public function getByStatus($status) {
        $sql = "SELECT * FROM contact_inquiries WHERE status = ? ORDER BY created_at DESC";
        return $this->db->getRows($sql, [$status]);
    }

    /**
     * Search inquiries
     */
    public function search($query) {
        $search = '%' . $query . '%';
        $sql = "SELECT * FROM contact_inquiries WHERE name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ? ORDER BY created_at DESC";
        return $this->db->getRows($sql, [$search, $search, $search, $search]);
    }

    /**
     * Count total inquiries
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM contact_inquiries";
        $result = $this->db->getRow($sql);
        return $result['total'];
    }

    /**
     * Get inquiries by date
     */
    public function getByDate($date) {
        $sql = "SELECT * FROM contact_inquiries WHERE DATE(created_at) = ? ORDER BY created_at DESC";
        return $this->db->getRows($sql, [$date]);
    }

    /**
     * Get recent inquiries
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT * FROM contact_inquiries ORDER BY created_at DESC LIMIT ?";
        return $this->db->getRows($sql, [$limit]);
    }
}
