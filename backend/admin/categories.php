<?php
/**
 * Categories Management Page
 * Add, Edit, Delete menu categories
 */

$page_title = 'Menu Categories | Asmara Admin';

require_once __DIR__ . '/../database/Connection.php';
require_once __DIR__ . '/../database/MenuRepository.php';
require_once __DIR__ . '/../security/Auth.php';
require_once __DIR__ . '/../security/Validator.php';

Auth::requireLogin();

$menuRepo = new MenuRepository();
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';

    if ($action_type === 'create') {
        $display_name = trim($_POST['display_name'] ?? '');
        // Generate a clean slug/name
        $name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $display_name));

        if (empty($display_name)) {
            $error = 'Display Name is required.';
        } elseif (empty($name)) {
            $error = 'Invalid category name.';
        } else {
            try {
                $menuRepo->createCategory($name, $display_name);
                Auth::logActivity(Auth::getCurrentUserId(), 'created', 'menu_categories', $name);
                $message = 'Category created successfully!';
                $action = 'list';
            } catch (Exception $e) {
                $error = 'Failed to create category: ' . $e->getMessage();
            }
        }
    } elseif ($action_type === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $display_name = trim($_POST['display_name'] ?? '');
        $name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $display_name));

        if ($id <= 0 || empty($display_name)) {
            $error = 'Invalid data provided.';
        } else {
            try {
                $menuRepo->updateCategory($id, $name, $display_name);
                Auth::logActivity(Auth::getCurrentUserId(), 'updated', 'menu_categories', $id);
                $message = 'Category updated successfully!';
                $action = 'list';
            } catch (Exception $e) {
                $error = 'Failed to update category: ' . $e->getMessage();
            }
        }
    } elseif ($action_type === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        try {
            $menuRepo->deleteCategory($id);
            Auth::logActivity(Auth::getCurrentUserId(), 'deleted', 'menu_categories', $id);
            $message = 'Category deleted successfully!';
            $action = 'list';
        } catch (Exception $e) {
            $error = 'Failed to delete category: ' . $e->getMessage();
        }
    }
}

$categories = $menuRepo->getCategoriesList();
$edit_category = null;

if ($action === 'edit' && isset($_GET['id'])) {
    $edit_category = $menuRepo->getCategoryById(intval($_GET['id']));
}
?>

<?php include 'header.php'; ?>

<div class="page-header">
    <h1 class="page-title">Menu Categories</h1>
    <p class="page-subtitle">Manage menu categories for food and drinks</p>
</div>

<div class="page-content">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start;">
        <!-- CATEGORIES LIST -->
        <div class="form-container" style="margin: 0; padding: 24px; background: #fff; border-radius: 12px; border: 1.5px solid var(--color-border);">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-family: 'Space Grotesk', sans-serif;">Existing Categories</h3>
            
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--color-border); color: var(--color-text-muted); font-size: 13px;">
                        <th style="padding: 12px 8px;">Display Name</th>
                        <th style="padding: 12px 8px;">System Name (Code)</th>
                        <th style="padding: 12px 8px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr style="border-bottom: 1px solid var(--color-border); font-size: 14px;">
                                <td style="padding: 14px 8px; font-weight: 600; color: var(--color-text);"><?php echo htmlspecialchars($cat['display_name']); ?></td>
                                <td style="padding: 14px 8px; color: var(--color-text-muted); font-family: monospace;"><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td style="padding: 14px 8px; text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <a href="?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Edit</a>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category? Any menu items in this category will need to be re-categorized.');">
                                            <input type="hidden" name="action_type" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                            <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; color: var(--color-danger); border-color: rgba(239,68,68,0.15);">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="padding: 24px; text-align: center; color: var(--color-text-muted);">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ADD / EDIT FORM -->
        <div class="form-container" style="margin: 0; padding: 24px; background: #fff; border-radius: 12px; border: 1.5px solid var(--color-border);">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-family: 'Space Grotesk', sans-serif;">
                <?php echo $action === 'edit' ? 'Edit Category' : 'Add New Category'; ?>
            </h3>
            
            <form method="POST">
                <input type="hidden" name="action_type" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                <?php if ($action === 'edit' && $edit_category): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Display Name *</label>
                    <input 
                        type="text" 
                        name="display_name" 
                        placeholder="e.g., Pizzas or Vegetarian Options"
                        value="<?php echo htmlspecialchars($edit_category['display_name'] ?? ''); ?>"
                        required
                        style="width: 100%; padding: 12px; border-radius: 8px; border: 1.5px solid var(--color-border); font-size: 14px;"
                    >
                </div>

                <div class="form-actions" style="margin-top: 24px; display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-weight: 600;">
                        <?php echo $action === 'edit' ? 'Update Category' : 'Create Category'; ?>
                    </button>
                    <?php if ($action === 'edit'): ?>
                        <a href="?action=list" class="btn btn-secondary" style="padding: 12px 24px;">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
