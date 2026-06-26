<?php
/**
 * Contact Messages Management Page
 * View, reply, and manage contact inquiries
 */

$page_title = 'Contact Messages | Asmara Admin';

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../database/ContactRepository.php';
require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Validator.php';

Auth::requireLogin();

$contactRepo = new ContactRepository();
$action = $_GET['action'] ?? 'list';
$view_id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';

    if ($action_type === 'update_status') {
        $inquiry_id = $_POST['inquiry_id'] ?? 0;
        $status = $_POST['status'] ?? '';

        if (in_array($status, ['new', 'read', 'replied', 'closed'])) {
            $contactRepo->updateStatus($inquiry_id, $status);
            Auth::logActivity(Auth::getCurrentUserId(), 'updated', 'contact_inquiries', $inquiry_id);
            $message = 'Inquiry status updated!';
            $action = 'list';
        }
    } elseif ($action_type === 'add_response') {
        $inquiry_id = $_POST['inquiry_id'] ?? 0;
        $response = $_POST['response'] ?? '';

        if (!empty($response)) {
            $contactRepo->addResponse($inquiry_id, $response);
            Auth::logActivity(Auth::getCurrentUserId(), 'updated', 'contact_inquiries', $inquiry_id);
            $message = 'Response added successfully!';
            $action = 'view';
            $_GET['id'] = $inquiry_id;
        } else {
            $error = 'Response cannot be empty';
        }
    } elseif ($action_type === 'delete') {
        $inquiry_id = $_POST['inquiry_id'] ?? 0;
        $contactRepo->delete($inquiry_id);
        Auth::logActivity(Auth::getCurrentUserId(), 'deleted', 'contact_inquiries', $inquiry_id);
        $message = 'Inquiry deleted successfully!';
        $action = 'list';
    }
}

// Get filter
$filter_status = $_GET['filter'] ?? null;

// Get inquiries
if ($filter_status) {
    $inquiries = $contactRepo->getByStatus($filter_status);
} else {
    $inquiries = $contactRepo->getAll();
}

$inquiry_detail = null;
if ($view_id && $action === 'view') {
    $inquiry_detail = $contactRepo->getById($view_id);
}

