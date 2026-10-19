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
require_once __DIR__ . '/../database/BranchRepository.php';
require_once __DIR__ . '/../data/GoogleAnalyticsService.php';
require_once __DIR__ . '/../security/Auth.php';

Auth::requireLogin();

$bookingRepo = new BookingRepository();
$menuRepo = new MenuRepository();
$contactRepo = new ContactRepository();
$branchRepo = new BranchRepository();
$gaService = GoogleAnalyticsService::fromEnvironment();
$gaStartDate = $_GET['ga_start'] ?? '30daysAgo';
$gaEndDate = $_GET['ga_end'] ?? 'today';
$gaRangeLabel = ($gaStartDate === '30daysAgo' && $gaEndDate === 'today')
    ? 'last 30 days'
    : $gaStartDate . ' to ' . $gaEndDate;
$gaData = $gaService->getDashboardData($gaStartDate, $gaEndDate);

if (!function_exists('formatAnalyticsMetric')) {
    function formatAnalyticsMetric($metricName, $value) {
        if ($value === null || $value === '') {
            return '0';
        }

        if (in_array($metricName, ['bounceRate', 'engagementRate'], true)) {
            return number_format(((float)$value) * 100, 1) . '%';
        }

        if (is_float($value) || (is_numeric($value) && strpos((string)$value, '.') !== false)) {
            return number_format((float)$value, 1);
        }

        return (string)$value;
    }
}

// ---- Core report data ------------------------------------------------
$total_bookings      = (int)$bookingRepo->count();
$pending_bookings    = (int)$bookingRepo->countByStatus('pending');
$confirmed_bookings  = (int)$bookingRepo->countByStatus('confirmed');
$completed_bookings  = (int)$bookingRepo->countByStatus('completed');
$cancelled_bookings  = (int)$bookingRepo->countByStatus('cancelled');

$total_menu_items    = (int)$menuRepo->count();
$total_inquiries     = (int)$contactRepo->count();
$new_inquiries       = (int)$contactRepo->getNewCount();
$replied_inquiries   = count($contactRepo->getByStatus('replied'));
$branches            = $branchRepo->getAll() ?? [];
$total_branches      = is_array($branches) ? count($branches) : 0;

$booking_confirmation_rate = $total_bookings > 0 ? round(($confirmed_bookings / $total_bookings) * 100, 1) : 0;
$booking_completion_rate   = $total_bookings > 0 ? round(($completed_bookings / $total_bookings) * 100, 1) : 0;
$booking_cancellation_rate = $total_bookings > 0 ? round(($cancelled_bookings / $total_bookings) * 100, 1) : 0;
$inquiry_response_rate      = $total_inquiries > 0 ? round(($replied_inquiries / $total_inquiries) * 100, 1) : 0;

// ---- Chart data — bookings per day, last 30 days ----------------------
$all_bookings = $bookingRepo->getAll(null, 5000, 0) ?? [];
$today = new DateTimeImmutable('today');
$timeline = []; // [date => total_bookings_int]
for ($i = 29; $i >= 0; $i--) {
    $d = $today->modify("-{$i} days")->format('Y-m-d');
    $timeline[$d] = 0;
}
foreach ($all_bookings as $b) {
    $d = substr((string)($b['booking_date'] ?? ''), 0, 10);
    if (isset($timeline[$d])) $timeline[$d]++;
}
$timeline_labels = [];
$timeline_values = [];
foreach ($timeline as $d => $v) {
    $timeline_labels[] = (int)substr($d, 8, 2); // day-of-month
    $timeline_values[] = $v;
}
$timeline_max = max(1, max($timeline_values));

// ---- Chart data — bookings by branch -----------------------------------
$by_branch = [];
foreach ($all_bookings as $b) {
    $name = $b['branch_name'] ?? ('Branch #' . ($b['branch_id'] ?? '0'));
    $bid = (int)($b['branch_id'] ?? 0);
    if (!isset($by_branch[$bid])) {
        $by_branch[$bid] = ['name' => $name, 'count' => 0, 'guests' => 0];
    }
    $by_branch[$bid]['count']++;
    $by_branch[$bid]['guests'] += (int)($b['guest_count'] ?? 0);
}
// Sort by count desc
uasort($by_branch, fn($a, $b) => $b['count'] <=> $a['count']);
$by_branch_max = max(1, max(array_column($by_branch, 'count') ?: [1]));

