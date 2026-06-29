<?php
/**
 * Bookings Management Page
 * View, confirm, reject, and cancel bookings
 */

$page_title = 'Bookings Management | Asmara Admin';

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../database/BookingRepository.php';
require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../data/email_helpers.php';

Auth::requireLogin();

$bookingRepo = new BookingRepository();
$action = $_GET['action'] ?? 'list';
$view_id = $_GET['id'] ?? null;
$message = '';
$message_type = 'success';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $cancellation_reason = trim($_POST['cancellation_reason'] ?? '');

    if (in_array($status, ['confirmed', 'completed', 'cancelled'])) {
        if ($status === 'cancelled' && $cancellation_reason === '') {
            $message = 'Please enter a cancellation reason before cancelling the reservation.';
            $message_type = 'error';
        } else {
            $booking_detail = $bookingRepo->getById($booking_id);
            $bookingRepo->updateStatus($booking_id, $status);
            Auth::logActivity(Auth::getCurrentUserId(), 'updated', 'bookings', $booking_id);
            $message = 'Booking status updated successfully!';
            if ($booking_detail && in_array($status, ['confirmed', 'cancelled', 'completed'], true)) {
                $emailSent = asmara_send_booking_status_email($booking_detail, $status, $cancellation_reason);
                $salesSent = asmara_send_booking_status_to_sales($booking_detail, $status, $cancellation_reason);
                if ($emailSent) {
                    $message .= ' Email sent to the guest.';
                } else {
                    $message .= ' Status updated, but the email could not be sent.';
                }
                if ($salesSent) {
                    $message .= ' Sales has been notified.';
                } else {
                    $message .= ' Sales notification could not be sent.';
                }
            }
            $message_type = 'success';
            $action = 'list';
        }
    }
}

// Get filter
$filter_status = $_GET['filter'] ?? null;

// Get bookings
if ($filter_status) {
    $bookings = $bookingRepo->getByStatus($filter_status);
} else {
    $bookings = $bookingRepo->getAll();
}

$booking_detail = null;
if ($view_id && $action === 'view') {
    $booking_detail = $bookingRepo->getById($view_id);
}

