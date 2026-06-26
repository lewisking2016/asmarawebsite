<?php
/**
 * Reports & Analytics Page
 * View business statistics and generate reports
 */

$page_title = 'Reports & Analytics | Asmara Admin';

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../database/BookingRepository.php';
require_once __DIR__ . '/../database/MenuRepository.php';
require_once __DIR__ . '/../database/ContactRepository.php';
require_once __DIR__ . '/../security/Auth.php';

Auth::requireLogin();

$bookingRepo = new BookingRepository();
$menuRepo = new MenuRepository();
$contactRepo = new ContactRepository();

// Get report data
$total_bookings = $bookingRepo->count();
$pending_bookings = $bookingRepo->countByStatus('pending');
$confirmed_bookings = $bookingRepo->countByStatus('confirmed');
$completed_bookings = $bookingRepo->countByStatus('completed');
$cancelled_bookings = $bookingRepo->countByStatus('cancelled');

$total_menu_items = $menuRepo->count();
$total_inquiries = $contactRepo->count();
$new_inquiries = $contactRepo->getNewCount();
$replied_inquiries = count($contactRepo->getByStatus('replied'));

// Calculate percentages
$booking_completion_rate = $total_bookings > 0 ? round(($completed_bookings / $total_bookings) * 100, 1) : 0;
$booking_cancellation_rate = $total_bookings > 0 ? round(($cancelled_bookings / $total_bookings) * 100, 1) : 0;
$inquiry_response_rate = $total_inquiries > 0 ? round(($replied_inquiries / $total_inquiries) * 100, 1) : 0;

// Get recent bookings for trend analysis
$recent_bookings = $bookingRepo->getAll();

?>
<?php include 'header.php'; ?>

            <div class="page-header">
                <h1 class="page-title">Reports & Analytics</h1>
                <p class="page-subtitle">Business statistics and performance metrics</p>
            </div>

            <div class="page-content">
                <!-- KEY METRICS -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 13h3v8H3zM10 7h3v14h-3zM17 3h3v18h-3z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $total_bookings; ?></div>
                            <div class="stat-label">Total Bookings</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 16.2l-3.5-3.5L4 14.2 9 19.2 20 8.2 18.6 6.8z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" style="color: #10b981;"><?php echo $completed_bookings; ?></div>
                            <div class="stat-label">Completed (<?php echo $booking_completion_rate; ?>%)</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 8v5l3 3 .7-.7-2.7-2.3V8z" fill="currentColor"/>
                                <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM12 20a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" style="color: #f59e0b;"><?php echo $pending_bookings; ?></div>
                            <div class="stat-label">Pending Bookings</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.3 5.7l-1-1L12 9l-5.3-4.3-1 1L11 10l-5 5 1 1L12 11l5.3 5.7 1-1-5-5z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" style="color: #ef4444;"><?php echo $cancelled_bookings; ?></div>
                            <div class="stat-label">Cancelled (<?php echo $booking_cancellation_rate; ?>%)</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 3h10v2H7zM7 7h10v2H7zM7 11h6v2H7zM5 21h14V5H5v16z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $total_inquiries; ?></div>
                            <div class="stat-label">Total Inquiries</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 6h-18v10h4v4l4-4h10z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" style="color: #3b82f6;"><?php echo $new_inquiries; ?></div>
                            <div class="stat-label">New Messages</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 2v20H5V2h2zm12 4c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2s2-.9 2-2V8c0-1.1-.9-2-2-2zM13 7h-2v10h2V7z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $total_menu_items; ?></div>
                            <div class="stat-label">Menu Items</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 17h2v4H3zM7 13h2v8H7zM11 7h2v14h-2zM15 3h2v18h-2z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" style="color: #10b981;"><?php echo $inquiry_response_rate; ?>%</div>
                            <div class="stat-label">Response Rate</div>
                        </div>
                    </div>
                </div>

                <!-- DETAILED REPORTS -->
                <div class="section">
                    <h3>Booking Status Breakdown</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Percentage</th>
                                <th>Visual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Confirmed</strong></td>
                                <td><?php echo $confirmed_bookings; ?></td>
                                <td><?php echo $total_bookings > 0 ? round(($confirmed_bookings / $total_bookings) * 100, 1) : 0; ?>%</td>
                                <td>
                                    <div style="width: 200px; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                        <div style="width: <?php echo $total_bookings > 0 ? round(($confirmed_bookings / $total_bookings) * 100) : 0; ?>%; height: 100%; background: #3b82f6;"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Pending</strong></td>
                                <td><?php echo $pending_bookings; ?></td>
                                <td><?php echo $total_bookings > 0 ? round(($pending_bookings / $total_bookings) * 100, 1) : 0; ?>%</td>
                                <td>
                                    <div style="width: 200px; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                        <div style="width: <?php echo $total_bookings > 0 ? round(($pending_bookings / $total_bookings) * 100) : 0; ?>%; height: 100%; background: #f59e0b;"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Completed</strong></td>
                                <td><?php echo $completed_bookings; ?></td>
                                <td><?php echo $total_bookings > 0 ? round(($completed_bookings / $total_bookings) * 100, 1) : 0; ?>%</td>
                                <td>
                                    <div style="width: 200px; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                        <div style="width: <?php echo $total_bookings > 0 ? round(($completed_bookings / $total_bookings) * 100) : 0; ?>%; height: 100%; background: #10b981;"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Cancelled</strong></td>
                                <td><?php echo $cancelled_bookings; ?></td>
                                <td><?php echo $total_bookings > 0 ? round(($cancelled_bookings / $total_bookings) * 100, 1) : 0; ?>%</td>
                                <td>
                                    <div style="width: 200px; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                        <div style="width: <?php echo $total_bookings > 0 ? round(($cancelled_bookings / $total_bookings) * 100) : 0; ?>%; height: 100%; background: #ef4444;"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- INQUIRY INSIGHTS -->
                <div class="section">
                    <h3>Inquiry Management Summary</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
                        <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb;">
                            <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">Total Inquiries Received</div>
                            <div style="font-size: 32px; font-weight: 700; color: #1e1b18;"><?php echo $total_inquiries; ?></div>
                        </div>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb;">
                            <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">New Inquiries</div>
                            <div style="font-size: 32px; font-weight: 700; color: #3b82f6;"><?php echo $new_inquiries; ?></div>
                        </div>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb;">
                            <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">Replied</div>
                            <div style="font-size: 32px; font-weight: 700; color: #10b981;"><?php echo $replied_inquiries; ?> (<?php echo $inquiry_response_rate; ?>%)</div>
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTIONS -->
                <div class="quick-actions">
                    <h3>Quick Access</h3>
                    <div class="action-buttons">
                        <a href="bookings.php" class="action-btn">View All Bookings</a>
                        <a href="contact.php" class="action-btn">View All Inquiries</a>
                        <a href="menu.php" class="action-btn">Manage Menu</a>
                        <a href="events.php" class="action-btn">Manage Events</a>
                    </div>
                </div>

            </div>

<?php include 'footer.php'; ?>
