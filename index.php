<?php
// Serve the frontend homepage directly — no redirect, no /frontend/ exposure
chdir(__DIR__ . '/frontend');
require __DIR__ . '/frontend/index.php';
