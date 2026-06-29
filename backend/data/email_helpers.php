<?php

function asmara_booking_status_label($status) {
    $labels = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
    ];

    return $labels[$status] ?? ucfirst((string)$status);
}

function asmara_send_booking_status_email(array $booking, $newStatus, $reason = '') {
    $to = trim((string)($booking['email'] ?? ''));
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $fromEmail = 'restaurant@asmara.co.ke';
    $fromName = 'Asmara Reservations';
    $statusLabel = asmara_booking_status_label($newStatus);
    $guestName = trim((string)($booking['guest_name'] ?? 'Guest'));
    $branchName = trim((string)($booking['branch_name'] ?? 'Asmara Restaurant'));
    $bookingDate = trim((string)($booking['booking_date'] ?? ''));
    $bookingTime = trim((string)($booking['booking_time'] ?? ''));
    $guests = (int)($booking['guest_count'] ?? 0);
    $code = trim((string)($booking['confirmation_code'] ?? ''));

    $subject = "Your Asmara reservation is now {$statusLabel}";
    if ($newStatus === 'confirmed') {
        $headline = 'Your reservation has been confirmed';
        $message = 'We are happy to let you know that your reservation is confirmed.';
    } elseif ($newStatus === 'cancelled') {
        $headline = 'Your reservation has been cancelled';
        $message = 'Your reservation has been cancelled by our team.';
    } else {
        $headline = 'Your reservation status has been updated';
        $message = 'Your reservation status has been updated.';
    }

    $html  = "<html><body style='font-family: Arial, sans-serif; color: #222; line-height: 1.6;'>";
    $html .= "<h2 style='color:#ed174b; margin-bottom: 12px;'>{$headline}</h2>";
    $html .= "<p>Hello " . htmlspecialchars($guestName) . ",</p>";
    $html .= "<p>{$message}</p>";
    $html .= "<table style='border-collapse: collapse; width: 100%; max-width: 560px; margin-top: 16px;'>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Status</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($statusLabel) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Confirmation Code</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($code) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Branch</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($branchName) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Date</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($bookingDate) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Time</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($bookingTime) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Guests</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . (int)$guests . "</td></tr>";
    if ($newStatus === 'cancelled' && trim($reason) !== '') {
        $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Cancellation Reason</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . nl2br(htmlspecialchars(trim($reason))) . "</td></tr>";
    }
    $html .= "</table>";
    $html .= "<p style='margin-top: 20px;'>If you have any questions, please reply to this email or contact Asmara Restaurant.</p>";
    $html .= "<p style='font-size: 12px; color: #888;'>Asmara Reservations</p>";
    $html .= "</body></html>";

    $smtpPassword = getenv('ASMARA_SMTP_PASS') ?: '';
    if ($smtpPassword !== '') {
        $smtpConfig = [
            'host' => getenv('ASMARA_SMTP_HOST') ?: 'mail.asmara.co.ke',
            'port' => (int)(getenv('ASMARA_SMTP_PORT') ?: 465),
            'username' => getenv('ASMARA_SMTP_USER') ?: $fromEmail,
            'password' => $smtpPassword,
            'encryption' => getenv('ASMARA_SMTP_ENCRYPTION') ?: 'ssl',
        ];

        if (asmara_send_smtp_mail($smtpConfig, $fromEmail, $fromName, $to, $subject, $html)) {
            return true;
        }
    }

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
    $headers[] = 'Reply-To: ' . $fromEmail;

    $headerString = implode("\r\n", $headers);
    $additionalParams = '-f' . $fromEmail;

    $sent = @mail($to, $subject, $html, $headerString, $additionalParams);
    if (!$sent) {
        $sent = @mail($to, $subject, $html, $headerString);
    }

    return $sent;
}

function asmara_send_booking_status_to_sales(array $booking, $newStatus, $reason = '') {
    $salesEmail = 'sales@asmara.co.ke';
    $fromEmail = 'restaurant@asmara.co.ke';
    $fromName = 'Asmara Reservations';
    $statusLabel = asmara_booking_status_label($newStatus);
    $guestName = trim((string)($booking['guest_name'] ?? 'Guest'));
    $branchName = trim((string)($booking['branch_name'] ?? 'Asmara Restaurant'));
    $bookingDate = trim((string)($booking['booking_date'] ?? ''));
    $bookingTime = trim((string)($booking['booking_time'] ?? ''));
    $guests = (int)($booking['guest_count'] ?? 0);
    $code = trim((string)($booking['confirmation_code'] ?? ''));
    $guestEmail = trim((string)($booking['email'] ?? ''));
    $guestPhone = trim((string)($booking['phone'] ?? ''));

    $subject = "Reservation {$statusLabel} - {$guestName} ({$code})";
    $html  = "<html><body style='font-family: Arial, sans-serif; color: #222; line-height: 1.6;'>";
    $html .= "<h2 style='color:#ed174b; margin-bottom: 12px;'>Reservation status updated</h2>";
    $html .= "<p>A reservation was marked as <strong>" . htmlspecialchars($statusLabel) . "</strong> in the admin dashboard.</p>";
    $html .= "<table style='border-collapse: collapse; width: 100%; max-width: 560px; margin-top: 16px;'>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Guest</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($guestName) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Guest Email</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($guestEmail) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Guest Phone</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($guestPhone) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Status</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($statusLabel) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Confirmation Code</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($code) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Branch</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($branchName) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Date</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($bookingDate) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Time</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($bookingTime) . "</td></tr>";
    $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Guests</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . (int)$guests . "</td></tr>";
    if ($newStatus === 'cancelled' && trim($reason) !== '') {
        $html .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Cancellation Reason</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . nl2br(htmlspecialchars(trim($reason))) . "</td></tr>";
    }
    $html .= "</table>";
    $html .= "</body></html>";

    return asmara_send_booking_status_message($salesEmail, $fromEmail, $fromName, $subject, $html);
}