?>
<?php include 'header.php'; ?>

            <div class="page-header">
                <h1 class="page-title">Contact Messages</h1>
                <p class="page-subtitle">Manage customer inquiries</p>
            </div>

            <div class="page-content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($action === 'view' && $inquiry_detail): ?>
                    <!-- MESSAGE DETAIL VIEW -->
                    <div class="detail-container">
                        <a href="?action=list" class="back-link">← Back to Messages</a>

                        <div class="detail-header">
                            <div>
                                <h2>From: <?php echo htmlspecialchars($inquiry_detail['name']); ?></h2>
                                <p class="meta">Received: <?php echo date('F j, Y \a\t H:i', strtotime($inquiry_detail['created_at'])); ?></p>
                            </div>
                            <span class="status-badge status-<?php echo $inquiry_detail['status']; ?>">
                                <?php echo ucfirst($inquiry_detail['status']); ?>
                            </span>
                        </div>

                        <div class="message-section">
                            <h3>Contact Information</h3>
                            <div class="info-item">
                                <label>Email:</label>
                                <p><a href="mailto:<?php echo htmlspecialchars($inquiry_detail['email']); ?>">
                                    <?php echo htmlspecialchars($inquiry_detail['email']); ?>
                                </a></p>
                            </div>
                        </div>

                        <div class="message-section">
                            <h3>Message</h3>
                            <div class="message-box">
                                <div class="subject">
                                    <strong>Subject:</strong> <?php echo htmlspecialchars($inquiry_detail['subject']); ?>
                                </div>
                                <div class="message-text">
                                    <?php echo nl2br(htmlspecialchars($inquiry_detail['message'])); ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($inquiry_detail['admin_response']): ?>
                        <div class="message-section">
                            <h3>Your Response</h3>
                            <div class="response-box">
                                <?php echo nl2br(htmlspecialchars($inquiry_detail['admin_response'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="message-section">
                            <h3>Add Response</h3>
                            <form method="POST" class="response-form">
                                <input type="hidden" name="action_type" value="add_response">
                                <input type="hidden" name="inquiry_id" value="<?php echo $inquiry_detail['id']; ?>">

                                <div class="form-group">
                                    <label>Your Response:</label>
                                    <textarea 
                                        name="response" 
                                        placeholder="Type your response here..."
                                        rows="6"
                                        required
                                    ></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Send Response</button>
                            </form>
                        </div>

                        <div class="actions-panel">
                            <form method="POST" class="status-form">
                                <input type="hidden" name="action_type" value="update_status">
                                <input type="hidden" name="inquiry_id" value="<?php echo $inquiry_detail['id']; ?>">

                                <div class="form-group">
                                    <label>Status:</label>
                                    <select name="status">
                                        <option value="new" <?php echo $inquiry_detail['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="read" <?php echo $inquiry_detail['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                        <option value="replied" <?php echo $inquiry_detail['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                        <option value="closed" <?php echo $inquiry_detail['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-secondary">Update Status</button>
                            </form>

                            <form method="POST" onsubmit="return confirm('Delete this message?');" class="delete-form">
                                <input type="hidden" name="action_type" value="delete">
                                <input type="hidden" name="inquiry_id" value="<?php echo $inquiry_detail['id']; ?>">
                                <button type="submit" class="btn btn-danger">Delete Message</button>
                            </form>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- MESSAGES LIST WITH SAAS CONTROLS -->
                    <div class="filter-bar-premium">
                        <div class="filter-left-actions">
                            <input 
                                type="text" 
                                id="message-search-input" 
                                placeholder="Search messages..." 
                                class="filter-search-input"
                                onkeyup="filterMessages()"
                            >

                            <select id="message-status-filter" class="filter-select" onchange="filterMessages()">
                                <option value="">All Statuses</option>
                                <option value="new" <?php echo $filter_status === 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="read" <?php echo $filter_status === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo $filter_status === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                <option value="closed" <?php echo $filter_status === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        
                        <div class="view-toggle-container">
                            <button class="view-toggle-btn active" id="btnMessageCardView" title="Card View" onclick="setMessageViewMode('card')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M104 56v64a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8V56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v64a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8V56a8 8 0 0 0-8-8ZM104 144v56a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8v-56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v56a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8v-56a8 8 0 0 0-8-8Z"/></svg>
                            </button>
                            <button class="view-toggle-btn" id="btnMessageListView" title="List View" onclick="setMessageViewMode('list')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M80 64a8 8 0 0 1 8-8h120a8 8 0 0 1 0 16H88a8 8 0 0 1-8-8Zm120 48H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16Zm0 56H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16ZM44 52a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Z"/></svg>
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($inquiries)): ?>
                        <div class="items-display-wrapper">
                            <div class="messages-grid card-view-mode" id="messagesDisplayGrid">
                                <?php foreach ($inquiries as $inquiry): ?>
                                <div class="message-card-premium" 
                                     data-status="<?php echo htmlspecialchars($inquiry['status']); ?>"
                                     data-search="<?php echo htmlspecialchars(strtolower($inquiry['name'] . ' ' . $inquiry['email'] . ' ' . $inquiry['subject'] . ' ' . $inquiry['message'])); ?>">
                                    
                                    <div class="message-card-header">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div class="message-sender-avatar">
                                                <?php echo strtoupper(substr($inquiry['name'], 0, 1)); ?>
                                            </div>
                                            <div class="message-meta-info">
                                                <div class="message-sender-name"><?php echo htmlspecialchars($inquiry['name']); ?></div>
                                                <div class="message-sender-email"><?php echo htmlspecialchars($inquiry['email']); ?></div>
                                            </div>
                                        </div>
                                        <span class="status-badge status-<?php echo $inquiry['status']; ?>">
                                            <?php echo ucfirst($inquiry['status']); ?>
                                        </span>
                                    </div>

                                    <div class="message-card-body">
                                        <div class="message-subject"><?php echo htmlspecialchars($inquiry['subject']); ?></div>
                                        <p class="message-preview"><?php echo htmlspecialchars(substr($inquiry['message'], 0, 120)) . (strlen($inquiry['message']) > 120 ? '...' : ''); ?></p>
                                    </div>

                                    <div class="message-card-footer">
                                        <span class="message-date">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:4px; display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <?php echo date('M d, Y H:i', strtotime($inquiry['created_at'])); ?>
                                        </span>
                                        <a href="?action=view&id=<?php echo $inquiry['id']; ?>" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">View & Reply</a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No messages found</p>
                        </div>
                    <?php endif; ?>

                    <script>
                        function filterMessages() {
                            const searchVal = document.getElementById('message-search-input').value.toLowerCase();
                            const statusVal = document.getElementById('message-status-filter').value;
                            const cards = document.querySelectorAll('.message-card-premium');

                            cards.forEach(card => {
                                const searchData = card.getAttribute('data-search');
                                const status = card.getAttribute('data-status');

                                const matchesSearch = searchData.includes(searchVal);
                                const matchesStatus = statusVal === '' || status === statusVal;

                                if (matchesSearch && matchesStatus) {
                                    card.style.display = '';
                                } else {
                                    card.style.display = 'none';
                                }
                            });
                        }

                        function setMessageViewMode(mode) {
                            const grid = document.getElementById('messagesDisplayGrid');
                            const btnCard = document.getElementById('btnMessageCardView');
                            const btnList = document.getElementById('btnMessageListView');

                            if (mode === 'list') {
                                grid.className = 'messages-grid list-view-mode';
                                btnList.classList.add('active');
                                btnCard.classList.remove('active');
                            } else {
                                grid.className = 'messages-grid card-view-mode';
                                btnCard.classList.add('active');
                                btnList.classList.remove('active');
                            }
                        }
                    </script>
                <?php endif; ?>
            </div>

<?php include 'footer.php'; ?>
