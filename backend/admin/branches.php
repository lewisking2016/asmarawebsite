<?php
/**
 * Branches Management Page
 * Edit branch details, hours, and capacity
 */

$page_title = 'Branches Management | Asmara Admin';

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../database/BranchRepository.php';
require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Validator.php';

Auth::requireLogin();

$branchRepo = new BranchRepository();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';
    $branch_id = $_POST['branch_id'] ?? 0;

    if ($action_type === 'update') {
        $data = [
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'opening_hours' => $_POST['opening_hours'] ?? '',
            'capacity' => $_POST['capacity'] ?? 50,
            'address' => $_POST['address'] ?? '',
        ];
        // Handle hero image upload
        if (!empty($_FILES['hero_image']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/branches/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $tmp = $_FILES['hero_image']['tmp_name'];
            $ext = pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('branch_') . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($tmp, $dest)) {
                $data['hero_image'] = '../backend/uploads/branches/' . $filename;
            }
        }

        $branchRepo->update($branch_id, $data);
        Auth::logActivity(Auth::getCurrentUserId(), 'updated', 'branches', $branch_id);
        $message = 'Branch updated successfully!';
        $action = 'list';
    }
}

$branches = $branchRepo->getAll();
$edit_branch = null;

if ($action === 'edit' && isset($_GET['id'])) {
    $edit_branch = $branchRepo->getById($_GET['id']);
}

?>
<?php include 'header.php'; ?>