function asmara_send_booking_status_message($to, $fromEmail, $fromName, $subject, $html) {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $smtpPassword = getenv('ASMARA_SMTP_PASS') ?: '';
    if ($smtpPassword !== '') {
        $smtpConfig = [
            'host' => getenv('ASMARA_SMTP_HOST') ?: 'mail.asmara.co.ke',
            'port' => (int)(getenv('ASMARA_SMTP_PORT') ?: 465),
            'username' => getenv('ASMARA_SMTP_USER') ?: $fromEmail,
            'password' => $smtpPassword,
            'encryption' => getenv('ASMARA_SMTP_ENCRYPTION') ?: 'ssl',
        ];

        if (asmara_send_smtp_mail($smtpConfig, $fromEmail, $fromName, $to, $subject, $html)) {
            return true;
        }
    }

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
    $headers[] = 'Reply-To: ' . $fromEmail;

    $headerString = implode("\r\n", $headers);
    $additionalParams = '-f' . $fromEmail;

    $sent = @mail($to, $subject, $html, $headerString, $additionalParams);
    if (!$sent) {
        $sent = @mail($to, $subject, $html, $headerString);
    }

    return $sent;
}

function asmara_send_smtp_mail(array $config, $fromEmail, $fromName, $to, $subject, $htmlBody) {
    $host = $config['host'] ?? 'mail.asmara.co.ke';
    $port = (int)($config['port'] ?? 465);
    $username = $config['username'] ?? $fromEmail;
    $password = $config['password'] ?? '';
    $encryption = strtolower((string)($config['encryption'] ?? 'ssl'));

    if ($password === '') {
        return false;
    }

    $remote = ($encryption === 'tls' ? 'tls://' : 'ssl://') . $host . ':' . $port;
    $socket = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        return false;
    }

    $readResponse = function () use ($socket) {
        $data = '';
        while (($line = fgets($socket, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $sendCommand = function ($command, array $expectedCodes = [250, 251]) use ($socket, $readResponse) {
        fwrite($socket, $command . "\r\n");
        $response = $readResponse();
        $code = (int)substr($response, 0, 3);
        return in_array($code, $expectedCodes, true) ? $response : false;
    };

    $banner = $readResponse();
    if ((int)substr($banner, 0, 3) !== 220) {
        fclose($socket);
        return false;
    }

    if (!$sendCommand('EHLO asmara.co.ke')) {
        fclose($socket);
        return false;
    }

    if ($encryption === 'tls') {
        if (!$sendCommand('STARTTLS', [220])) {
            fclose($socket);
            return false;
        }
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if (!$sendCommand('EHLO asmara.co.ke')) {
            fclose($socket);
            return false;
        }
    }

    if (!$sendCommand('AUTH LOGIN', [334])) {
        fclose($socket);
        return false;
    }
    if (!$sendCommand(base64_encode($username), [334])) {
        fclose($socket);
        return false;
    }
    if (!$sendCommand(base64_encode($password), [235])) {
        fclose($socket);
        return false;
    }

    if (!$sendCommand('MAIL FROM:<' . $fromEmail . '>', [250])) {
        fclose($socket);
        return false;
    }
    if (!$sendCommand('RCPT TO:<' . $to . '>', [250, 251])) {
        fclose($socket);
        return false;
    }
    if (!$sendCommand('DATA', [354])) {
        fclose($socket);
        return false;
    }

    $headers = [];
    $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
    $headers[] = 'To: <' . $to . '>';
    $headers[] = 'Subject: ' . $subject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'Date: ' . date(DATE_RFC2822);
    $message = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.";

    fwrite($socket, $message . "\r\n");
    $result = $readResponse();
    $sent = ((int)substr($result, 0, 3) === 250);
    $sendCommand('QUIT', [221]);
    fclose($socket);

    return $sent;
}
