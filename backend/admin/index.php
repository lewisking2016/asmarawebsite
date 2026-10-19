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
$confirmed_bookings = $bookingRepo->countByStatus('confirmed');
$completed_bookings = $bookingRepo->countByStatus('completed');
$cancelled_bookings = $bookingRepo->countByStatus('cancelled');
$booking_confirmation_rate = $total_bookings > 0 ? round(($confirmed_bookings / $total_bookings) * 100, 1) : 0;
$booking_completion_rate = $total_bookings > 0 ? round(($completed_bookings / $total_bookings) * 100, 1) : 0;
$booking_cancellation_rate = $total_bookings > 0 ? round(($cancelled_bookings / $total_bookings) * 100, 1) : 0;
$booking_pending_rate = $total_bookings > 0 ? round(($pending_bookings / $total_bookings) * 100, 1) : 0;
$total_menu_items = $menuRepo->count();
$new_messages = $contactRepo->getNewCount();
$total_branches = $branchRepo->count();

$today_bookings = $bookingRepo->getTodayBookings();
$recent_messages = $contactRepo->getRecent(5);
$upcoming_bookings = $bookingRepo->getUpcomingBookings(7);

?>
<?php include 'header.php'; ?>

            <div class="ad-page-header">
                <span class="ad-page-eyebrow">✦ Overview</span>
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($current_user['username']); ?>. Here&rsquo;s what&rsquo;s happening across your restaurants right now.</p>
            </div>

            <div class="page-content">
                <!-- STATISTICS TILES -->
                <div class="ad-stat-row" role="list">
                    <a href="bookings.php" class="ad-stat-tile" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M208 32h-24V24a8 8 0 0 0-16 0v8H88V24a8 8 0 0 0-16 0v8H48a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h160a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16Zm0 176H48V48h24v8a8 8 0 0 0 16 0v-8h80v8a8 8 0 0 0 16 0v-8h24Z"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo (int)$total_bookings; ?></div>
                            <div class="ad-stat-label">Total Bookings</div>
                        </div>
                    </a>

                    <a href="bookings.php?filter=completed" class="ad-stat-tile is-emerald" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo (int)$completed_bookings; ?></div>
                            <div class="ad-stat-label">Completed &middot; <?php echo $booking_completion_rate; ?>%</div>
                        </div>
                    </a>

                    <a href="bookings.php?filter=confirmed" class="ad-stat-tile is-cobalt" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo (int)$confirmed_bookings; ?></div>
                            <div class="ad-stat-label">Confirmed &middot; <?php echo $booking_confirmation_rate; ?>%</div>
                        </div>
                    </a>

                    <a href="bookings.php?filter=pending" class="ad-stat-tile is-amber" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 7v6l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo (int)$pending_bookings; ?></div>
                            <div class="ad-stat-label">Pending &middot; <?php echo $booking_pending_rate; ?>%</div>
                        </div>
                    </a>

                    <a href="bookings.php?filter=cancelled" class="ad-stat-tile is-rose" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo (int)$cancelled_bookings; ?></div>
                            <div class="ad-stat-label">Cancelled &middot; <?php echo $booking_cancellation_rate; ?>%</div>
                        </div>
                    </a>

                    <a href="menu.php" class="ad-stat-tile is-rose" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M200 32H56a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h144a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16Zm0 176H56V48h144Z"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo (int)$total_menu_items; ?></div>
                            <div class="ad-stat-label">Menu Items</div>
                        </div>
                    </a>

                    <a href="contact.php" class="ad-stat-tile is-cobalt" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224 48H32a8 8 0 0 0-8 8v136a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a8 8 0 0 0-8-8Z"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo (int)$new_messages; ?></div>
                            <div class="ad-stat-label">New Messages</div>
                        </div>
                    </a>

                    <a href="branches.php" class="ad-stat-tile is-emerald" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232 224h-24V72a8 8 0 0 0-8-8h-56V32a8 8 0 0 0-12.27-6.74l-80 52A8 8 0 0 0 48 84v140H24a8 8 0 0 0 0 16h208a8 8 0 0 0 0-16Z"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo (int)$total_branches; ?></div>
                            <div class="ad-stat-label">Branches</div>
                        </div>
                    </a>
                </div>

                <!-- QUICK ACTIONS -->
                <div class="ad-card ad-card-padded" style="margin-bottom:24px;">
                    <div class="ad-card-header" style="border:0; padding:0 0 16px;">
                        <h3>Quick Actions</h3>
                        <span class="ad-chip ad-chip-ink"><span class="ad-chip-dot"></span>Shortcuts</span>
                    </div>
                    <div class="action-buttons" style="display:flex; flex-wrap:wrap; gap:10px;">
                        <a href="menu.php" class="ad-btn ad-btn-primary">+ Add Menu Item</a>
                        <a href="bookings.php" class="ad-btn ad-btn-ghost">View Bookings</a>
                        <a href="branches.php" class="ad-btn ad-btn-ghost">Manage Branches</a>
                        <a href="contact.php" class="ad-btn ad-btn-ghost">View Messages</a>
                    </div>
                </div>

                <!-- TODAY'S BOOKINGS -->
                <div class="ad-card ad-card-padded" style="margin-bottom:24px;">
                    <div class="ad-card-header" style="border:0; padding:0 0 16px;">
                        <h3>Today&rsquo;s Bookings</h3>
                        <a href="bookings.php" class="ad-btn ad-btn-soft" style="padding:6px 12px; font-size:12px;">View all</a>
                    </div>
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
                                <?php foreach (array_slice($today_bookings, 0, 5) as $booking):
                                    $st = $booking['status'] ?? 'pending';
                                    $chipClass = $st === 'completed' ? 'ad-chip-emerald'
                                                : ($st === 'confirmed' ? 'ad-chip-cobalt'
                                                : ($st === 'cancelled' ? 'ad-chip-crimson' : 'ad-chip-amber'));
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['guest_count']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['branch_name']); ?></td>
                                    <td>
                                        <span class="ad-chip <?php echo $chipClass; ?>"><span class="ad-chip-dot"></span><?php echo ucfirst($st); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="ad-empty">
                            <div class="ad-empty-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </div>
                            <h4>No bookings for today</h4>
                            <p>When guests reserve a table for today it will show up here in real time.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- RECENT MESSAGES -->
                <div class="ad-card ad-card-padded">
                    <div class="ad-card-header" style="border:0; padding:0 0 16px;">
                        <h3>Recent Messages</h3>
                        <a href="contact.php" class="ad-btn ad-btn-soft" style="padding:6px 12px; font-size:12px;">View inbox</a>
                    </div>
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
                        <div class="ad-empty">
                            <div class="ad-empty-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v12H4z M4 6l8 6 8-6" stroke="currentColor" stroke-width="2"/></svg>
                            </div>
                            <h4>No messages yet</h4>
                            <p>Customer messages from the contact form will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

<?php include 'footer.php'; ?>
