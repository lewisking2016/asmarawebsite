<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../security/Auth.php';

Auth::logout();
header('Location: /backend/admin/login.php?logged_out=1');
exit();
