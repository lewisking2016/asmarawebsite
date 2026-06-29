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

            <div class="page-header">
                <h1 class="page-title">Menu Management</h1>
                <p class="page-subtitle">Add, edit, or remove menu items</p>
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
                        <h3><?php echo $action === 'create' ? 'Add New Menu Item' : 'Edit Menu Item'; ?></h3>
                        
                        <form method="POST" class="menu-form" enctype="multipart/form-data">
                            <input type="hidden" name="action_type" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                            <?php if ($action === 'edit' && $edit_item): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                            <?php endif; ?>

                            <div class="form-group">
                                <label>Item Name *</label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    placeholder="e.g., Injera & Wat"
                                    value="<?php echo $edit_item['name'] ?? ''; ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea 
                                    name="description" 
                                    placeholder="Describe this menu item"
                                    rows="4"
                                ><?php echo $edit_item['description'] ?? ''; ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Category *</label>
                                    <select name="category" required>
                                        <option value="">Select category</option>
                                        <option value="appetizers" <?php echo ($edit_item['category'] ?? '') === 'appetizers' ? 'selected' : ''; ?>>Appetizers</option>
                                        <option value="mains" <?php echo ($edit_item['category'] ?? '') === 'mains' ? 'selected' : ''; ?>>Main Courses</option>
                                        <option value="desserts" <?php echo ($edit_item['category'] ?? '') === 'desserts' ? 'selected' : ''; ?>>Desserts</option>
                                        <option value="drinks" <?php echo ($edit_item['category'] ?? '') === 'drinks' ? 'selected' : ''; ?>>Drinks</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Price (KES) *</label>
                                    <input 
                                        type="number" 
                                        name="price" 
                                        placeholder="0.00"
                                        step="0.01"
                                        value="<?php echo $edit_item['price'] ?? ''; ?>"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 24px;">
                                <label style="display:block; margin-bottom:8px; font-weight:600;">Available Locations / Branches *</label>
                                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; padding: 16px; background: #f8fafc; border: 1.5px solid var(--color-border); border-radius: 12px;">
                                    <?php 
                                    $current_branches = [];
                                    if (!empty($edit_item['available_branches'])) {
                                        $current_branches = json_decode($edit_item['available_branches'], true) ?: [];
                                    }
                                    foreach ($all_branches as $branch): 
                                        $is_checked = in_array((string)$branch['id'], array_map('strval', $current_branches));
                                    ?>
                                        <label style="display:flex; align-items:center; gap:8px; font-weight:500; cursor:pointer;">
                                            <input type="checkbox" name="branches[]" value="<?php echo $branch['id']; ?>" <?php echo $is_checked ? 'checked' : ''; ?> style="width:18px; height:18px; accent-color:var(--color-primary);">
                                            <span><?php echo htmlspecialchars($branch['name']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <small style="color:var(--color-text-muted); display:block; margin-top:4px;">Select which restaurant branches offer this menu item.</small>
                            </div>

                            <div class="form-group">
                                <label>Image</label>
                                <div class="upload-dropzone">
                                    <div class="thumbnail-preview" id="menu-image-preview">
                                        <?php if (!empty($edit_item['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars(admin_preview_src($edit_item['image_url'])); ?>" alt="current">
                                        <?php else: ?>
                                            <svg width="48" height="36" viewBox="0 0 24 24" fill="none"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke="#e2e8f0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 15l-5-5-4 4-7-7" stroke="#e2e8f0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="upload-instructions">Click to choose or drag & drop an image. Recommended: 800x600 JPG/PNG.</div>
                                        <input type="file" name="image" id="menu-image-input" accept="image/*" style="margin-top:8px;">
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $action === 'edit' ? 'Update Item' : 'Add Item'; ?>
                                </button>
                                <a href="?action=list" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- MENU ITEMS LIST WITH SAAS CONTROLS -->
                    <div class="filter-bar-premium">
                        <div class="filter-left-actions">
                            <a href="?action=create" class="action-btn">+ Add New Item</a>
                            
                            <input 
                                type="text" 
                                id="menu-search-input" 
                                placeholder="Search menu items..." 
                                class="filter-search-input"
                                onkeyup="filterItems()"
                            >

                            <select id="category-filter" class="filter-select" onchange="filterItems()">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="view-toggle-container">
                            <button class="view-toggle-btn active" id="btnCardView" title="Card View" onclick="setViewMode('card')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M104 56v64a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8V56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v64a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8V56a8 8 0 0 0-8-8ZM104 144v56a8 8 0 0 1-8 8H40a8 8 0 0 1-8-8v-56a8 8 0 0 1 8-8h56a8 8 0 0 1 8 8Zm112-8h-56a8 8 0 0 0-8 8v56a8 8 0 0 0 8 8h56a8 8 0 0 0 8-8v-56a8 8 0 0 0-8-8Z"/></svg>
                            </button>
                            <button class="view-toggle-btn" id="btnListView" title="List View" onclick="setViewMode('list')">
                                <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor"><path d="M80 64a8 8 0 0 1 8-8h120a8 8 0 0 1 0 16H88a8 8 0 0 1-8-8Zm120 48H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16Zm0 56H88a8 8 0 0 0 0 16h120a8 8 0 0 0 0-16ZM44 52a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Zm0 48a12 12 0 1 0 12 12 12 12 0 0 0-12-12Z"/></svg>
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($items)): ?>
                        <div class="items-display-wrapper">
                            <div class="items-grid card-view-mode" id="itemsDisplayGrid">
                                <?php foreach ($items as $item): ?>
                                    <div class="item-card" data-category="<?php echo htmlspecialchars($item['category']); ?>" data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>">
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
                                                <span class="status-badge" style="background:rgba(0,0,0,0.05); color:var(--color-text); padding:4px 10px; font-size:11px;"><?php echo ucfirst($item['category']); ?></span>
                                                <span class="status-badge <?php echo $item['is_available'] ? 'status-confirmed' : 'status-cancelled'; ?>">
                                                    <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                                                </span>
                                            </div>
                                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <p class="item-description"><?php echo htmlspecialchars($item['description'] ?? 'No description provided.'); ?></p>
                                            <div class="item-price">KES <?php echo number_format($item['price'], 2); ?></div>
                                            <?php 
                                            $item_branches = [];
                                            if (!empty($item['available_branches'])) {
                                                $branch_ids = json_decode($item['available_branches'], true) ?: [];
                                                foreach ($all_branches as $b) {
                                                    if (in_array((string)$b['id'], array_map('strval', $branch_ids))) {
                                                        $item_branches[] = $b['name'];
                                                    }
                                                }
                                            }
                                            ?>
                                            <?php if (!empty($item_branches)): ?>
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
                                                    <span style="font-size:11px; color:var(--color-text-muted); font-style:italic;">All locations</span>
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
                    const items = document.querySelectorAll('.item-card');

                    items.forEach(item => {
                        const name = item.getAttribute('data-name');
                        const category = item.getAttribute('data-category');

                        const matchesSearch = name.includes(searchVal);
                        const matchesCategory = categoryVal === '' || category === categoryVal;

                        if (matchesSearch && matchesCategory) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                }

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