// ---- Chart data — guest count by branch (capacity utilization) -------
$guests_by_branch = [];
foreach ($branches as $br) {
    $bid = (int)($br['id']);
    $guests_by_branch[$bid] = [
        'name' => $br['name'] ?? ('Branch ' . $bid),
        'capacity' => (int)($br['capacity'] ?? 0),
        'booked' => isset($by_branch[$bid]) ? $by_branch[$bid]['guests'] : 0,
    ];
}

// ---- Chart data — booking-status donut --------------------------------
$status_breakdown = [
    ['key' => 'confirmed', 'label' => 'Confirmed', 'count' => $confirmed_bookings, 'color' => '#2256d4'],
    ['key' => 'pending',   'label' => 'Pending',   'count' => $pending_bookings,   'color' => '#c97a16'],
    ['key' => 'completed', 'label' => 'Completed', 'count' => $completed_bookings, 'color' => '#0f8a5f'],
    ['key' => 'cancelled', 'label' => 'Cancelled', 'count' => $cancelled_bookings, 'color' => '#c81d3f'],
];
$status_total = max(1, array_sum(array_column($status_breakdown, 'count')));

// Cumulative angles for the donut (start at -90°)
$donut_arcs = [];
$donut_acc = -90;
foreach ($status_breakdown as $slice) {
    if ($slice['count'] <= 0) continue;
    $pct = $slice['count'] / $status_total;
    $sweep = 360 * $pct;
    $a1 = $donut_acc;
    $a2 = $donut_acc + $sweep;
    $donut_arcs[] = [
        'label' => $slice['label'],
        'count' => $slice['count'],
        'pct'   => round($pct * 100, 1),
        'color' => $slice['color'],
        'a1'    => $a1,
        'a2'    => $a2,
    ];
    $donut_acc = $a2;
}
?>
<?php include 'header.php'; ?>

            <div class="ad-page-header">
                <span class="ad-page-eyebrow">✦ Insights</span>
                <h1>Reports &amp; Analytics</h1>
                <p>Business performance, booking trends, and customer engagement across all Asmara locations.</p>
                <div class="ad-page-header-actions">
                    <a href="bookings.php" class="ad-btn ad-btn-ghost">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        Bookings
                    </a>
                    <a href="contact.php" class="ad-btn ad-btn-ghost">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v12H4z M4 6l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                        Messages
                    </a>
                    <button type="button" class="ad-btn ad-btn-primary" onclick="window.print()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4v-7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v7h-2M6 14h12v7H6z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                        Export PDF
                    </button>
                </div>
            </div>

            <div class="page-content">

                <!-- KEY METRICS (unified ad-stat-tile language) -->
                <div class="ad-stat-row" role="list">
                    <a href="bookings.php" class="ad-stat-tile" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo number_format($total_bookings); ?></div>
                            <div class="ad-stat-label">Total Bookings</div>
                        </div>
                    </a>
                    <a href="bookings.php?filter=confirmed" class="ad-stat-tile is-cobalt" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo number_format($confirmed_bookings); ?></div>
                            <div class="ad-stat-label">Confirmed &middot; <?php echo $booking_confirmation_rate; ?>%</div>
                        </div>
                    </a>
                    <a href="bookings.php?filter=completed" class="ad-stat-tile is-emerald" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo number_format($completed_bookings); ?></div>
                            <div class="ad-stat-label">Completed &middot; <?php echo $booking_completion_rate; ?>%</div>
                        </div>
                    </a>
                    <a href="bookings.php?filter=pending" class="ad-stat-tile is-amber" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 7v6l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo number_format($pending_bookings); ?></div>
                            <div class="ad-stat-label">Pending</div>
                        </div>
                    </a>
                    <a href="bookings.php?filter=cancelled" class="ad-stat-tile is-rose" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo number_format($cancelled_bookings); ?></div>
                            <div class="ad-stat-label">Cancelled &middot; <?php echo $booking_cancellation_rate; ?>%</div>
                        </div>
                    </a>
                    <a href="contact.php" class="ad-stat-tile is-cobalt" role="listitem" style="text-decoration:none;color:inherit;">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v12H4z M4 6l8 6 8-6" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo number_format($total_inquiries); ?></div>
                            <div class="ad-stat-label">Inquiries</div>
                        </div>
                    </a>
                    <div class="ad-stat-tile is-emerald" role="listitem">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 16.2l-3.5-3.5L4 14.2 9 19.2 20 8.2 18.6 6.8z" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo number_format($inquiry_response_rate); ?>%</div>
                            <div class="ad-stat-label">Inquiries Replied</div>
                        </div>
                    </div>
                    <div class="ad-stat-tile is-rose" role="listitem">
                        <div class="ad-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 8h16v12H4zM4 4h16M9 12h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div>
                            <div class="ad-stat-value"><?php echo number_format($total_menu_items); ?></div>
                            <div class="ad-stat-label">Menu Items</div>
                        </div>
                    </div>
                </div>

                <!-- CHARTS GRID (row 1) — line + donut -->
                <div class="ad-charts-row ad-charts-row-2">
                    <article class="ad-card ad-card-padded ad-chart-card">
                        <header class="ad-chart-head">
                            <div>
                                <h3>Bookings over time</h3>
                                <p class="ad-chart-sub">Last 30 days &middot; total <strong><?php echo number_format(array_sum($timeline_values)); ?></strong> bookings</p>
                            </div>
                            <span class="ad-chip ad-chip-cobalt"><span class="ad-chip-dot"></span>Line</span>
                        </header>
                        <?php
                            // Render SVG line + filled area. Viewbox 1000x240, padding 24 left / 16 right.
                            $width = 1000; $height = 240; $padL = 24; $padR = 16; $padT = 16; $padB = 28;
                            $plotW = $width - $padL - $padR;
                            $plotH = $height - $padT - $padB;
                            $n = count($timeline_values);
                            $step = $n > 1 ? $plotW / ($n - 1) : 0;
                            $points = '';
                            $area = '';
                            foreach ($timeline_values as $i => $v) {
                                $x = $padL + ($step * $i);
                                $y = $padT + $plotH - (($v / $timeline_max) * $plotH);
                                $points .= ($i === 0 ? '' : ' ') . round($x, 1) . ',' . round($y, 1);
                            }
                            $area = $points . ' ' . ($padL + $plotW) . ',' . ($padT + $plotH) . ' ' . $padL . ',' . ($padT + $plotH);
                            // X-axis labels (every 5th day)
                            $xTicks = [];
                            foreach ($timeline_labels as $i => $day) {
                                if ($i % 5 === 0 || $i === $n - 1) {
                                    $x = $padL + ($step * $i);
                                    $xTicks[] = ['x' => $x, 'day' => $day];
                                }
                            }
                        ?>
                        <svg class="ad-chart-svg" viewBox="0 0 <?php echo $width; ?> <?php echo $height; ?>" preserveAspectRatio="none" role="img" aria-label="Bookings last 30 days">
                            <defs>
                                <linearGradient id="adLineFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#ed174b" stop-opacity="0.20"/>
                                    <stop offset="100%" stop-color="#ed174b" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <!-- Y gridlines (4 ticks) -->
                            <?php for ($i = 1; $i <= 4; $i++):
                                $y = $padT + ($plotH * $i / 4); ?>
                                <line x1="<?php echo $padL; ?>" y1="<?php echo $y; ?>" x2="<?php echo $padL + $plotW; ?>" y2="<?php echo $y; ?>" stroke="#ece3da" stroke-width="1" stroke-dasharray="3 4"/>
                            <?php endfor; ?>
                            <!-- Filled area -->
                            <polygon points="<?php echo $area; ?>" fill="url(#adLineFill)"/>
                            <!-- Line -->
                            <polyline points="<?php echo $points; ?>" fill="none" stroke="#ed174b" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round"/>
                            <!-- Points (every 5th for density) -->
                            <?php foreach ($timeline_values as $i => $v):
                                $x = $padL + ($step * $i);
                                $y = $padT + $plotH - (($v / $timeline_max) * $plotH); ?>
                                <circle cx="<?php echo $x; ?>" cy="<?php echo $y; ?>" r="<?php echo $i % 5 === 0 ? 3 : 1.6; ?>" fill="#fff" stroke="#ed174b" stroke-width="<?php echo $i % 5 === 0 ? 2 : 1; ?>"/>
                            <?php endforeach; ?>
                            <!-- X-axis labels -->
                            <?php foreach ($xTicks as $t): ?>
                                <text x="<?php echo $t['x']; ?>" y="<?php echo $padT + $plotH + 18; ?>" text-anchor="middle" font-family="Plus Jakarta Sans, sans-serif" font-size="11" fill="#8b8597"><?php echo $t['day']; ?></text>
                            <?php endforeach; ?>
                        </svg>
                    </article>

                    <article class="ad-card ad-card-padded ad-chart-card ad-chart-card-donut">
                        <header class="ad-chart-head">
                            <div>
                                <h3>Booking status mix</h3>
                                <p class="ad-chart-sub"><?php echo number_format($total_bookings); ?> total bookings across 4 status stages</p>
                            </div>
                            <span class="ad-chip ad-chip-rose"><span class="ad-chip-dot"></span>Donut</span>
                        </header>
                        <div class="ad-donut-wrap">
                            <?php
                                // Donut chart, viewbox 200x200, radius 70, stroke 22.
                                $cx = 100; $cy = 100; $r = 70; $stroke = 22;
                                $circumference = 2 * M_PI * $r;
                            ?>
                            <svg class="ad-donut-svg" viewBox="0 0 200 200" role="img" aria-label="Booking status donut chart">
                                <circle cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="<?php echo $r; ?>" fill="none" stroke="#fbf8f5" stroke-width="<?php echo $stroke; ?>"/>
                                <circle cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="<?php echo $r; ?>" fill="none" stroke="#ed174b" stroke-width="<?php echo $stroke; ?>" stroke-dasharray="0 <?php echo $circumference; ?>" transform="rotate(-90 <?php echo $cx; ?> <?php echo $cy; ?>)"/>
                                <?php
                                    $offset = 0;
                                    foreach ($status_breakdown as $slice):
                                        if ($slice['count'] <= 0) continue;
                                        $dashLen = ($slice['count'] / $status_total) * $circumference;
                                        $gap = $circumference - $dashLen; ?>
                                        <circle cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="<?php echo $r; ?>" fill="none" stroke="<?php echo $slice['color']; ?>" stroke-width="<?php echo $stroke; ?>" stroke-dasharray="<?php echo $dashLen; ?> <?php echo $gap; ?>" stroke-dashoffset="<?php echo -$offset; ?>" transform="rotate(-90 <?php echo $cx; ?> <?php echo $cy; ?>)"/>
                                        <?php $offset += $dashLen; ?>
                                    <?php endforeach; ?>
                            </svg>
                            <div class="ad-donut-center">
                                <div class="ad-donut-center-value"><?php echo number_format($total_bookings); ?></div>
                                <div class="ad-donut-center-label">Total Bookings</div>
                            </div>
                        </div>
                        <ul class="ad-donut-legend">
                            <?php foreach ($status_breakdown as $slice):
                                $pct = $total_bookings > 0 ? round(($slice['count'] / $total_bookings) * 100, 1) : 0; ?>
                                <li>
                                    <span class="ad-legend-dot" style="background:<?php echo $slice['color']; ?>"></span>
                                    <span class="ad-legend-label"><?php echo htmlspecialchars($slice['label']); ?></span>
                                    <span class="ad-legend-value"><?php echo number_format($slice['count']); ?> <small>(<?php echo $pct; ?>%)</small></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                </div>

                <!-- CHARTS GRID (row 2) — branch bars + capacity -->
                <div class="ad-charts-row ad-charts-row-2">
                    <article class="ad-card ad-card-padded ad-chart-card">
                        <header class="ad-chart-head">
                            <div>
                                <h3>Bookings by branch</h3>
                                <p class="ad-chart-sub">How each restaurant location is performing</p>
                            </div>
                            <span class="ad-chip ad-chip-emerald"><span class="ad-chip-dot"></span>Bars</span>
                        </header>
                        <div class="ad-bars">
                            <?php
                                $bars_count = max(1, count($by_branch));
                                foreach ($by_branch as $br):
                                    $pct = $br['count'] / $by_branch_max;
                                    $pct_vis = max(2, round($pct * 100, 1));
                                    $rank_label = '#' . (array_search(array_keys($by_branch)[array_search($br, $by_branch)], array_keys($by_branch)) + 1); ?>
                                <div class="ad-bar-row">
                                    <div class="ad-bar-label">
                                        <span><?php echo htmlspecialchars($br['name']); ?></span>
                                        <strong><?php echo number_format($br['count']); ?> <small>bookings</small></strong>
                                    </div>
                                    <div class="ad-bar-track">
                                        <span class="ad-bar-fill" style="width: <?php echo $pct_vis; ?>%"></span>
                                    </div>
                                    <div class="ad-bar-meta"><?php echo $br['guests']; ?> guests</div>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($by_branch) || count(array_filter($by_branch, fn($b) => $b['count'] > 0)) === 0): ?>
                                    <div class="ad-empty" style="margin: 24px 0;">
                                        <div class="ad-empty-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></div>
                                        <h4>No bookings yet</h4>
                                        <p>Once guests start reserving tables, branch performance will appear here.</p>
                                    </div>
                                <?php endif; ?>
                        </div>
                    </article>

                    <article class="ad-card ad-card-padded ad-chart-card">
                        <header class="ad-chart-head">
                            <div>
                                <h3>Capacity vs booked guests</h3>
                                <p class="ad-chart-sub">Per-branch utilization based on current reservations</p>
                            </div>
                            <span class="ad-chip ad-chip-amber"><span class="ad-chip-dot"></span>Utilization</span>
                        </header>
                        <div class="ad-capacity-list">
                            <?php
                                foreach ($guests_by_branch as $cb):
                                    $cap = max(1, $cb['capacity']);
                                    $util = min(100, round(($cb['booked'] / $cap) * 100, 1));
                                    $over = $cb['booked'] > $cap;
                                    $utilClass = $over ? 'is-over' : ($util >= 75 ? 'is-high' : ''); ?>
                                <div class="ad-capacity-row">
                                    <div class="ad-capacity-head">
                                        <strong><?php echo htmlspecialchars($cb['name']); ?></strong>
                                        <span>
                                            <?php echo number_format($cb['booked']); ?> /
                                            <?php echo number_format($cb['capacity']); ?> seats
                                        </span>
                                    </div>
                                    <div class="ad-bar-track">
                                        <span class="ad-bar-fill ad-bar-fill-<?php echo $utilClass; ?>" style="width: <?php echo max(2, $util); ?>%"></span>
                                    </div>
                                    <div class="ad-capacity-foot">
                                        <span><?php echo $util; ?>% utilized</span>
                                        <?php if ($over): ?>
                                            <span class="ad-chip ad-chip-crimson"><span class="ad-chip-dot"></span>Over capacity</span>
                                        <?php elseif ($util >= 75): ?>
                                            <span class="ad-chip ad-chip-amber"><span class="ad-chip-dot"></span>High</span>
                                        <?php else: ?>
                                            <span class="ad-chip ad-chip-emerald"><span class="ad-chip-dot"></span>Plenty of room</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($guests_by_branch)): ?>
                                <div class="ad-empty" style="margin: 24px 0;">
                                    <div class="ad-empty-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/></svg></div>
                                    <h4>No branch data</h4>
                                    <p>Add branches under <a href="branches.php">Branches</a> to enable capacity reports.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>

                <!-- GOOGLE ANALYTICS -->
                <?php if (!empty($gaData['enabled'])): ?>
                <?php
                    $gaTotals = $gaData['overview']['totals'] ?? [];
                    $gaTop = $gaData['top_pages']['rows'] ?? [];
                    $gaSrc = $gaData['traffic_sources']['rows'] ?? [];
                    $gaAud = $gaData['audience']['rows'] ?? [];
                ?>
                <article class="ad-card ad-card-padded" style="margin-top: 24px;">
                    <header class="ad-chart-head">
                        <div>
                            <span class="ad-page-eyebrow" style="margin-bottom:8px;">Google Analytics</span>
                            <h3>Website traffic</h3>
                            <p class="ad-chart-sub">Live property <strong><?php echo htmlspecialchars($gaData['property_id']); ?></strong> &middot; <?php echo htmlspecialchars($gaRangeLabel); ?></p>
                        </div>
                        <span class="ad-chip ad-chip-amber"><span class="ad-chip-dot"></span>GA4</span>
                    </header>

                    <div class="ad-stat-row" role="list" style="margin-bottom: 18px;">
                        <div class="ad-stat-tile" role="listitem">
                            <div class="ad-stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="9" r="1" fill="currentColor"/><path d="M11 13h2v5h-2z" fill="currentColor"/></svg></div>
                            <div><div class="ad-stat-value"><?php echo formatAnalyticsMetric('activeUsers', $gaTotals['activeUsers'] ?? 0); ?></div><div class="ad-stat-label">Active Users</div></div>
                        </div>
                        <div class="ad-stat-tile is-cobalt" role="listitem">
                            <div class="ad-stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 4h16v14H5.17L4 19.17V4z" stroke="currentColor" stroke-width="1.7"/><path d="M8 8h8v2H8zm0 4h6v2H8z" stroke="currentColor" stroke-width="1.7"/></svg></div>
                            <div><div class="ad-stat-value"><?php echo formatAnalyticsMetric('sessions', $gaTotals['sessions'] ?? 0); ?></div><div class="ad-stat-label">Sessions</div></div>
                        </div>
                        <div class="ad-stat-tile is-emerald" role="listitem">
                            <div class="ad-stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="9" cy="9" r="3" stroke="currentColor" stroke-width="1.7"/><path d="M3 19a6 6 0 0 1 12 0M16 11a3 3 0 1 0 0-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div>
                            <div><div class="ad-stat-value"><?php echo formatAnalyticsMetric('newUsers', $gaTotals['newUsers'] ?? 0); ?></div><div class="ad-stat-label">New Users</div></div>
                        </div>
                        <div class="ad-stat-tile is-amber" role="listitem">
                            <div class="ad-stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 11h6V4H4zm0 9h6v-7H4zm8 0h8V13h-8zm0-9h8V4h-8z" stroke="currentColor" stroke-width="1.7"/></svg></div>
                            <div><div class="ad-stat-value"><?php echo formatAnalyticsMetric('screenPageViews', $gaTotals['screenPageViews'] ?? 0); ?></div><div class="ad-stat-label">Page Views</div></div>
                        </div>
                        <div class="ad-stat-tile is-rose" role="listitem">
                            <div class="ad-stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M12 5v14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div>
                            <div><div class="ad-stat-value"><?php echo formatAnalyticsMetric('engagementRate', $gaTotals['engagementRate'] ?? 0); ?></div><div class="ad-stat-label">Engagement</div></div>
                        </div>
                        <div class="ad-stat-tile" role="listitem">
                            <div class="ad-stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 17l6-6 4 4 6-6" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></div>
                            <div><div class="ad-stat-value"><?php echo formatAnalyticsMetric('bounceRate', $gaTotals['bounceRate'] ?? 0); ?></div><div class="ad-stat-label">Bounce Rate</div></div>
                        </div>
                    </div>

                    <div class="ad-charts-row ad-charts-row-2">
                        <article class="ad-card ad-card-quiet ad-card-padded ad-chart-card">
                            <header class="ad-chart-head">
                                <div>
                                    <h3>Top pages</h3>
                                    <p class="ad-chart-sub">Most viewed pages on the website</p>
                                </div>
                                <span class="ad-chip ad-chip-cobalt"><span class="ad-chip-dot"></span>Table</span>
                            </header>
                            <div style="overflow-x:auto;">
                                <table class="data-table">
                                    <thead><tr><th>Page</th><th>Views</th><th>Users</th><th>Events</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($gaTop as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['dimensions']['fullPageUrl'] ?? ''); ?></td>
                                            <td><?php echo formatAnalyticsMetric('screenPageViews', $row['metrics']['screenPageViews'] ?? 0); ?></td>
                                            <td><?php echo formatAnalyticsMetric('activeUsers', $row['metrics']['activeUsers'] ?? 0); ?></td>
                                            <td><?php echo formatAnalyticsMetric('eventCount', $row['metrics']['eventCount'] ?? 0); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </article>

                        <article class="ad-card ad-card-quiet ad-card-padded ad-chart-card">
                            <header class="ad-chart-head">
                                <div>
                                    <h3>Traffic sources</h3>
                                    <p class="ad-chart-sub">Where visitors come from</p>
                                </div>
                                <span class="ad-chip ad-chip-emerald"><span class="ad-chip-dot"></span>Table</span>
                            </header>
                            <div style="overflow-x:auto;">
                                <table class="data-table">
                                    <thead><tr><th>Channel</th><th>Sessions</th><th>Users</th><th>Engagement</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($gaSrc as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['dimensions']['sessionDefaultChannelGroup'] ?? ''); ?></td>
                                            <td><?php echo formatAnalyticsMetric('sessions', $row['metrics']['sessions'] ?? 0); ?></td>
                                            <td><?php echo formatAnalyticsMetric('activeUsers', $row['metrics']['activeUsers'] ?? 0); ?></td>
                                            <td><?php echo formatAnalyticsMetric('engagementRate', $row['metrics']['engagementRate'] ?? 0); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>
                </article>
                <?php else: ?>
                <article class="ad-card ad-card-padded ad-card-tinted" style="margin-top: 24px;">
                    <header class="ad-chart-head">
                        <div>
                            <span class="ad-page-eyebrow" style="margin-bottom:8px;">Google Analytics</span>
                            <h3>Website traffic</h3>
                            <p class="ad-chart-sub">Connect GA4 credentials to unlock sessions, page views, traffic sources and audience data here.</p>
                        </div>
                        <span class="ad-chip ad-chip-ink"><span class="ad-chip-dot"></span>Not connected</span>
                    </header>
                    <?php if (!empty($gaData['error'])): ?>
                        <div class="alert alert-error" style="margin: 0;"><?php echo htmlspecialchars($gaData['error']); ?></div>
                    <?php endif; ?>
                </article>
                <?php endif; ?>

                <!-- BOOKING STATUS BREAKDOWN TABLE -->
                <article class="ad-card ad-card-padded" style="margin-top: 24px;">
                    <header class="ad-chart-head" style="margin-bottom: 18px;">
                        <div>
                            <h3>Status breakdown detail</h3>
                            <p class="ad-chart-sub">Counts and visual share of every status</p>
                        </div>
                        <span class="ad-chip ad-chip-rose"><span class="ad-chip-dot"></span>Detail</span>
                    </header>
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
                            <?php foreach ($status_breakdown as $slice):
                                $pct = $total_bookings > 0 ? round(($slice['count'] / $total_bookings) * 100, 1) : 0; ?>
                                <tr>
                                    <td><span class="ad-chip" style="background: <?php echo $slice['color']; ?>1A; color: <?php echo $slice['color']; ?>; border-color: <?php echo $slice['color']; ?>40"><span class="ad-chip-dot"></span><?php echo htmlspecialchars($slice['label']); ?></span></td>
                                    <td><strong><?php echo number_format($slice['count']); ?></strong></td>
                                    <td><?php echo $pct; ?>%</td>
                                    <td>
                                        <div class="ad-bar-track" style="width: 220px;">
                                            <span class="ad-bar-fill" style="width: <?php echo max(2, $pct); ?>%; background: <?php echo $slice['color']; ?>;"></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>

                <!-- QUICK ACTIONS -->
                <div class="ad-card ad-card-padded" style="margin-top: 24px;">
                    <div class="ad-card-header" style="border:0; padding:0 0 14px;">
                        <h3>Quick access</h3>
                    </div>
                    <div class="action-buttons" style="display:flex; flex-wrap:wrap; gap:10px;">
                        <a href="bookings.php" class="ad-btn ad-btn-primary">View All Bookings</a>
                        <a href="contact.php" class="ad-btn ad-btn-ghost">View Inquiries</a>
                        <a href="menu.php" class="ad-btn ad-btn-ghost">Manage Menu</a>
                        <a href="events.php" class="ad-btn ad-btn-ghost">Manage Events</a>
                    </div>
                </div>

            </div>

<?php include 'footer.php'; ?>
