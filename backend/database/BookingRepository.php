<?php
/**
 * Booking Repository
 * Data access layer for bookings
 */

require_once __DIR__ . '/Connection.php';

class BookingRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all bookings
     */
    public function getAll($status = null, $limit = 100, $offset = 0) {
        $sql = "SELECT b.*, br.name as branch_name FROM bookings b 
                JOIN branches br ON b.branch_id = br.id";
        
        if ($status) {
            $sql .= " WHERE b.status = ?";
            return $this->db->getRows($sql . " ORDER BY b.booking_date DESC, b.booking_time DESC LIMIT ? OFFSET ?", 
                [$status, $limit, $offset]);
        }
        
        return $this->db->getRows($sql . " ORDER BY b.booking_date DESC, b.booking_time DESC LIMIT ? OFFSET ?", 
            [$limit, $offset]);
    }

    /**
     * Get booking by ID
     */
    public function getById($id) {
        $sql = "SELECT b.*, br.name as branch_name FROM bookings b 
                JOIN branches br ON b.branch_id = br.id WHERE b.id = ?";
        return $this->db->getRow($sql, [$id]);
    }

    /**
     * Get booking by confirmation code
     */
    public function getByConfirmationCode($code) {
        $sql = "SELECT b.*, br.name as branch_name FROM bookings b 
                JOIN branches br ON b.branch_id = br.id WHERE b.confirmation_code = ?";
        return $this->db->getRow($sql, [$code]);
    }

    /**
     * Create new booking
     */
    public function create($data) {
        $confirmation_code = $this->generateConfirmationCode();
        // Build insert dynamically to optionally include event_id when provided
        $columns = [
            'guest_name', 'email', 'phone', 'booking_date', 'booking_time', 'guest_count', 'branch_id'
        ];
        $params = [
            $data['guest_name'],
            $data['email'],
            $data['phone'],
            $data['booking_date'],
            $data['booking_time'],
            $data['guest_count'],
            $data['branch_id']
        ];

        if (!empty($data['special_requests'])) {
            $columns[] = 'special_requests';
            $params[] = $data['special_requests'];
        } else {
            $columns[] = 'special_requests';
            $params[] = null;
        }

        if (isset($data['event_id']) && $data['event_id'] !== '') {
            // Ensure the bookings table has event_id column before including it
            try {
                $col = $this->db->getRow("SHOW COLUMNS FROM bookings LIKE 'event_id'");
                if ($col) {
                    $columns[] = 'event_id';
                    $params[] = $data['event_id'];
                }
            } catch (Exception $e) {
                // ignore, do not include event_id if schema doesn't support it
            }
        }

        // confirmation code always appended
        $columns[] = 'confirmation_code';
        $params[] = $confirmation_code;

        $placeholders = array_fill(0, count($columns), '?');
        $sql = "INSERT INTO bookings (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        return $this->db->insert($sql, $params);
    }

    /**
     * Update booking
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['guest_name', 'email', 'phone', 'booking_date', 'booking_time', 'guest_count', 'status', 'special_requests'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE bookings SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $this->db->query($sql, $params);
        return true;
    }

    /**
     * Update booking status
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE bookings SET status = ? WHERE id = ?";
        $this->db->query($sql, [$status, $id]);
        return true;
    }

    /**
     * Delete booking
     */
    public function delete($id) {
        $sql = "DELETE FROM bookings WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Get bookings by date
     */
    public function getByDate($date) {
        $sql = "SELECT b.*, br.name as branch_name FROM bookings b 
                JOIN branches br ON b.branch_id = br.id 
                WHERE DATE(b.booking_date) = ? AND b.status IN ('pending', 'confirmed')
                ORDER BY b.booking_time ASC";
        return $this->db->getRows($sql, [$date]);
    }

    /**
     * Get bookings by branch and date
     */
    public function getByBranchAndDate($branch_id, $date) {
        $sql = "SELECT * FROM bookings 
                WHERE branch_id = ? AND DATE(booking_date) = ? AND status IN ('pending', 'confirmed')
                ORDER BY booking_time ASC";
        return $this->db->getRows($sql, [$branch_id, $date]);
    }

    /**
     * Get bookings by guest email
     */
    public function getByEmail($email) {
        $sql = "SELECT b.*, br.name as branch_name FROM bookings b 
                JOIN branches br ON b.branch_id = br.id 
                WHERE b.email = ? ORDER BY b.booking_date DESC";
        return $this->db->getRows($sql, [$email]);
    }

    /**
     * Get bookings by status
     */
    public function getByStatus($status) {
        $sql = "SELECT b.*, br.name as branch_name FROM bookings b 
                JOIN branches br ON b.branch_id = br.id 
                WHERE b.status = ? ORDER BY b.booking_date DESC";
        return $this->db->getRows($sql, [$status]);
    }

    /**
     * Count bookings by status
     */
    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) as total FROM bookings WHERE status = ?";
        $result = $this->db->getRow($sql, [$status]);
        return $result['total'];
    }

    /**
     * Count total bookings
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM bookings";
        $result = $this->db->getRow($sql);
        return $result['total'];
    }

    /**
     * Get bookings for date range
     */
    public function getDateRange($start_date, $end_date) {
        $sql = "SELECT b.*, br.name as branch_name FROM bookings b 
                JOIN branches br ON b.branch_id = br.id 
                WHERE DATE(b.booking_date) BETWEEN ? AND ?
                ORDER BY b.booking_date DESC, b.booking_time DESC";
        return $this->db->getRows($sql, [$start_date, $end_date]);
    }

    /**
     * Check if time slot is available
     */
    public function isTimeSlotAvailable($branch_id, $date, $time, $guest_count) {
        $sql = "SELECT SUM(guest_count) as total_guests FROM bookings 
                WHERE branch_id = ? AND booking_date = ? AND booking_time = ? AND status IN ('pending', 'confirmed')";
        
        $result = $this->db->getRow($sql, [$branch_id, $date, $time]);
        $total_guests = $result['total_guests'] ?? 0;
        
        // Get branch capacity
        $branch_sql = "SELECT capacity FROM branches WHERE id = ?";
        $branch = $this->db->getRow($branch_sql, [$branch_id]);
        
        return ($total_guests + $guest_count) <= $branch['capacity'];
    }

    /**
     * Generate unique confirmation code
     */
    private function generateConfirmationCode() {
        // Query next AUTO_INCREMENT value from information_schema, or fallback to count + 1000
        try {
            $sql = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings'";
            $res = $this->db->getRow($sql);
            $nextId = $res['AUTO_INCREMENT'] ?? null;
            if (!$nextId) {
                $countSql = "SELECT COUNT(*) as total FROM bookings";
                $countRes = $this->db->getRow($countSql);
                $nextId = ($countRes['total'] ?? 0) + 1;
            }
        } catch (Exception $e) {
            $nextId = rand(1000, 9999);
        }

        // Generate a sequential number padded with leading zeros (e.g., ASM-10001, ASM-10002)
        $startNumber = 10000 + intval($nextId);
        return "ASM-" . $startNumber;
    }

    /**
     * Get today's bookings
     */
    public function getTodayBookings() {
        $sql = "SELECT b.*, br.name as branch_name FROM bookings b 
                JOIN branches br ON b.branch_id = br.id 
                WHERE DATE(b.booking_date) = CURDATE() AND b.status IN ('pending', 'confirmed')
                ORDER BY b.booking_time ASC";
        return $this->db->getRows($sql);
    }

    /**
     * Get upcoming bookings
     */
    public function getUpcomingBookings($days = 7) {
        $sql = "SELECT b.*, br.name as branch_name FROM bookings b 
                JOIN branches br ON b.branch_id = br.id 
                WHERE b.booking_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY) 
                AND b.status IN ('pending', 'confirmed')
                ORDER BY b.booking_date ASC, b.booking_time ASC";
        return $this->db->getRows($sql, [$days]);
    }
}
