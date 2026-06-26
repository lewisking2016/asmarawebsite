<?php
/**
 * Users Management Page
 * Manage admin users and permissions
 */

$page_title = 'Users Management | Asmara Admin';

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../database/UserRepository.php';

Auth::requireLogin();

$userRepo = new UserRepository();
$users = $userRepo->getAll();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';
$edit_user = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';

    if ($action_type === 'create') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'staff';

        if (empty($username) || empty($email) || empty($password)) {
            $error = 'All fields are required';
        } else {
            $userRepo->create([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role' => $role
            ]);
            Auth::logActivity(Auth::getCurrentUserId(), 'created', 'users', 0);
            $message = 'User created successfully!';
            $action = 'list';
        }

    } elseif ($action_type === 'update') {
        $user_id = $_POST['user_id'] ?? 0;
        $data = [
            'email' => $_POST['email'] ?? '',
            'role' => $_POST['role'] ?? 'staff'
        ];

        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        $userRepo->update($user_id, $data);
        Auth::logActivity(Auth::getCurrentUserId(), 'updated', 'users', $user_id);
        $message = 'User updated successfully!';
        $action = 'list';

    } elseif ($action_type === 'delete') {
        $user_id = $_POST['user_id'] ?? 0;
        if ($user_id !== Auth::getCurrentUserId()) {
            $userRepo->delete($user_id);
            Auth::logActivity(Auth::getCurrentUserId(), 'deleted', 'users', $user_id);
            $message = 'User deleted successfully!';
        } else {
            $error = 'Cannot delete your own account!';
        }
        $action = 'list';
    }
}

if ($action === 'edit' && isset($_GET['id'])) {
    $edit_user = $userRepo->getById($_GET['id']);
}