<?php
function admin_preview_src($url) {
    if (empty($url)) return '';
    // If it starts with images/, route via frontend folder
    if (strpos($url, 'images/') === 0) {
        return '/frontend/' . $url;
    }
    $p = preg_replace('/^\.\./', '', $url);
    if ($p === '') return '';
    if ($p[0] !== '/') $p = '/' . $p;
    return $p;
}
?>

            <div class="page-header">
                <h1 class="page-title">Branches Management</h1>
                <p class="page-subtitle">Manage all restaurant locations</p>
            </div>

            <div class="page-content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($action === 'edit' && $edit_branch): ?>
                    <!-- EDIT FORM -->
                    <div class="form-container">
                        <a href="?action=list" class="back-link">← Back to Branches</a>

                        <h3>Edit <?php echo htmlspecialchars($edit_branch['name']); ?></h3>
                        
                        <form method="POST" class="branch-form" enctype="multipart/form-data">
                            <input type="hidden" name="action_type" value="update">
                            <input type="hidden" name="branch_id" value="<?php echo $edit_branch['id']; ?>">

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Branch Name</label>
                                    <input 
                                        type="text" 
                                        value="<?php echo htmlspecialchars($edit_branch['name']); ?>"
                                        disabled
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Email *</label>
                                    <input 
                                        type="email" 
                                        name="email" 
                                        placeholder="branch@asmara.co.ke"
                                        value="<?php echo htmlspecialchars($edit_branch['email'] ?? ''); ?>"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Phone *</label>
                                    <input 
                                        type="tel" 
                                        name="phone" 
                                        placeholder="+254 7XX XXX XXX"
                                        value="<?php echo htmlspecialchars($edit_branch['phone'] ?? ''); ?>"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Capacity (Seats) *</label>
                                    <input 
                                        type="number" 
                                        name="capacity" 
                                        placeholder="50"
                                        value="<?php echo htmlspecialchars($edit_branch['capacity'] ?? 50); ?>"
                                        min="10"
                                        max="500"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Address *</label>
                                <textarea 
                                    name="address" 
                                    placeholder="Full address"
                                    rows="2"
                                    required
                                ><?php echo htmlspecialchars($edit_branch['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Opening Hours *</label>
                                <input 
                                    type="text" 
                                    name="opening_hours" 
                                    placeholder="e.g., 10:00 AM - 11:00 PM"
                                    value="<?php echo htmlspecialchars($edit_branch['opening_hours'] ?? ''); ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label>Hero Image</label>
                                <div class="upload-dropzone">
                                    <div class="thumbnail-preview" id="branch-image-preview">
                                        <?php if (!empty($edit_branch['hero_image'])): ?>
                                            <img src="<?php echo htmlspecialchars(admin_preview_src($edit_branch['hero_image'])); ?>" alt="current">
                                        <?php else: ?>
                                            <svg width="40" height="30" viewBox="0 0 24 24" fill="none"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 15l-5-5-4 4-7-7" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="upload-instructions">Upload a cover photo for this branch. Recommended: 1200x600 JPG/PNG.</div>
                                        <input type="file" name="hero_image" id="branch-image-input" accept="image/*" style="margin-top:8px;">
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="?action=list" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- SEARCH & FILTER FOR BRANCHES -->
                    <div class="filter-bar-premium">
                        <div class="filter-left-actions">
                            <input 
                                type="text" 
                                id="branch-search-input" 
                                placeholder="Search branches..." 
                                class="filter-search-input"
                                onkeyup="filterBranches()"
                            >
                        </div>
                        
                        <div class="view-toggle-container">
                            <button class="view-toggle-btn active" id="btnBranchCardView" title="Card View" onclick="setBranchViewMode('card')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M104 56v64a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8V56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v64a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8V56a8 8 0 0 0-8-8ZM104 144v56a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8v-56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v56a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8v-56a8 8 0 0 0-8-8Z"/></svg>
                            </button>
                            <button class="view-toggle-btn" id="btnBranchListView" title="List View" onclick="setBranchViewMode('list')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M80 64a8 8 0 0 1 8-8h120a8 8 0 0 1 0 16H88a8 8 0 0 1-8-8Zm120 48H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16Zm0 56H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16ZM44 52a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Z"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- BRANCHES LIST -->
                    <div class="items-display-wrapper">
                        <div class="branches-grid premium-branches card-view-mode" id="branchesDisplayGrid">
                            <?php foreach ($branches as $branch): ?>
                                <div class="branch-card-premium" data-name="<?php echo htmlspecialchars(strtolower($branch['name'])); ?>">
                                    <div class="branch-premium-hero">
                                        <?php if (!empty($branch['hero_image'])): ?>
                                            <img src="<?php echo htmlspecialchars(admin_preview_src($branch['hero_image'])); ?>" alt="<?php echo htmlspecialchars($branch['name']); ?>">
                                        <?php else: ?>
                                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#cbd5e1;">
                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/><path d="M21 15l-5-5-4 4-7-7"/></svg>
                                            </div>
                                        <?php endif; ?>
                                        <div class="branch-premium-badge">Capacity: <?php echo htmlspecialchars($branch['capacity']); ?> seats</div>
                                    </div>

                                    <div class="branch-premium-body">
                                        <div class="branch-premium-title"><?php echo htmlspecialchars($branch['name']); ?> Branch</div>
                                        
                                        <div class="branch-premium-info-list">
                                            <div class="branch-premium-info-row">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <div><strong>Address:</strong> <?php echo htmlspecialchars($branch['address'] ?? 'N/A'); ?></div>
                                            </div>

                                            <div class="branch-premium-info-row">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                <div><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($branch['phone']); ?>" style="color:var(--color-primary); font-weight:600;"><?php echo htmlspecialchars($branch['phone']); ?></a></div>
                                            </div>

                                            <div class="branch-premium-info-row">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                <div><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($branch['email']); ?>" style="color:var(--color-primary); font-weight:600;"><?php echo htmlspecialchars($branch['email']); ?></a></div>
                                            </div>

                                            <div class="branch-premium-info-row">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <div><strong>Hours:</strong> <?php echo htmlspecialchars($branch['opening_hours'] ?? 'N/A'); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="branch-premium-footer">
                                        <a href="?action=edit&id=<?php echo $branch['id']; ?>" class="action-btn" style="padding: 10px 24px; border-radius:10px;">Edit details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <script>
                        function filterBranches() {
                            const val = document.getElementById('branch-search-input').value.toLowerCase();
                            const cards = document.querySelectorAll('.branch-card-premium');
                            cards.forEach(card => {
                                const name = card.getAttribute('data-name');
                                if (name.includes(val)) {
                                    card.style.display = '';
                                } else {
                                    card.style.display = 'none';
                                }
                            });
                        }

                        function setBranchViewMode(mode) {
                            const grid = document.getElementById('branchesDisplayGrid');
                            const btnCard = document.getElementById('btnBranchCardView');
                            const btnList = document.getElementById('btnBranchListView');

                            if (mode === 'list') {
                                grid.className = 'branches-grid premium-branches list-view-mode';
                                btnList.classList.add('active');
                                btnCard.classList.remove('active');
                            } else {
                                grid.className = 'branches-grid premium-branches card-view-mode';
                                btnCard.classList.add('active');
                                btnList.classList.remove('active');
                            }
                        }
                    </script>
                <?php endif; ?>
            </div>

<?php include 'footer.php'; ?>
