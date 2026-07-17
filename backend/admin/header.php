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
    <link rel="stylesheet" href="/asmaraadmin/dashboard.css">
</head>
<body>
    <!-- HEADER -->
    <header class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <a href="/asmaraadmin/index" class="admin-logo">
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
                <a href="/asmaraadmin/logout" class="logout-btn">
                    <svg width="16" height="16" viewBox="0 0 256 256" fill="none" style="vertical-align: middle; margin-right: 4px;"><path d="M112 216a8 8 0 0 1-8 8H48a16 16 0 0 1-16-16V48a16 16 0 0 1 16-16h56a8 8 0 0 1 0 16H48v160h56a8 8 0 0 1 8 8Zm117.66-93.66-40-40a8 8 0 0 0-11.32 11.32L204.69 120H112a8 8 0 0 0 0 16h92.69l-26.35 26.34a8 8 0 0 0 11.32 11.32l40-40a8 8 0 0 0 0-11.32Z" fill="currentColor"/></svg>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <div class="admin-layout">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Toggle menu">
            <svg width="24" height="24" viewBox="0 0 256 256" fill="currentColor">
                <path d="M224 128a8 8 0 0 1-8 8H40a8 8 0 0 1 0-16h176a8 8 0 0 1 8 8ZM40 72h176a8 8 0 0 0 0-16H40a8 8 0 0 0 0 16Zm176 112H40a8 8 0 0 0 0 16h176a8 8 0 0 0 0-16Z"/>
            </svg>
        </button>
        
        <!-- SIDEBAR -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="/asmaraadmin/index" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 256 256" fill="currentColor">
                                <path d="M216 40H40a16 16 0 0 0-16 16v144a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a16 16 0 0 0-16-16ZM40 56h176v16H40Zm0 144V88h176v112Z"/>
                                <rect x="48" y="104" width="72" height="72" rx="4" opacity=".5"/>
                                <rect x="136" y="104" width="72" height="32" rx="4" opacity=".5"/>
                                <rect x="136" y="148" width="72" height="28" rx="4" opacity=".3"/>
                            </svg>
                        </span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="/asmaraadmin/menu" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'menu.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 256 256" fill="currentColor">
                                <path d="M224 128a104 104 0 1 1-104-104 104.11 104.11 0 0 1 104 104Z" opacity=".2"/>
                                <path d="M224 120h-88V32a8 8 0 0 0-16 0v88H32a8 8 0 0 0 0 16h88v88a8 8 0 0 0 16 0v-88h88a8 8 0 0 0 0-16Zm-96 88v-80h80a88 88 0 0 1-80 80Z"/>
                            </svg>
                        </span>
                        <span class="nav-label">Menu Items</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="/asmaraadmin/categories" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 256 256" fill="currentColor">
                                <path d="M104 40H56a16 16 0 0 0-16 16v48a16 16 0 0 0 16 16h48a16 16 0 0 0 16-16V56a16 16 0 0 0-16-16Zm0 112H56a16 16 0 0 0-16 16v48a16 16 0 0 0 16 16h48a16 16 0 0 0 16-16v-48a16 16 0 0 0-16-16Zm96-112h-48a16 16 0 0 0-16 16v48a16 16 0 0 0 16 16h48a16 16 0 0 0 16-16V56a16 16 0 0 0-16-16Zm0 112h-48a16 16 0 0 0-16 16v48a16 16 0 0 0 16 16h48a16 16 0 0 0 16-16v-48a16 16 0 0 0-16-16Z"/>
                            </svg>
                        </span>
                        <span class="nav-label">Categories</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="/asmaraadmin/bookings" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 256 256" fill="currentColor">
                                <path d="M208 32h-24V24a8 8 0 0 0-16 0v8H88V24a8 8 0 0 0-16 0v8H48a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h160a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16Zm0 176H48V48h24v8a8 8 0 0 0 16 0v-8h80v8a8 8 0 0 0 16 0v-8h24v32H48v128h160Z"/>
                                <circle cx="128" cy="132" r="12"/>
                                <circle cx="172" cy="132" r="12"/>
                                <circle cx="84" cy="172" r="12"/>
                                <circle cx="128" cy="172" r="12"/>
                                <circle cx="172" cy="172" r="12"/>
                            </svg>
                        </span>
                        <span class="nav-label">Bookings</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="/asmaraadmin/branches" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'branches.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 256 256" fill="currentColor">
                                <path d="M240 192h-8V48a16 16 0 0 0-16-16h-48a16 16 0 0 0-16 16v40H104V40a16 16 0 0 0-16-16H40a16 16 0 0 0-16 16v152h-8a8 8 0 0 0 0 16h224a8 8 0 0 0 0-16ZM168 48h48v144h-48Zm-64 56h48v88h-48Zm-64-64h48v152H40Zm60 96a8 8 0 0 1 8-8h16a8 8 0 0 1 0 16h-16a8 8 0 0 1-8-8Zm72 0a8 8 0 0 1 8-8h16a8 8 0 0 1 0 16h-16a8 8 0 0 1-8-8Z"/>
                            </svg>
                        </span>
                        <span class="nav-label">Branches</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="/asmaraadmin/contact" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 256 256" fill="currentColor">
                                <path d="M224 48H32a8 8 0 0 0-8 8v136a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a8 8 0 0 0-8-8Zm-96 85.15L52.57 64h150.86ZM98.71 128 40 181.81V74.19Zm11.84 10.85 12.58 11a8 8 0 0 0 10.74 0l12.58-11L210.39 192H45.61ZM157.29 128 216 74.19v107.62Z"/>
                            </svg>
                        </span>
                        <span class="nav-label">Messages</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="/asmaraadmin/events" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 256 256" fill="currentColor">
                                <path d="M216 40H40a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a16 16 0 0 0-16-16ZM40 56h176v40H40Zm176 160H40V112h176v104Z"/>
                                <circle cx="88" cy="148" r="12"/>
                                <circle cx="128" cy="148" r="12"/>
                                <circle cx="168" cy="148" r="12"/>
                                <circle cx="88" cy="188" r="12"/>
                                <circle cx="128" cy="188" r="12"/>
                                <circle cx="168" cy="188" r="12"/>
                            </svg>
                        </span>
                        <span class="nav-label">Events</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="/asmaraadmin/newsletter" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'newsletter.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 256 256" fill="currentColor">
                                <path d="M184 32H72a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h112a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16Zm-40 176h-32a8 8 0 0 1 0-16h32a8 8 0 0 1 0 16Zm40-32H72V48h112Z"/>
                            </svg>
                        </span>
                        <span class="nav-label">Newsletter</span>
                    </a>
                </div>

                <?php if (Auth::isAdmin()): ?>
                <div class="nav-item">
                    <a href="/asmaraadmin/users" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 256 256" fill="currentColor">
                                <path d="M117.25 157.92a60 60 0 1 0-66.5 0A95.83 95.83 0 0 0 3.53 195.63a8 8 0 1 0 13.4 8.74 80 80 0 0 1 134.14 0 8 8 0 0 0 13.4-8.74 95.83 95.83 0 0 0-47.22-37.71ZM40 108a44 44 0 1 1 44 44 44.05 44.05 0 0 1-44-44Zm160.29 48.41a8 8 0 0 1-11.92 10.71 80 80 0 0 0-134.14 0 8 8 0 1 1-13.4-8.74 95.83 95.83 0 0 1 47.22-37.71 60 60 0 1 1 66.5 0 95.83 95.83 0 0 1 47.22 37.71ZM196 108a44 44 0 1 1-44-44 44.05 44.05 0 0 1 44 44Z"/>
                            </svg>
                        </span>
                        <span class="nav-label">Users</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="/asmaraadmin/reports" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 256 256" fill="currentColor">
                                <path d="M232 208a8 8 0 0 1-8 8H32a8 8 0 0 1-8-8V48a8 8 0 0 1 16 0v108.69l50.34-50.35a8 8 0 0 1 11.32 0L128 132.69l58.34-58.35a8 8 0 0 1 11.32 11.32l-64 64a8 8 0 0 1-11.32 0L104 131.31l-64 64V200h184a8 8 0 0 1 8 8Z"/>
                            </svg>
                        </span>
                        <span class="nav-label">Reports</span>
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="admin-main">
            <div class="page-content">
