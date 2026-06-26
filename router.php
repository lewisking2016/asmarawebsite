<?php
/**
 * Router for PHP Built-in Server
 * This routes requests to the correct files
 */

$requested_file = $_SERVER["SCRIPT_FILENAME"];

// If the requested file exists and is a real file (static files, images, css, js), serve it
if (is_file($requested_file)) {
    return false;
}

// Get the request URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove leading slash
$request_uri = ltrim($request_uri, '/');

// Handle /asmaraadmin alias for security
if (strpos($request_uri, 'asmaraadmin') === 0) {
    // /asmaraadmin -> /backend/admin/login.php
    if ($request_uri === 'asmaraadmin' || $request_uri === 'asmaraadmin/') {
        include __DIR__ . '/backend/admin/login.php';
        return true;
    }
    
    // /asmaraadmin/menu -> /backend/admin/menu.php
    // /asmaraadmin/bookings -> /backend/admin/bookings.php
    // etc.
    $admin_page = str_replace('asmaraadmin/', '', $request_uri);
    $admin_page = str_replace('asmaraadmin', '', $admin_page);
    $admin_page = ltrim($admin_page, '/');
    
    // If no page specified, show login
    if (empty($admin_page)) {
        include __DIR__ . '/backend/admin/login.php';
        return true;
    }
    
    // Map common pages
    $admin_routes = array(
        'login' => 'login.php',
        'dashboard' => 'index.php',
        'index' => 'index.php',
        'menu' => 'menu.php',
        'bookings' => 'bookings.php',
        'booking' => 'bookings.php',
        'branches' => 'branches.php',
        'branch' => 'branches.php',
        'contact' => 'contact.php',
        'events' => 'events.php',
        'newsletter' => 'newsletter.php',
        'users' => 'users.php',
        'reports' => 'reports.php',
    );
    
    // Check if it's a recognized admin page
    $page_name = explode('?', $admin_page)[0]; // Remove query strings
    $page_name = explode('/', $page_name)[0]; // Get first segment
    
    if (isset($admin_routes[$page_name])) {
        $admin_file = __DIR__ . '/backend/admin/' . $admin_routes[$page_name];
        if (file_exists($admin_file)) {
            include $admin_file;
            return true;
        }
    }
    
    // If page not found, show login
    include __DIR__ . '/backend/admin/login.php';
    return true;
}

// Check if this is a static file request (in frontend folder)
$static_extensions = array('css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'mp4', 'webm', 'woff', 'woff2', 'ttf', 'eot');
$file_ext = pathinfo($request_uri, PATHINFO_EXTENSION);

if (in_array($file_ext, $static_extensions)) {
    // Try to serve from frontend
    if (file_exists(__DIR__ . '/frontend/' . $request_uri)) {
        return false; // Let PHP serve it directly
    }
    // Try to serve from root
    if (file_exists(__DIR__ . '/' . $request_uri)) {
        return false; // Let PHP serve it directly
    }
}

// Check if this is a backend request (old method for backwards compatibility)
if (strpos($request_uri, 'backend/') === 0) {
    $backend_file = __DIR__ . '/' . $request_uri;
    if (file_exists($backend_file)) {
        if (is_file($backend_file)) {
            include $backend_file;
        }
        return true;
    }
}

// Check if file exists in frontend root
$frontend_file = __DIR__ . '/frontend/' . $request_uri;
if (file_exists($frontend_file) && is_file($frontend_file)) {
    include $frontend_file;
    return true;
}

// Default to frontend index for empty request
if (empty($request_uri) || $request_uri === '') {
    include __DIR__ . '/frontend/index.php';
    return true;
}

// Catch-all: Try frontend index for all other PHP requests
if ($file_ext === 'php' || empty($file_ext)) {
    include __DIR__ . '/frontend/index.php';
    return true;
}

// Not found
http_response_code(404);
echo "404 - Not Found: $request_uri";
return true;
?>
