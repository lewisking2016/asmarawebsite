<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Validator.php';

Auth::startSession();

// If already logged in, redirect to dashboard
if (Auth::isLoggedIn()) {
    header('Location: /asmaraadmin/index');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input
    if (!Validator::validateRequired($username)) {
        $error = 'Username is required';
    } elseif (!Validator::validateRequired($password)) {
        $error = 'Password is required';
    } elseif (Auth::login($username, $password)) {
        // Login successful
        header('Location: /asmaraadmin/index');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asmara Admin - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #fbf8f0 0%, #f4eee0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #1e150d;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 24px;
        }

        .login-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(30, 21, 13, 0.06);
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header h1 {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 32px;
            margin-bottom: 8px;
            color: #ed174b;
            font-weight: 700;
        }

        .login-header p {
            font-size: 14px;
            color: #6e6559;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #1e150d;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            color: #1e150d;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #ed174b;
            box-shadow: 0 0 0 3px rgba(237, 23, 75, 0.12);
        }

        input::placeholder {
            color: #9ca3af;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #b91c1c;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #065f46;
        }

        .login-button {
            width: 100%;
            padding: 14px;
            background: #ed174b;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 24px;
            box-shadow: 0 4px 12px rgba(237, 23, 75, 0.15);
        }

        .login-button:hover {
            background: #d41241;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(237, 23, 75, 0.25);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .form-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }

        .divider {
            border: none;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            margin: 24px 0;
        }



        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Asmara Admin</h1>
                <p>Restaurant Management System</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="login-button">Login</button>
            </form>



            <div class="form-footer">
                <p>&copy; 2026 Asmara Restaurant. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
