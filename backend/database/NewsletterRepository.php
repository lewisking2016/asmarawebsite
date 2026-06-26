<?php
/**
 * NewsletterRepository
 * Handles all newsletter subscriber database operations
 */

require_once __DIR__ . '/Connection.php';

class NewsletterRepository {
    private $db;
    private $table = 'newsletter_subscribers';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all active newsletter subscribers
     */
    public function getAll($onlyActive = true) {
        $sql = "SELECT * FROM {$this->table}";
        if ($onlyActive) {
            $sql .= " WHERE is_active = TRUE";
        }
        $sql .= " ORDER BY subscribed_at DESC";
        return $this->db->getRows($sql);
    }

    /**
     * Get subscriber by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->getRow($sql, [$id]);
    }

    /**
     * Get subscriber by email
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        return $this->db->getRow($sql, [$email]);
    }

    /**
     * Subscribe a new email
     */
    public function subscribe($email) {
        $email = strtolower(trim($email));
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }

        try {
            // Check if already exists
            $existing = $this->getByEmail($email);
            if ($existing) {
                if ($existing['is_active']) {
                    return [
                        'success' => true,
                        'message' => 'Email already subscribed',
                        'id' => $existing['id']
                    ];
                } else {
                    // Reactivate
                    $sql = "UPDATE {$this->table} SET is_active = TRUE WHERE email = ?";
                    $this->db->query($sql, [$email]);
                    return [
                        'success' => true,
                        'message' => 'Subscription reactivated',
                        'id' => $existing['id']
                    ];
                }
            }

            // Insert new subscriber
            $sql = "INSERT INTO {$this->table} (email, is_active) VALUES (?, TRUE)";
            $this->db->insert($sql, [$email]);
            
            return [
                'success' => true,
                'message' => 'Successfully subscribed'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Unsubscribe an email
     */
    public function unsubscribe($email) {
        try {
            $sql = "UPDATE {$this->table} SET is_active = FALSE WHERE email = ?";
            $this->db->query($sql, [strtolower(trim($email))]);
            return [
                'success' => true,
                'message' => 'Successfully unsubscribed'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a subscriber
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $this->db->query($sql, [$id]);
            return [
                'success' => true,
                'message' => 'Subscriber deleted'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get total subscriber count
     */
    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE is_active = TRUE";
        $result = $this->db->getRow($sql);
        return $result['count'] ?? 0;
    }

    /**
     * Get active subscriber count
     */
    public function getActiveCount() {
        return $this->getTotalCount();
    }

    /**
     * Search subscribers by email
     */
    public function search($searchTerm) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE email LIKE ? 
                ORDER BY subscribed_at DESC";
        return $this->db->getRows($sql, ['%' . $searchTerm . '%']);
    }

    /**
     * Export subscribers as CSV
     */
    public function exportAsCSV() {
        $subscribers = $this->getAll();
        $csv = "Email,Subscribed Date,Status\n";
        
        foreach ($subscribers as $sub) {
            $status = $sub['is_active'] ? 'Active' : 'Inactive';
            $csv .= "\"{$sub['email']}\",\"{$sub['subscribed_at']}\",\"{$status}\"\n";
        }
        
        return $csv;
    }
}
?>
