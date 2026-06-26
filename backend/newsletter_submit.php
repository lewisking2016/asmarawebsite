<?php
/**
 * Newsletter Submission Handler
 * Receives email, validates, and stores in the newsletter_subscribers table.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/database/Connection.php';

$response = [
    'success' => false,
    'message' => 'Invalid request method.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON input if sent as JSON, otherwise fall back to $_POST
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    if (!is_array($input)) {
        $input = $_POST;
    }

    $email = trim($input['email'] ?? '');

    // Basic Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } else {
        try {
            $db = Database::getInstance();
            // Check if already subscribed
            $checkSql = "SELECT * FROM newsletter_subscribers WHERE email = ?";
            $existing = $db->getRow($checkSql, [$email]);

            if ($existing) {
                if (!$existing['is_active']) {
                    // Reactivate subscription
                    $updateSql = "UPDATE newsletter_subscribers SET is_active = TRUE WHERE email = ?";
                    $db->query($updateSql, [$email]);
                    $response = [
                        'success' => true,
                        'message' => 'Welcome back! Your subscription is active again.'
                    ];
                } else {
                    $response = [
                        'success' => true,
                        'message' => 'You are already subscribed to our newsletter!'
                    ];
                }
            } else {
                $insertSql = "INSERT INTO newsletter_subscribers (email) VALUES (?)";
                $db->insert($insertSql, [$email]);
                $response = [
                    'success' => true,
                    'message' => 'Thank you for subscribing to our newsletter!'
                ];
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
}

echo json_encode($response);
exit;
