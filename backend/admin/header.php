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
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <!-- HEADER -->
    <header class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <a href="index.php" class="admin-logo">
                    <svg width="20" height="20" viewBox="0 0 256 256" fill="none" style="vertical-align: middle; margin-right: 8px;"><path d="M224 104a8 8 0 0 0-8 8v16h-56V64h16a8 8 0 0 0 0-16H80a8 8 0 0 0 0 16h16v64H40v-16a8 8 0 0 0-16 0v80a8 8 0 0 0 16 0v-48h56v48a8 8 0 0 0 16 0V64h32v128a8 8 0 0 0 16 0v-48h56v48a8 8 0 0 0 16 0v-80a8 8 0 0 0-8-8Z" fill="currentColor"/></svg>
                    Asmara Admin
                </a>
                <span class="breadcrumb">Welcome back, <?php echo htmlspecialchars($current_user['username']); ?></span>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <span><?php echo htmlspecialchars($current_user['username']); ?></span>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <svg width="16" height="16" viewBox="0 0 256 256" fill="none" style="vertical-align: middle; margin-right: 4px;"><path d="M112 216a8 8 0 0 1-8 8H48a16 16 0 0 1-16-16V48a16 16 0 0 1 16-16h56a8 8 0 0 1 0 16H48v160h56a8 8 0 0 1 8 8Zm117.66-93.66-40-40a8 8 0 0 0-11.32 11.32L204.69 120H112a8 8 0 0 0 0 16h92.69l-26.35 26.34a8 8 0 0 0 11.32 11.32l40-40a8 8 0 0 0 0-11.32Z" fill="currentColor"/></svg>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <div class="admin-layout">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 256 256" fill="none"><path d="M216 40H40a16 16 0 0 0-16 16v144a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a16 16 0 0 0-16-16ZM40 56h176v16H40Zm0 144V88h176v112Z" fill="currentColor"/><rect x="48" y="104" width="72" height="72" rx="4" fill="currentColor" opacity=".5"/><rect x="136" y="104" width="72" height="32" rx="4" fill="currentColor" opacity=".5"/><rect x="136" y="148" width="72" height="28" rx="4" fill="currentColor" opacity=".3"/></svg>
                        </span>
                        Dashboard
                    </a>
                </div>

                <div class="nav-item">
                    <a href="menu.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'menu.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 256 256" fill="none"><path d="M200 32H56a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h144a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16Zm0 176H56V48h144ZM80 80h96a8 8 0 0 1 0 16H80a8 8 0 0 1 0-16Zm0 40h96a8 8 0 0 1 0 16H80a8 8 0 0 1 0-16Zm0 40h64a8 8 0 0 1 0 16H80a8 8 0 0 1 0-16Z" fill="currentColor"/></svg>
                        </span>
                        Menu Items
                    </a>
                </div>

                <div class="nav-item">
                    <a href="bookings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 256 256" fill="none"><path d="M208 32h-24V24a8 8 0 0 0-16 0v8H88V24a8 8 0 0 0-16 0v8H48a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h160a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16Zm0 176H48V48h24v8a8 8 0 0 0 16 0v-8h80v8a8 8 0 0 0 16 0v-8h24Zm-68-76a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm44 0a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm-88 40a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm44 0a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm44 0a12 12 0 1 1-12-12 12 12 0 0 1 12 12Z" fill="currentColor"/></svg>
                        </span>
                        Bookings
                    </a>
                </div>

                <div class="nav-item">
                    <a href="branches.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'branches.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 256 256" fill="none"><path d="M232 224h-24V72a8 8 0 0 0-8-8h-56V32a8 8 0 0 0-12.27-6.74l-80 52A8 8 0 0 0 48 84v140H24a8 8 0 0 0 0 16h208a8 8 0 0 0 0-16ZM64 224V89.34L136 42v182Zm128 0h-40V80h40ZM96 120h16a8 8 0 0 1 0 16H96a8 8 0 0 1 0-16Zm-16 56a8 8 0 0 1 8-8h16a8 8 0 0 1 0 16H88a8 8 0 0 1-8-8Z" fill="currentColor"/></svg>
                        </span>
                        Branches
                    </a>
                </div>

                <div class="nav-item">
                    <a href="contact.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 256 256" fill="none"><path d="M224 48H32a8 8 0 0 0-8 8v136a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a8 8 0 0 0-8-8Zm-96 85.15L52.57 64h150.86ZM98.71 128 40 181.81V74.19Zm11.84 10.85 12.58 11a8 8 0 0 0 10.74 0l12.58-11L210.39 192H45.61ZM157.29 128 216 74.19v107.62Z" fill="currentColor"/></svg>
                        </span>
                        Messages
                    </a>
                </div>

                <div class="nav-item">
                    <a href="events.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 256 256" fill="none"><path d="M208 32h-24V24a8 8 0 0 0-16 0v8H88V24a8 8 0 0 0-16 0v8H48a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h160a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16Zm0 176H48V48h24v8a8 8 0 0 0 16 0v-8h80v8a8 8 0 0 0 16 0v-8h24Zm-68-76a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm44 0a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm-88 40a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm44 0a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm44 0a12 12 0 1 1-12-12 12 12 0 0 1 12 12Z" fill="currentColor"/></svg>
                        </span>
                        Events
                    </a>
                </div>

                <?php if (Auth::isAdmin()): ?>
                <div class="nav-item">
                    <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 256 256" fill="none"><path d="M117.25 157.92a60 60 0 1 0-66.5 0A95.83 95.83 0 0 0 3.53 195.63a8 8 0 1 0 13.4 8.74 80 80 0 0 1 134.14 0 8 8 0 0 0 13.4-8.74 95.83 95.83 0 0 0-47.22-37.71ZM40 108a44 44 0 1 1 44 44 44.05 44.05 0 0 1-44-44Zm210.06 87.63a95.83 95.83 0 0 0-47.22-37.71 60 60 0 0 0-33.47-103.4 8 8 0 1 0-3.81 15.53A44 44 0 0 1 212 108a44.05 44.05 0 0 1-20.78 37.35 8 8 0 0 0 0 13.7 95.83 95.83 0 0 1 47.22 37.71 8 8 0 0 0 11.62-10.13Z" fill="currentColor"/></svg>
                        </span>
                        Users
                    </a>
                </div>

                <div class="nav-item">
                    <a href="reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 256 256" fill="none"><path d="M232 208a8 8 0 0 1-8 8H32a8 8 0 0 1-8-8V48a8 8 0 0 1 16 0v108.69l58.34-58.35a8 8 0 0 1 11.32 0L128 116.69l58.34-58.35a8 8 0 0 1 11.32 11.32l-64 64a8 8 0 0 1-11.32 0L104 115.31 40 179.31V200h184a8 8 0 0 1 8 8Z" fill="currentColor"/></svg>
                        </span>
                        Reports
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="admin-main">
