<?php
/**
 * Contact Submission Handler
 * Receives contact form submissions, validates, and stores them in the database.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/database/Connection.php';
require_once __DIR__ . '/database/ContactRepository.php';

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

    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $subject = trim($input['subject'] ?? 'Website Inquiry');
    $message = trim($input['message'] ?? '');

    // Basic Validation
    if (empty($name)) {
        $response['message'] = 'Please enter your name.';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } elseif (empty($message)) {
        $response['message'] = 'Please enter your message.';
    } else {
        $contactRepo = new ContactRepository();
        $inquiryData = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ];

        try {
            $inquiry_id = $contactRepo->create($inquiryData);
            if ($inquiry_id) {
                $response = [
                    'success' => true,
                    'message' => 'Thank you for contacting us! Your inquiry has been received.'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to send inquiry. Please try again.'
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
