<?php
/**
 * Booking Submission Handler
 * Receives booking data, validates, and creates a database record.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/database/Connection.php';
require_once __DIR__ . '/database/BookingRepository.php';
require_once __DIR__ . '/database/BranchRepository.php';
require_once __DIR__ . '/security/Validator.php';

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

    $guest_name = trim($input['fullname'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $branch_name = trim($input['branch'] ?? '');
    $guest_count = intval($input['guests'] ?? 0);
    $booking_date = trim($input['date'] ?? '');
    $booking_time = trim($input['time'] ?? '');
    $special_requests = trim($input['requests'] ?? '');
    $event_id = trim($input['event_id'] ?? '');

    // Map branch name to branch_id
    $branchRepo = new BranchRepository();
    $branch = $branchRepo->getByName($branch_name);
    $branch_id = $branch ? intval($branch['id']) : 0;

    $bookingData = [
        'guest_name' => $guest_name,
        'email' => $email,
        'phone' => $phone,
        'booking_date' => $booking_date,
        'booking_time' => $booking_time,
        'guest_count' => $guest_count,
        'branch_id' => $branch_id,
        'special_requests' => $special_requests
    ];

    if ($event_id !== '') {
        $bookingData['event_id'] = $event_id;
    }

    if (Validator::validateBooking($bookingData)) {
        $bookingRepo = new BookingRepository();
        
        // Check availability
        if ($bookingRepo->isTimeSlotAvailable($branch_id, $booking_date, $booking_time, $guest_count)) {
            try {
                $booking_id = $bookingRepo->create($bookingData);
                if ($booking_id) {
                    $newBooking = $bookingRepo->getById($booking_id);
                    $response = [
                        'success' => true,
                        'message' => 'Reservation confirmed successfully!',
                        'code' => $newBooking['confirmation_code'],
                        'name' => $guest_name,
                        'guests' => $guest_count,
                        'branch' => $newBooking['branch_name'],
                        'date' => $booking_date,
                        'time' => $booking_time
                    ];

                    // Send email notification to management
                    $notifyEmails = ['semhar@asmara.co.ke', 'sales@asmara.co.ke'];
                    $branchDisplay = $newBooking['branch_name'] ?? $branch_name;
                    $confCode = $newBooking['confirmation_code'];

                    $subject = "New Reservation: {$guest_name} — {$branchDisplay} ({$confCode})";

                    $body  = "<html><body style='font-family: Arial, sans-serif; color: #333;'>";
                    $body .= "<h2 style='color: #ed174b;'>New Reservation Received</h2>";
                    $body .= "<table style='border-collapse: collapse; width: 100%; max-width: 500px;'>";
                    $body .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Confirmation Code</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$confCode}</td></tr>";
                    $body .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Guest Name</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$guest_name}</td></tr>";
                    $body .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Email</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$email}</td></tr>";
                    $body .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Phone</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$phone}</td></tr>";
                    $body .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Branch</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$branchDisplay}</td></tr>";
                    $body .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Date</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$booking_date}</td></tr>";
                    $body .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Time</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$booking_time}</td></tr>";
                    $body .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Guests</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$guest_count}</td></tr>";
                    if (!empty($special_requests)) {
                        $body .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Special Requests</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$special_requests}</td></tr>";
                    }
                    $body .= "</table>";
                    $body .= "<p style='margin-top: 20px; font-size: 0.85rem; color: #999;'>This is an automated notification from the Asmara Restaurants website.</p>";
                    $body .= "</body></html>";

                    $headers  = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                    $headers .= "From: Asmara Reservations <noreply@asmara.co.ke>\r\n";
                    $headers .= "Reply-To: {$email}\r\n";

                    foreach ($notifyEmails as $to) {
                        @mail($to, $subject, $body, $headers);
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Failed to create booking in database.'
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'This branch is at full capacity for the selected time slot. Please choose another time or branch.'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => Validator::getFirstError() ?: 'Validation failed.'
        ];
    }
}

echo json_encode($response);
exit;