?>
<?php include 'header.php'; ?>

            <div class="page-header">
                <h1 class="page-title">Users Management</h1>
                <p class="page-subtitle">Manage admin staff and permissions</p>
            </div>

            <div class="page-content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($action === 'create' || $action === 'edit'): ?>
                    <!-- ADD/EDIT FORM -->
                    <div class="form-container">
                        <a href="?action=list" class="back-link">← Back to Users</a>

                        <h3><?php echo $action === 'create' ? 'Add New User' : 'Edit User'; ?></h3>
                        
                        <form method="POST" class="user-form">
                            <input type="hidden" name="action_type" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                            <?php if ($action === 'edit' && $edit_user): ?>
                                <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                            <?php endif; ?>

                            <?php if ($action === 'create'): ?>
                                <div class="form-group">
                                    <label>Username *</label>
                                    <input 
                                        type="text" 
                                        name="username" 
                                        placeholder="e.g., johndoe"
                                        required
                                    >
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label>Email Address *</label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    placeholder="user@asmara.co.ke"
                                    value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label><?php echo $action === 'create' ? 'Password *' : 'Password (leave blank to keep current)'; ?></label>
                                <input 
                                    type="password" 
                                    name="password" 
                                    placeholder="Secure password"
                                    <?php echo $action === 'create' ? 'required' : ''; ?>
                                >
                            </div>

                            <div class="form-group">
                                <label>Role *</label>
                                <select name="role" required>
                                    <option value="admin" <?php echo ($edit_user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin (Full Access)</option>
                                    <option value="manager" <?php echo ($edit_user['role'] ?? '') === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="staff" <?php echo ($edit_user['role'] ?? '') === 'staff' ? 'selected' : ''; ?>>Staff</option>
                                </select>
                            </div>

                            <div class="action-buttons" style="gap: 12px; margin-top: 24px;">
                                <button type="submit" class="action-btn">
                                    <?php echo $action === 'create' ? '+ Create User' : '✓ Update User'; ?>
                                </button>
                                <a href="?action=list" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- USERS LIST WITH SAAS CONTROLS -->
                    <div class="filter-bar-premium">
                        <div class="filter-left-actions">
                            <a href="?action=create" class="action-btn">+ New User</a>

                            <input 
                                type="text" 
                                id="user-search-input" 
                                placeholder="Search users..." 
                                class="filter-search-input"
                                onkeyup="filterUsers()"
                            >

                            <select id="user-role-filter" class="filter-select" onchange="filterUsers()">
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                        
                        <div class="view-toggle-container">
                            <button class="view-toggle-btn active" id="btnUserCardView" title="Card View" onclick="setUserViewMode('card')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M104 56v64a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8V56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v64a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8V56a8 8 0 0 0-8-8ZM104 144v56a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8v-56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v56a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8v-56a8 8 0 0 0-8-8Z"/></svg>
                            </button>
                            <button class="view-toggle-btn" id="btnUserListView" title="List View" onclick="setUserViewMode('list')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M80 64a8 8 0 0 1 8-8h120a8 8 0 0 1 0 16H88a8 8 0 0 1-8-8Zm120 48H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16Zm0 56H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16ZM44 52a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Z"/></svg>
                            </button>
                        </div>
                    </div>

                    <?php if (empty($users)): ?>
                        <div class="empty-state">
                            <p>No users found. <a href="?action=create">Create your first user</a></p>
                        </div>
                    <?php else: ?>
                        <div class="items-display-wrapper">
                            <div class="users-grid card-view-mode" id="usersDisplayGrid">
                                <?php foreach ($users as $user): ?>
                                    <div class="user-card-premium" 
                                         data-role="<?php echo htmlspecialchars($user['role']); ?>"
                                         data-search="<?php echo htmlspecialchars(strtolower($user['username'] . ' ' . $user['email'])); ?>">
                                        <div class="user-card-header">
                                            <div class="user-avatar-premium">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                            <div class="user-meta-info">
                                                <div class="user-username"><?php echo htmlspecialchars($user['username']); ?></div>
                                                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                            </div>
                                        </div>

                                        <div class="user-card-body">
                                            <div class="user-details-list">
                                                <div class="user-detail-row">
                                                    <strong>Role:</strong>
                                                    <span class="status-badge status-confirmed" style="text-transform: capitalize;"><?php echo htmlspecialchars($user['role']); ?></span>
                                                </div>
                                                <div class="user-detail-row">
                                                    <strong>Last Login:</strong>
                                                    <span><?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></span>
                                                </div>
                                                <div class="user-detail-row">
                                                    <strong>Status:</strong>
                                                    <span class="status-badge status-completed">Active</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="user-card-footer">
                                            <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-secondary" style="flex: 1; padding: 8px; font-size: 13px; text-align: center; text-decoration: none;">Edit</a>
                                            <?php if ($user['id'] !== Auth::getCurrentUserId()): ?>
                                                <form method="POST" style="margin: 0; flex: 1;" onsubmit="return confirm('Delete this user?');">
                                                    <input type="hidden" name="action_type" value="delete">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 8px; font-size: 13px; color: var(--color-danger); border-color: rgba(239, 68, 68, 0.15);">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <script>
                        function filterUsers() {
                            const searchVal = document.getElementById('user-search-input').value.toLowerCase();
                            const roleVal = document.getElementById('user-role-filter').value;
                            const cards = document.querySelectorAll('.user-card-premium');

                            cards.forEach(card => {
                                const searchData = card.getAttribute('data-search');
                                const role = card.getAttribute('data-role');

                                const matchesSearch = searchData.includes(searchVal);
                                const matchesRole = roleVal === '' || role === roleVal;

                                if (matchesSearch && matchesRole) {
                                    card.style.display = '';
                                } else {
                                    card.style.display = 'none';
                                }
                            });
                        }

                        function setUserViewMode(mode) {
                            const grid = document.getElementById('usersDisplayGrid');
                            const btnCard = document.getElementById('btnUserCardView');
                            const btnList = document.getElementById('btnUserListView');

                            if (mode === 'list') {
                                grid.className = 'users-grid list-view-mode';
                                btnList.classList.add('active');
                                btnCard.classList.remove('active');
                            } else {
                                grid.className = 'users-grid card-view-mode';
                                btnCard.classList.add('active');
                                btnList.classList.remove('active');
                            }
                        }
                    </script>
                <?php endif; ?>
            </div>

<?php include 'footer.php'; ?>
