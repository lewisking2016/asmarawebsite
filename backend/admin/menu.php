<?php
/**
 * Menu Management Page
 * Add, Edit, Delete menu items
 */

$page_title = 'Menu Management | Asmara Admin';

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../database/MenuRepository.php';
require_once __DIR__ . '/../database/BranchRepository.php';
require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Validator.php';
require_once __DIR__ . '/../security/ImageOptimizer.php';

Auth::requireLogin();

$menuRepo = new MenuRepository();
$branchRepo = new BranchRepository();
$all_branches = $branchRepo->getAll();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';

    if ($action_type === 'create') {
        $selected_branches = $_POST['branches'] ?? [];
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'is_available' => true,
            'available_branches' => !empty($selected_branches) ? json_encode($selected_branches) : null,
        ];

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/menu/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $tmp = $_FILES['image']['tmp_name'];
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('menu_') . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($tmp, $dest)) {
                ImageOptimizer::optimize($dest);
                $data['image_url'] = '/backend/uploads/menu/' . $filename;
            }
        }

        if (Validator::validateMenuItem($data)) {
            $id = $menuRepo->create($data);
            Auth::logActivity(Auth::getCurrentUserId(), 'created', 'menu_items', $id);
            $message = 'Menu item created successfully!';
            $action = 'list';
        } else {
            $error = Validator::getFirstError();
        }
    } elseif ($action_type === 'update') {
        $id = $_POST['id'] ?? 0;
        $selected_branches = $_POST['branches'] ?? [];
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'available_branches' => !empty($selected_branches) ? json_encode($selected_branches) : null,
        ];
        // Handle image upload on update
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/menu/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $tmp = $_FILES['image']['tmp_name'];
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('menu_') . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($tmp, $dest)) {
                ImageOptimizer::optimize($dest);
                $data['image_url'] = '/backend/uploads/menu/' . $filename;
            }
        }

        if (Validator::validateMenuItem($data)) {
            $menuRepo->update($id, $data);
            Auth::logActivity(Auth::getCurrentUserId(), 'updated', 'menu_items', $id);
            $message = 'Menu item updated successfully!';
            $action = 'list';
        } else {
            $error = Validator::getFirstError();
        }
    } elseif ($action_type === 'delete') {
        $id = $_POST['id'] ?? 0;
        $menuRepo->delete($id);
        Auth::logActivity(Auth::getCurrentUserId(), 'deleted', 'menu_items', $id);
        $message = 'Menu item deleted successfully!';
        $action = 'list';
    } elseif ($action_type === 'toggle_availability') {
        $id = $_POST['id'] ?? 0;
        $menuRepo->toggleAvailability($id);
        Auth::logActivity(Auth::getCurrentUserId(), 'updated', 'menu_items', $id);
        $action = 'list';
    }
}

$categories = $menuRepo->getCategories();
$categories_list = $menuRepo->getCategoriesList();
$category_display_names = [];
foreach ($categories_list as $c) {
    $category_display_names[$c['name']] = $c['display_name'];
}
$items = $menuRepo->getAll();
$edit_item = null;

