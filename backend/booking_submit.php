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
