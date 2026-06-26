<?php
/**
 * Admin Newsletter Management
 */

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../database/NewsletterRepository.php';
require_once __DIR__ . '/../security/Auth.php';

Auth::startSession();

if (!Auth::isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$newsletterRepo = new NewsletterRepository();
$subscribers = $newsletterRepo->getAll();
$totalCount = $newsletterRepo->getTotalCount();

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete' && isset($_POST['subscriber_id'])) {
            $result = $newsletterRepo->delete($_POST['subscriber_id']);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            // Refresh subscribers list
            $subscribers = $newsletterRepo->getAll();
            $totalCount = $newsletterRepo->getTotalCount();
        } elseif ($_POST['action'] === 'unsubscribe' && isset($_POST['email'])) {
            $result = $newsletterRepo->unsubscribe($_POST['email']);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            // Refresh subscribers list
            $subscribers = $newsletterRepo->getAll();
            $totalCount = $newsletterRepo->getTotalCount();
        }
    }
}

// Handle search
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $subscribers = $newsletterRepo->search($search);
}

include 'header.php';
?>

<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    .page-header {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--color-text-dark);
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: var(--color-primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--color-primary, #ed174b);
        opacity: 0.9;
    }

    .btn-secondary {
        background-color: var(--color-gold);
        color: var(--color-text-dark);
    }

    .btn-secondary:hover {
        opacity: 0.9;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        opacity: 0.9;
    }

    .search-box {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
    }

    .search-box input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }

    .subscribers-table {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background-color: #f8f9fa;
    }

    th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: var(--color-text-dark);
        border-bottom: 2px solid #ddd;
    }

    td {
        padding: 15px;
        border-bottom: 1px solid #eee;
    }

    tr:hover {
        background-color: #f8f9fa;
    }

    .email-cell {
        font-family: 'Courier New', monospace;
        font-size: 13px;
    }

    .date-cell {
        font-size: 13px;
        color: #666;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-active {
        background-color: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
    }

    .actions-cell {
        display: flex;
        gap: 8px;
    }

    .btn-small {
        padding: 6px 12px;
        font-size: 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-unsubscribe {
        background-color: #ffc107;
        color: #333;
    }

    .btn-unsubscribe:hover {
        background-color: #ffb300;
    }

    .btn-delete {
        background-color: #dc3545;
        color: white;
    }

    .btn-delete:hover {
        background-color: #c82333;
    }

    .message {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        animation: slideIn 0.3s ease;
    }

    .message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        max-width: 400px;
        width: 90%;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 15px;
        color: var(--color-text-dark);
    }

    .modal-text {
        color: #666;
        margin-bottom: 20px;
        line-height: 1.6;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
</style>

<div class="dashboard-container">
    <div class="page-header">
        <h1 class="page-title">
            <svg width="24" height="24" viewBox="0 0 256 256" fill="none" style="display: inline-block; margin-right: 12px; vertical-align: middle;">
                <path d="M224 48H32a8 8 0 0 0-8 8v136a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a8 8 0 0 0-8-8Zm-96 85.15L52.57 64h150.86ZM98.71 128 40 181.81V74.19Zm11.84 10.85 12.58 11a8 8 0 0 0 10.74 0l12.58-11L210.39 192H45.61ZM157.29 128 216 74.19v107.62Z" fill="currentColor"/>
            </svg>
            Newsletter Subscribers
        </h1>
        <div class="header-actions">
            <a href="?export=csv" class="btn btn-secondary">
                <svg width="16" height="16" viewBox="0 0 256 256" fill="none" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                    <path d="M216 120v96a16 16 0 0 1-16 16H56a16 16 0 0 1-16-16v-96a8 8 0 0 1 16 0v96h144v-96a8 8 0 0 1 16 0Zm-61.66-34.34a8 8 0 0 1 0 11.32l-40 40a8 8 0 0 1-11.32-11.32L132.69 104H40a8 8 0 0 1 0-16h92.69l-29.17-29.17a8 8 0 0 1 11.32-11.32Z" fill="currentColor"/>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="search-box">
        <form method="GET" style="display: flex; gap: 10px; flex: 1;">
            <input type="text" name="search" placeholder="Search by email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 256 256" fill="none" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                    <path d="M229.66 218.34l-50.07-50.06a88.21 88.21 0 1 0-11.31 11.31l50.06 50.07a8 8 0 0 0 11.32-11.32ZM40 112a72 72 0 1 1 72 72 72.08 72.08 0 0 1-72-72Z" fill="currentColor"/>
                </svg>
                Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="newsletter.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="subscribers-table">
        <?php if (empty($subscribers)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="64" height="64" viewBox="0 0 256 256" fill="none" style="opacity: 0.5;">
                        <path d="M224 48H32a8 8 0 0 0-8 8v136a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a8 8 0 0 0-8-8Zm-96 85.15L52.57 64h150.86ZM98.71 128 40 181.81V74.19Zm11.84 10.85 12.58 11a8 8 0 0 0 10.74 0l12.58-11L210.39 192H45.61ZM157.29 128 216 74.19v107.62Z" fill="currentColor"/>
                    </svg>
                </div>
                <h3>No Subscribers Yet</h3>
                <p>Newsletter subscribers will appear here when users sign up from the website.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%;">ID</th>
                            <th style="width: 40%;">Email Address</th>
                            <th style="width: 20%;">Subscription Date</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $sub): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($sub['id']); ?></td>
                                <td class="email-cell"><?php echo htmlspecialchars($sub['email']); ?></td>
                                <td class="date-cell"><?php echo date('M d, Y', strtotime($sub['subscribed_at'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $sub['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $sub['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <?php if ($sub['is_active']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="unsubscribe">
                                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($sub['email']); ?>">
                                                <button type="submit" class="btn-small btn-unsubscribe" onclick="return confirm('Unsubscribe this email?');">Unsubscribe</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="subscriber_id" value="<?php echo htmlspecialchars($sub['id']); ?>">
                                            <button type="submit" class="btn-small btn-delete" onclick="return confirm('Delete this subscriber permanently?');">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