if ($action === 'edit' && isset($_GET['id'])) {
    $edit_item = $menuRepo->getById($_GET['id']);
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
    if (strpos($url, '/backend/uploads/menu/') === 0) {
        return $url;
    }
    // remove leading ../ so path becomes absolute from webroot
    $p = preg_replace('/^\.\./', '', $url);
    if ($p === '') return '';
    if ($p[0] !== '/') $p = '/' . $p;
    return $p;
}
?>

            <div class="ad-page-header">
                <span class="ad-page-eyebrow">✦ Menu</span>
                <h1>Menu Management</h1>
                <p>Add, edit, or remove menu items &mdash; and pick which branches serve each dish.</p>
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
                    <div class="ad-card ad-card-tinted ad-card-padded-lg" style="max-width: 880px; margin: 0 auto;">
                        <a href="?action=list" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:var(--ad-rose); text-decoration:none; margin-bottom:16px;">&larr; Back to menu</a>

                        <div class="ad-card-header" style="border:0; padding: 4px 0 22px;">
                            <div>
                                <span class="ad-page-eyebrow" style="margin-bottom:8px;"><?php echo $action === 'edit' ? 'Editing' : 'New item'; ?></span>
                                <h3 style="font-family:'Playfair Display', Georgia, serif; font-size:26px; line-height:1.15; color:var(--ad-ink); margin:0;">
                                    <?php echo $action === 'create' ? 'Add a Menu Item' : 'Edit Menu Item'; ?>
                                </h3>
                            </div>
                        </div>

                        <form method="POST" class="menu-form" enctype="multipart/form-data">
                            <input type="hidden" name="action_type" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                            <?php if ($action === 'edit' && $edit_item): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                            <?php endif; ?>

                            <div class="ad-field">
                                <label class="ad-label ad-required" for="menu-name">Item Name</label>
                                <input id="menu-name" class="ad-input" type="text" name="name" placeholder="e.g., Injera & Wat" value="<?php echo htmlspecialchars($edit_item['name'] ?? ''); ?>" required>
                            </div>

                            <div class="ad-field">
                                <label class="ad-label" for="menu-description">Description</label>
                                <textarea id="menu-description" class="ad-textarea" name="description" placeholder="Describe this menu item&hellip;" rows="4"><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea>
                                <div class="ad-hint">Plain text. Keep it between 1&ndash;3 sentences.</div>
                            </div>

                            <div class="ad-grid-2">
                                <div class="ad-field">
                                    <label class="ad-label ad-required" for="menu-category">Category</label>
                                    <div class="ad-select-wrap">
                                        <select id="menu-category" class="ad-select" name="category" required>
                                            <option value="">Select category</option>
                                            <?php foreach ($categories_list as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo ($edit_item['category'] ?? '') === $cat['name'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['display_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="ad-field">
                                    <label class="ad-label ad-required" for="menu-price">Price (KES)</label>
                                    <div class="ad-input-group ad-with-leading">
                                        <span class="ad-leading" style="font-size:13px; font-weight:700; color:var(--ad-ink-3);">KES</span>
                                        <input id="menu-price" class="ad-input" type="number" name="price" placeholder="0.00" step="0.01" value="<?php echo $edit_item['price'] ?? ''; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="ad-field">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                    <label class="ad-label" style="margin:0;">Available Branches</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer; color:var(--ad-rose);">
                                        <input type="checkbox" id="selectAllBranches" onchange="toggleAllBranches(this)" style="width:16px; height:16px; accent-color:var(--ad-rose);">
                                        <span>All Branches</span>
                                    </label>
                                </div>
                                <div class="ad-branch-grid">
                                    <?php
                                    $current_branches = [];
                                    if (!empty($edit_item['available_branches'])) {
                                        $current_branches = json_decode($edit_item['available_branches'], true) ?: [];
                                    }
                                    foreach ($all_branches as $branch):
                                        $is_checked = in_array((string)$branch['id'], array_map('strval', $current_branches));
                                    ?>
                                        <label class="ad-branch-chip">
                                            <input type="checkbox" name="branches[]" class="branch-checkbox" value="<?php echo $branch['id']; ?>" <?php echo $is_checked ? 'checked' : ''; ?> onchange="updateSelectAllState()">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            <span><?php echo htmlspecialchars($branch['name']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="ad-hint">Select which restaurant branches offer this menu item.</div>
                            </div>

                            <div class="ad-field">
                                <label class="ad-label" for="menu-image-input">Item Image</label>
                                <div class="ad-cover-uploader">
                                    <div id="menu-image-preview" class="ad-cover-preview">
                                        <?php if (!empty($edit_item['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars(admin_preview_src($edit_item['image_url'])); ?>" alt="current">
                                        <?php else: ?>
                                            <div class="ad-cover-empty">
                                                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke="currentColor" stroke-width="1.5"/><path d="M21 15l-5-5-4 4-7-7" stroke="currentColor" stroke-width="1.5"/></svg>
                                                <strong>Upload an item photo</strong>
                                                <span>Drag &amp; drop or click to browse &mdash; 800x600 recommended</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ad-cover-actions">
                                        <label for="menu-image-input" class="ad-btn ad-btn-ghost" style="cursor:pointer;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                            <?php echo !empty($edit_item['image_url']) ? 'Replace image' : 'Choose image'; ?>
                                        </label>
                                        <input type="file" name="image" id="menu-image-input" accept="image/*" class="ad-cover-file">
                                    </div>
                                    <div class="ad-hint">Recommended 800x600 JPG/PNG. Images are auto-compressed on upload.</div>
                                </div>
                            </div>

                            <div class="ad-form-actions">
                                <button type="submit" class="ad-btn ad-btn-primary ad-btn-lg"><?php echo $action === 'edit' ? 'Update Item' : 'Add Item'; ?></button>
                                <a href="?action=list" class="ad-btn ad-btn-ghost ad-btn-lg">Cancel</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- MENU ITEMS LIST WITH SAAS CONTROLS -->
                    <div class="ad-toolbar">
                        <div class="ad-toolbar-section">
                            <a href="?action=create" class="ad-btn ad-btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                                New Menu Item
                            </a>

                            <label for="menu-search-input" class="ad-search">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                <input id="menu-search-input" type="text" placeholder="Search menu items&hellip;" onkeyup="filterItems()">
                            </label>

                            <div class="ad-select-wrap">
                                <select id="category-filter" class="ad-select ad-trigger" onchange="filterItems()">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories_list as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['display_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="ad-select-wrap">
                                <select id="branch-filter" class="ad-select ad-trigger" onchange="filterItems()">
                                    <option value="">View All Locations</option>
                                    <option value="all">All Branches Only</option>
                                    <?php foreach ($all_branches as $branch): ?>
                                        <option value="<?php echo htmlspecialchars($branch['id']); ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="ad-segmented" role="tablist" aria-label="View mode">
                            <button id="btnCardView" class="is-active" title="Card view" aria-label="Card view" onclick="setViewMode('card')">
                                <svg width="16" height="16" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M104 56v64a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8V56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v64a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8V56a8 8 0 0 0-8-8ZM104 144v56a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8v-56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v56a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8v-56a8 8 0 0 0-8-8Z"/></svg>
                            </button>
                            <button id="btnListView" title="List view" aria-label="List view" onclick="setViewMode('list')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M80 64a8 8 0 0 1 8-8h120a8 8 0 0 1 0 16H88a8 8 0 0 1-8-8Zm120 48H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16Zm0 56H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16ZM44 52a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Z"/></svg>
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($items)): ?>
                        <div class="items-display-wrapper">
                            <div class="items-grid card-view-mode" id="itemsDisplayGrid">
                                <?php foreach ($items as $item): ?>
                                    <?php 
                                    $item_branches = [];
                                    $branch_ids = [];
                                    if (!empty($item['available_branches'])) {
                                        $branch_ids = json_decode($item['available_branches'], true) ?: [];
                                        foreach ($all_branches as $b) {
                                            if (in_array((string)$b['id'], array_map('strval', $branch_ids))) {
                                                $item_branches[] = $b['name'];
                                            }
                                        }
                                    }
                                    $is_all_branches = empty($item['available_branches']) || count($branch_ids) >= count($all_branches);
                                    ?>
                                    <div class="item-card" 
                                         data-category="<?php echo htmlspecialchars($item['category']); ?>" 
                                         data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>"
                                         data-branches="<?php echo $is_all_branches ? 'all' : htmlspecialchars(json_encode(array_map('strval', $branch_ids))); ?>">
                                        <div class="item-media-container">
                                            <?php if (!empty($item['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars(admin_preview_src($item['image_url'])); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <?php else: ?>
                                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#cbd5e1;">
                                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/><path d="M21 15l-5-5-4 4-7-7"/></svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="item-content">
                                            <div class="item-meta">
                                                <span class="status-badge" style="background:rgba(0,0,0,0.05); color:var(--color-text); padding:4px 10px; font-size:11px;"><?php echo htmlspecialchars($category_display_names[$item['category']] ?? ucfirst($item['category'])); ?></span>
                                                <span class="status-badge <?php echo $item['is_available'] ? 'status-confirmed' : 'status-cancelled'; ?>">
                                                    <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                                                </span>
                                            </div>
                                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <p class="item-description"><?php echo htmlspecialchars($item['description'] ?? 'No description provided.'); ?></p>
                                            <div class="item-price">KES <?php echo number_format($item['price'], 2); ?></div>
                                            
                                            <?php if (!$is_all_branches && !empty($item_branches)): ?>
                                                <div class="item-branches-row" style="display:flex; flex-wrap:wrap; gap:6px; margin-top:4px;">
                                                    <?php foreach ($item_branches as $bname): ?>
                                                        <span style="display:inline-flex; align-items:center; gap:4px; background:rgba(237,23,75,0.06); color:var(--color-primary); padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600;">
                                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                            <?php echo htmlspecialchars($bname); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div style="margin-top:4px;">
                                                    <span style="display:inline-flex; align-items:center; gap:4px; background:rgba(40,167,69,0.08); color:#28a745; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600;">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                        All Branches
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="item-footer">
                                            <div style="display:flex; gap:8px; width:100%;">
                                                <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-secondary" style="flex:1; padding:10px; font-size:13px; text-align:center;">Edit</a>
                                                
                                                <form method="POST" style="display:inline; flex:1;" onsubmit="return confirm('Delete this menu item?');">
                                                    <input type="hidden" name="action_type" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-secondary" style="width:100%; padding:10px; font-size:13px; color:var(--color-danger); border-color:rgba(239,68,68,0.15);">Delete</button>
                                                </form>

                                                <form method="POST" style="display:inline; flex:1;">
                                                    <input type="hidden" name="action_type" value="toggle_availability">
                                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-primary" style="width:100%; padding:10px; font-size:13px; font-weight:600;">
                                                        <?php echo $item['is_available'] ? 'Disable' : 'Enable'; ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No menu items yet. <a href="?action=create">Add one now</a></p>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>

            <script>
                function filterItems() {
                    const searchVal = document.getElementById('menu-search-input').value.toLowerCase();
                    const categoryVal = document.getElementById('category-filter').value;
                    const branchVal = document.getElementById('branch-filter') ? document.getElementById('branch-filter').value : '';
                    const items = document.querySelectorAll('.item-card');

                    items.forEach(item => {
                        const name = item.getAttribute('data-name');
                        const category = item.getAttribute('data-category');
                        const rawBranches = item.getAttribute('data-branches') || 'all';

                        const matchesSearch = name.includes(searchVal);
                        const matchesCategory = categoryVal === '' || category === categoryVal;
                        
                        let matchesBranch = true;
                        if (branchVal === '') {
                            matchesBranch = true;
                        } else if (branchVal === 'all') {
                            matchesBranch = (rawBranches === 'all');
                        } else {
                            if (rawBranches === 'all') {
                                matchesBranch = true;
                            } else {
                                try {
                                    const bArr = JSON.parse(rawBranches);
                                    matchesBranch = Array.isArray(bArr) && bArr.map(String).includes(String(branchVal));
                                } catch (e) {
                                    matchesBranch = true;
                                }
                            }
                        }

                        if (matchesSearch && matchesCategory && matchesBranch) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                }

                function toggleAllBranches(master) {
                    document.querySelectorAll('.branch-checkbox').forEach(cb => {
                        cb.checked = master.checked;
                    });
                }

                function updateSelectAllState() {
                    const cbs = document.querySelectorAll('.branch-checkbox');
                    const master = document.getElementById('selectAllBranches');
                    if (master && cbs.length) {
                        master.checked = Array.from(cbs).every(cb => cb.checked);
                    }
                }

                document.addEventListener('DOMContentLoaded', updateSelectAllState);

                function setViewMode(mode) {
                    const grid = document.getElementById('itemsDisplayGrid');
                    const btnCard = document.getElementById('btnCardView');
                    const btnList = document.getElementById('btnListView');

                    if (mode === 'list') {
                        grid.className = 'items-grid list-view-mode';
                        btnList.classList.add('active');
                        btnCard.classList.remove('active');
                    } else {
                        grid.className = 'items-grid card-view-mode';
                        btnCard.classList.add('active');
                        btnList.classList.remove('active');
                    }
                }
            </script>

<?php include 'footer.php'; ?>
