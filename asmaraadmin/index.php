<?php
// Fallback router for /asmaraadmin URLs when Apache rewrite rules do not apply.
// This allows the live site to serve admin pages from backend/admin without depending on .htaccess.

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');
$segments = explode('/', trim($requestUri, '/'));

$page = 'login';
if (isset($segments[1]) && $segments[0] === 'asmaraadmin') {
    $page = preg_replace('/\.php$/', '', $segments[1]);
    if ($page === '') {
        $page = 'login';
    }
}

$adminRoutes = [
    'login' => 'login.php',
    'dashboard' => 'index.php',
    'index' => 'index.php',
    'menu' => 'menu.php',
    'categories' => 'categories.php',
    'category' => 'categories.php',
    'bookings' => 'bookings.php',
    'booking' => 'bookings.php',
    'branches' => 'branches.php',
    'branch' => 'branches.php',
    'contact' => 'contact.php',
    'events' => 'events.php',
    'newsletter' => 'newsletter.php',
    'users' => 'users.php',
    'reports' => 'reports.php',
    'logout' => 'logout.php',
];

if (!isset($adminRoutes[$page])) {
    $page = 'login';
}

$target = __DIR__ . '/../backend/admin/' . $adminRoutes[$page];
if (is_file($target)) {
    include $target;
    exit();
}

http_response_code(404);
echo '404 Admin page not found.';
exit();
