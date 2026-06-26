<?php
/**
 * Admin Dashboard - Home
 */

$page_title = 'Dashboard | Asmara Admin';

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../database/BookingRepository.php';
require_once __DIR__ . '/../database/MenuRepository.php';
require_once __DIR__ . '/../database/ContactRepository.php';
require_once __DIR__ . '/../database/BranchRepository.php';

// Get statistics
$bookingRepo = new BookingRepository();
$menuRepo = new MenuRepository();
$contactRepo = new ContactRepository();
$branchRepo = new BranchRepository();

$total_bookings = $bookingRepo->count();
$pending_bookings = $bookingRepo->countByStatus('pending');
$total_menu_items = $menuRepo->count();
$new_messages = $contactRepo->getNewCount();
$total_branches = $branchRepo->count();

$today_bookings = $bookingRepo->getTodayBookings();
$recent_messages = $contactRepo->getRecent(5);
$upcoming_bookings = $bookingRepo->getUpcomingBookings(7);

?>
<?php include 'header.php'; ?>

            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome to Asmara Restaurant Management System</p>
            </div>

            <div class="page-content">
                <!-- STATISTICS CARDS -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 256 256" fill="none"><path d="M208 32h-24V24a8 8 0 0 0-16 0v8H88V24a8 8 0 0 0-16 0v8H48a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h160a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16Zm0 176H48V48h24v8a8 8 0 0 0 16 0v-8h80v8a8 8 0 0 0 16 0v-8h24Zm-68-76a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm44 0a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm-88 40a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm44 0a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm44 0a12 12 0 1 1-12-12 12 12 0 0 1 12 12Z" fill="currentColor"/></svg></div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $total_bookings; ?></div>
                            <div class="stat-label">Total Bookings</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 256 256" fill="none"><path d="M128 24a104 104 0 1 0 104 104A104.11 104.11 0 0 0 128 24Zm0 192a88 88 0 1 1 88-88 88.1 88.1 0 0 1-88 88Zm64-88a8 8 0 0 1-8 8h-56a8 8 0 0 1-8-8V72a8 8 0 0 1 16 0v48h48a8 8 0 0 1 8 8Z" fill="currentColor"/></svg></div>
                        <div class="stat-content">
                            <div class="stat-value" style="color: #ffd200;"><?php echo $pending_bookings; ?></div>
                            <div class="stat-label">Pending Bookings</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 256 256" fill="none"><path d="M200 32H56a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h144a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16Zm0 176H56V48h144ZM80 80h96a8 8 0 0 1 0 16H80a8 8 0 0 1 0-16Zm0 40h96a8 8 0 0 1 0 16H80a8 8 0 0 1 0-16Zm0 40h64a8 8 0 0 1 0 16H80a8 8 0 0 1 0-16Z" fill="currentColor"/></svg></div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $total_menu_items; ?></div>
                            <div class="stat-label">Menu Items</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 256 256" fill="none"><path d="M224 48H32a8 8 0 0 0-8 8v136a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a8 8 0 0 0-8-8Zm-96 85.15L52.57 64h150.86ZM98.71 128 40 181.81V74.19Zm11.84 10.85 12.58 11a8 8 0 0 0 10.74 0l12.58-11L210.39 192H45.61ZM157.29 128 216 74.19v107.62Z" fill="currentColor"/></svg></div>
                        <div class="stat-content">
                            <div class="stat-value" style="color: #ed174b;"><?php echo $new_messages; ?></div>
                            <div class="stat-label">New Messages</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon"><svg width="24" height="24" viewBox="0 0 256 256" fill="none"><path d="M232 224h-24V72a8 8 0 0 0-8-8h-56V32a8 8 0 0 0-12.27-6.74l-80 52A8 8 0 0 0 48 84v140H24a8 8 0 0 0 0 16h208a8 8 0 0 0 0-16ZM64 224V89.34L136 42v182Zm128 0h-40V80h40ZM96 120h16a8 8 0 0 1 0 16H96a8 8 0 0 1 0-16Zm-16 56a8 8 0 0 1 8-8h16a8 8 0 0 1 0 16H88a8 8 0 0 1-8-8Z" fill="currentColor"/></svg></div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $total_branches; ?></div>
                            <div class="stat-label">Branches</div>
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTIONS -->
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="menu.php" class="action-btn">+ Add Menu Item</a>
                        <a href="bookings.php" class="action-btn">View Bookings</a>
                        <a href="branches.php" class="action-btn">Manage Branches</a>
                        <a href="contact.php" class="action-btn">View Messages</a>
                    </div>
                </div>

                <!-- TODAY'S BOOKINGS -->
                <div class="section">
                    <h3>Today's Bookings</h3>
                    <?php if (!empty($today_bookings)): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Guest Name</th>
                                    <th>Time</th>
                                    <th>Guests</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($today_bookings, 0, 5) as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['guest_count']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['branch_name']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No bookings for today</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- RECENT MESSAGES -->
                <div class="section">
                    <h3>Recent Messages</h3>
                    <?php if (!empty($recent_messages)): ?>
                        <div class="messages-list">
                            <?php foreach ($recent_messages as $message): ?>
                            <div class="message-item">
                                <div class="message-header">
                                    <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                    <span class="message-date"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></span>
                                </div>
                                <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                                <div class="message-preview"><?php echo substr(htmlspecialchars($message['message']), 0, 100) . '...'; ?></div>
                                <a href="contact.php?view=<?php echo $message['id']; ?>" class="view-link">View Message</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No messages yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

<?php include 'footer.php'; ?>