?>
<?php include 'header.php'; ?>

            <div class="page-header">
                <h1 class="page-title">Bookings Management</h1>
                <p class="page-subtitle">Manage restaurant reservations</p>
            </div>

            <div class="page-content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type === 'error' ? 'error' : 'success'; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if ($action === 'view' && $booking_detail): ?>
                    <!-- BOOKING DETAIL VIEW -->
                    <div class="detail-container">
                        <a href="?action=list" class="back-link">← Back to Bookings</a>

                        <div class="detail-header">
                            <h2>Booking #<?php echo $booking_detail['id']; ?></h2>
                            <span class="status-badge status-<?php echo $booking_detail['status']; ?>">
                                <?php echo ucfirst($booking_detail['status']); ?>
                            </span>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-section">
                                <h3>Guest Information</h3>
                                <div class="detail-item">
                                    <label>Name:</label>
                                    <p><?php echo htmlspecialchars($booking_detail['guest_name']); ?></p>
                                </div>
                                <div class="detail-item">
                                    <label>Email:</label>
                                    <p><a href="mailto:<?php echo htmlspecialchars($booking_detail['email']); ?>">
                                        <?php echo htmlspecialchars($booking_detail['email']); ?>
                                    </a></p>
                                </div>
                                <div class="detail-item">
                                    <label>Phone:</label>
                                    <p><a href="tel:<?php echo htmlspecialchars($booking_detail['phone']); ?>">
                                        <?php echo htmlspecialchars($booking_detail['phone']); ?>
                                    </a></p>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h3>Reservation Details</h3>
                                <div class="detail-item">
                                    <label>Date:</label>
                                    <p><?php echo date('F j, Y', strtotime($booking_detail['booking_date'])); ?></p>
                                </div>
                                <div class="detail-item">
                                    <label>Time:</label>
                                    <p><?php echo htmlspecialchars($booking_detail['booking_time']); ?></p>
                                </div>
                                <div class="detail-item">
                                    <label>Number of Guests:</label>
                                    <p><?php echo htmlspecialchars($booking_detail['guest_count']); ?></p>
                                </div>
                                <div class="detail-item">
                                    <label>Branch:</label>
                                    <p><?php echo htmlspecialchars($booking_detail['branch_name']); ?></p>
                                </div>
                                <div class="detail-item">
                                    <label>Confirmation Code:</label>
                                    <p><code><?php echo htmlspecialchars($booking_detail['confirmation_code']); ?></code></p>
                                </div>
                            </div>

                            <?php if ($booking_detail['special_requests']): ?>
                            <div class="detail-section">
                                <h3>Special Requests</h3>
                                <p><?php echo htmlspecialchars($booking_detail['special_requests']); ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="detail-section">
                                <h3>Booking Timeline</h3>
                                <div class="detail-item">
                                    <label>Created:</label>
                                    <p><?php echo date('F j, Y \a\t H:i', strtotime($booking_detail['created_at'])); ?></p>
                                </div>
                                <div class="detail-item">
                                    <label>Last Updated:</label>
                                    <p><?php echo date('F j, Y \a\t H:i', strtotime($booking_detail['updated_at'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <?php if ($booking_detail['status'] === 'pending'): ?>
                        <div class="action-panel">
                            <h3>Update Status</h3>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="booking_id" value="<?php echo $booking_detail['id']; ?>">
                                <div class="form-group">
                                    <label>New Status:</label>
                                    <select name="status" id="booking-status-select" required>
                                        <option value="">Select new status</option>
                                        <option value="confirmed">Confirm Booking</option>
                                        <option value="cancelled">Cancel Booking</option>
                                    </select>
                                </div>
                                <div class="form-group" id="cancellation-reason-wrap" style="display:none;">
                                    <label>Cancellation Reason:</label>
                                    <textarea name="cancellation_reason" id="cancellation-reason" rows="4" placeholder="Briefly explain why this booking is being cancelled..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Status</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- BOOKINGS LIST WITH SAAS CONTROLS -->
                    <div class="filter-bar-premium">
                        <div class="filter-left-actions">
                            <input 
                                type="text" 
                                id="booking-search-input" 
                                placeholder="Search guests, email, phone..." 
                                class="filter-search-input"
                                onkeyup="filterBookings()"
                            >
                            
                            <select id="status-select-filter" class="filter-select" onchange="filterBookings()">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="filter-tabs">
                            <a href="?action=list" class="action-btn" style="background:#ffffff; color:var(--color-text); border:1.5px solid var(--color-border); box-shadow:none; padding:10px 18px; border-radius:10px;">Reset Filters</a>
                        </div>
                    </div>

                    <?php if (!empty($bookings)): ?>
                        <div class="cards-grid" id="bookingsGrid">
                            <?php foreach ($bookings as $booking): ?>
                                <div class="booking-card" 
                                     data-status="<?php echo htmlspecialchars($booking['status']); ?>"
                                     data-search="<?php echo htmlspecialchars(strtolower($booking['guest_name'] . ' ' . $booking['email'] . ' ' . $booking['phone'] . ' ' . $booking['confirmation_code'])); ?>">
                                    <div class="booking-card-header">
                                        <div>
                                            <h4 style="margin:0; font-family:var(--font-heading); font-size:1.15rem;"><?php echo htmlspecialchars($booking['guest_name']); ?></h4>
                                            <div class="muted" style="font-size:13px; color:var(--color-text-muted); margin-top:2px;"><?php echo htmlspecialchars($booking['branch_name']); ?></div>
                                            <div style="font-size:12px; color:var(--color-text-muted); margin-top:6px;">
                                                Ref: <code><?php echo htmlspecialchars($booking['confirmation_code']); ?></code>
                                            </div>
                                        </div>
                                        <div style="text-align:right">
                                            <span class="status-badge status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                        </div>
                                    </div>

                                    <div class="booking-card-body" style="padding:16px 0; border-bottom: 1px solid var(--color-border); margin-bottom:16px;">
                                        <div class="date-time" style="font-size:14px; font-weight:600; color:var(--color-text);"><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?> • <?php echo htmlspecialchars($booking['booking_time']); ?></div>
                                        <div class="guests" style="font-size:13px; margin-top:6px; color:var(--color-text-muted);">Guests: <strong><?php echo htmlspecialchars($booking['guest_count']); ?></strong></div>
                                        <?php if (!empty($booking['special_requests'])): ?>
                                            <div class="muted" style="font-size:12px; margin-top:8px; background:#f8fafc; padding:8px 12px; border-radius:8px; color:var(--color-text-muted);"><?php echo htmlspecialchars($booking['special_requests']); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="booking-card-footer" style="display:flex; justify-content:space-between; align-items:center;">
                                        <div class="contact-links" style="display:flex; gap:12px; font-size:13px;">
                                            <a href="tel:<?php echo htmlspecialchars($booking['phone']); ?>" style="color:var(--color-primary); font-weight:600;">Call</a>
                                            <a href="mailto:<?php echo htmlspecialchars($booking['email']); ?>" style="color:var(--color-primary); font-weight:600;">Email</a>
                                        </div>
                                        <div class="actions">
                                            <a href="?action=view&id=<?php echo $booking['id']; ?>" class="action-btn" style="padding:8px 16px; border-radius:8px; font-size:12px;">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No bookings found</p>
                        </div>
                    <?php endif; ?>

                    <script>
                        function filterBookings() {
                            const searchVal = document.getElementById('booking-search-input').value.toLowerCase();
                            const statusVal = document.getElementById('status-select-filter').value;
                            const cards = document.querySelectorAll('.booking-card');

                            cards.forEach(card => {
                                const searchData = card.getAttribute('data-search');
                                const status = card.getAttribute('data-status');

                                const matchesSearch = searchData.includes(searchVal);
                                const matchesStatus = statusVal === '' || status === statusVal;

                                if (matchesSearch && matchesStatus) {
                                    card.style.display = '';
                                } else {
                                    card.style.display = 'none';
                                }
                            });
                        }
                    </script>

                <?php endif; ?>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const statusSelect = document.getElementById('booking-status-select');
                    const reasonWrap = document.getElementById('cancellation-reason-wrap');
                    const reasonField = document.getElementById('cancellation-reason');

                    if (statusSelect && reasonWrap && reasonField) {
                        const syncReasonField = () => {
                            const showReason = statusSelect.value === 'cancelled';
                            reasonWrap.style.display = showReason ? 'block' : 'none';
                            reasonField.required = showReason;
                            if (!showReason) {
                                reasonField.value = '';
                            }
                        };

                        statusSelect.addEventListener('change', syncReasonField);
                        syncReasonField();
                    }
                });
            </script>

<?php include 'footer.php'; ?>
