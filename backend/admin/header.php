<?php
/**
 * Admin Dashboard Header Template
 */

require_once __DIR__ . '/../security/Auth.php';

Auth::requireLogin();

$current_user = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $page_title ?? 'Asmara Admin Dashboard'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/asmaraadmin/dashboard.css?v=<?php echo filemtime(__DIR__ . '/dashboard.css'); ?>">
</head>
<body>
    <!-- HEADER -->
    <header class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <a href="/asmaraadmin" class="header-brand">
                    <span class="brand-mark">A</span>
                    <span class="brand-text">Asmara Admin</span>
                </a>
            </div>

            <div class="header-center">
                <span class="header-greeting">Welcome back, <?php echo htmlspecialchars($current_user['username']); ?> 👋</span>
            </div>

            <div class="header-right">
                <div class="header-user">
                    <div class="user-badge"><?php echo strtoupper(substr($current_user['username'], 0, 1)); ?></div>
                    <span><?php echo htmlspecialchars($current_user['username']); ?></span>
                </div>
                <a href="?logout=1" class="btn-logout">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 4h-7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7M16 8l4 4-4 4M20 12h-10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Logout
                </a>
                <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Toggle menu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>
    </header>

    <div class="admin-layout">
        <!-- SIDEBAR — premium SaaS shape -->
        <aside class="admin-sidebar" id="admin-sidebar">

            <div class="sidebar-brand-block">
                <span class="sidebar-eyebrow">Restaurant</span>
                <span class="sidebar-venue">Asmara &middot; Admin</span>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-label">Overview</span>
                <nav class="sidebar-nav">
                    <div class="nav-item">
                        <a href="/asmaraadmin/index" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" title="Dashboard">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="3" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                    <rect x="13" y="3" width="8" height="5" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                    <rect x="13" y="10" width="8" height="11" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                    <rect x="3" y="13" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                </svg>
                            </span>
                            <span class="nav-label">Dashboard</span>
                        </a>
                    </div>
                </nav>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-label">Catalogue</span>
                <nav class="sidebar-nav">
                    <div class="nav-item">
                        <a href="/asmaraadmin/menu" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'menu.php' ? 'active' : ''; ?>" title="Menu Items">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M9 8h6M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    <circle cx="6.5" cy="8" r=".8" fill="currentColor"/>
                                    <circle cx="6.5" cy="12" r=".8" fill="currentColor"/>
                                    <circle cx="6.5" cy="16" r=".8" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="nav-label">Menu Items</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/asmaraadmin/categories" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>" title="Categories">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="3" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                    <rect x="13" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                    <rect x="3" y="13" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                    <rect x="13" y="13" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                </svg>
                            </span>
                            <span class="nav-label">Categories</span>
                        </a>
                    </div>
                </nav>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-label">Operations</span>
                <nav class="sidebar-nav">
                    <div class="nav-item">
                        <a href="/asmaraadmin/bookings" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?>" title="Bookings">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    <circle cx="8" cy="15" r="1" fill="currentColor"/>
                                    <circle cx="12" cy="15" r="1" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="nav-label">Bookings</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/asmaraadmin/events" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>" title="Events">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 3l1.6 5L19 9l-4.4 3 1.6 5-4.2-3-4.2 3 1.6-5L5 9l5.4-1Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                    <path d="M5 18v2M19 18v2M3 13h2M19 13h2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="nav-label">Events</span>
                        </a>
                    </div>
                </nav>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-label">Network</span>
                <nav class="sidebar-nav">
                    <div class="nav-item">
                        <a href="/asmaraadmin/branches" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'branches.php' ? 'active' : ''; ?>" title="Branches">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 22s7-7.5 7-12a7 7 0 1 0-14 0c0 4.5 7 12 7 12Z" stroke="currentColor" stroke-width="1.6"/>
                                    <circle cx="12" cy="10" r="2.6" stroke="currentColor" stroke-width="1.6"/>
                                </svg>
                            </span>
                            <span class="nav-label">Branches</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/asmaraadmin/contact" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : ''; ?>" title="Messages">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 6h16v12H4z M4 6l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="nav-label">Messages</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/asmaraadmin/newsletter" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'newsletter.php' ? 'active' : ''; ?>" title="Newsletter">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M7 9h10M7 13h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="nav-label">Newsletter</span>
                        </a>
                    </div>
                </nav>
            </div>

            <?php if (Auth::isAdmin()): ?>
            <div class="sidebar-section">
                <span class="sidebar-section-label">Settings</span>
                <nav class="sidebar-nav">
                    <div class="nav-item">
                        <a href="/asmaraadmin/users" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" title="Team">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle cx="9" cy="9" r="3.2" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M3 19a6 6 0 0 1 12 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    <path d="M16 11a3 3 0 1 0 0-6M19.5 18a4.5 4.5 0 0 0-3.7-2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="nav-label">Team</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/asmaraadmin/reports" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" title="Reports">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="nav-label">Reports</span>
                        </a>
                    </div>
                </nav>
            </div>
            <?php endif; ?>

            <div class="sidebar-footer">
                <div class="sidebar-user-pill">
                    <span class="avatar"><?php echo strtoupper(substr($current_user['username'], 0, 1)); ?></span>
                    <div class="meta">
                        <strong><?php echo htmlspecialchars($current_user['username']); ?></strong>
                        <span>Signed in</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="admin-main">
            <div class="page-content">
