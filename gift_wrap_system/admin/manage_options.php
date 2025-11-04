<?php
require_once('includes/session.php');
require_once('../db_connect.php');

$message = '';
$styles = [];
$edit_style = null;

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. You do not have sufficient privileges.";
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['add_style']) || isset($_POST['edit_style']))) {
    $style_id = isset($_POST['style_id']) ? (int)$_POST['style_id'] : 0;
    $style_name = trim($_POST['style_name']);
    $description = trim($_POST['description']);
    $price_small = (float)trim($_POST['base_price_small']);
    $price_medium = (float)trim($_POST['base_price_medium']);
    $price_large = (float)trim($_POST['base_price_large']);
    $price_xl = (float)trim($_POST['base_price_xl']);

    try {
        if (isset($_POST['add_style'])) {
            $sql = "INSERT INTO wrapping_styles (style_name, description, base_price_small, base_price_medium, base_price_large, base_price_xl) 
                    VALUES (:name, :desc, :ps, :pm, :pl, :pxl)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $style_name,
                ':desc' => $description,
                ':ps' => $price_small,
                ':pm' => $price_medium,
                ':pl' => $price_large,
                ':pxl' => $price_xl
            ]);
            $message = "<div class='alert success'>New wrapping style added successfully!</div>";
        } elseif (isset($_POST['edit_style']) && $style_id > 0) {
            $sql = "UPDATE wrapping_styles SET style_name = :name, description = :desc, base_price_small = :ps, base_price_medium = :pm, base_price_large = :pl, base_price_xl = :pxl 
                    WHERE style_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $style_name,
                ':desc' => $description,
                ':ps' => $price_small,
                ':pm' => $price_medium,
                ':pl' => $price_large,
                ':pxl' => $price_xl,
                ':id' => $style_id
            ]);
            $message = "<div class='alert success'>Wrapping style updated successfully!</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert error'>Database error: " . $e->getMessage() . "</div>";
    }
}

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $style_id = (int)$_GET['id'];
    try {
        $sql = "DELETE FROM wrapping_styles WHERE style_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $style_id, PDO::PARAM_INT);
        $stmt->execute();
        $message = "<div class='alert success'>Wrapping style deleted successfully.</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert error'>Error deleting style. Check if it's referenced by existing requests.</div>";
    }
    // Redirect to clean up URL
    header("Location: manage_options.php");
    exit;
}

// Handle Edit Fetch
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $style_id = (int)$_GET['id'];
    try {
        $sql = "SELECT * FROM wrapping_styles WHERE style_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $style_id, PDO::PARAM_INT);
        $stmt->execute();
        $edit_style = $stmt->fetch();
    } catch (PDOException $e) {
        $message = "<div class='alert error'>Could not fetch style for editing.</div>";
    }
}

// Fetch all styles for display
try {
    $stmt = $pdo->query("SELECT * FROM wrapping_styles ORDER BY style_name");
    $styles = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "<div class='alert error'>Could not fetch wrapping styles.</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Options | Gift Wrap System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <div id="wrapper">
        <?php include('includes/sidebar.php'); ?>

        <div id="content-wrapper">
            <header>
                <h1>🛠 Manage Wrapping Options & Pricing</h1>
            </header>

            <main>
                <?php echo $message; ?>

                <section class="style-form-container card">
                    <h2><?php echo $edit_style ? 'Edit Style: ' . htmlspecialchars($edit_style['style_name']) : 'Add New Wrapping Style'; ?></h2>
                    <form method="POST" action="manage_options.php" class="style-form">
                        
                        <input type="hidden" name="style_id" value="<?php echo $edit_style ? $edit_style['style_id'] : ''; ?>">

                        <div class="form-group">
                            <label for="style_name">Style Name</label>
                            <input type="text" name="style_name" id="style_name" value="<?php echo $edit_style ? htmlspecialchars($edit_style['style_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="3"><?php echo $edit_style ? htmlspecialchars($edit_style['description']) : ''; ?></textarea>
                        </div>

                        <h3>Base Prices (₱)</h3>
                        <div class="price-group">
                            <div class="form-group price-input">
                                <label for="base_price_small">Small</label>
                                <input type="number" step="0.01" min="0" name="base_price_small" id="base_price_small" value="<?php echo $edit_style ? htmlspecialchars($edit_style['base_price_small']) : '0.00'; ?>" required>
                            </div>
                            <div class="form-group price-input">
                                <label for="base_price_medium">Medium</label>
                                <input type="number" step="0.01" min="0" name="base_price_medium" id="base_price_medium" value="<?php echo $edit_style ? htmlspecialchars($edit_style['base_price_medium']) : '0.00'; ?>" required>
                            </div>
                            <div class="form-group price-input">
                                <label for="base_price_large">Large</label>
                                <input type="number" step="0.01" min="0" name="base_price_large" id="base_price_large" value="<?php echo $edit_style ? htmlspecialchars($edit_style['base_price_large']) : '0.00'; ?>" required>
                            </div>
                            <div class="form-group price-input">
                                <label for="base_price_xl">X-Large</label>
                                <input type="number" step="0.01" min="0" name="base_price_xl" id="base_price_xl" value="<?php echo $edit_style ? htmlspecialchars($edit_style['base_price_xl']) : '0.00'; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-action">
                            <?php if ($edit_style): ?>
                                <button type="submit" name="edit_style" class="btn primary-btn">Save Changes</button>
                                <a href="manage_options.php" class="btn secondary-btn">Cancel Edit</a>
                            <?php else: ?>
                                <button type="submit" name="add_style" class="btn primary-btn">Add Style</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>

                <section class="style-list-container">
                    <h2>Current Wrapping Styles (<?php echo count($styles); ?>)</h2>
                    <table class="data-table full-width">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Style Name</th>
                                <th>Small Price</th>
                                <th>Medium Price</th>
                                <th>Large Price</th>
                                <th>X-Large Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($styles)): ?>
                                <?php foreach ($styles as $style): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($style['style_id']); ?></td>
                                        <td><?php echo htmlspecialchars($style['style_name']); ?></td>
                                        <td>₱<?php echo number_format($style['base_price_small'], 2); ?></td>
                                        <td>₱<?php echo number_format($style['base_price_medium'], 2); ?></td>
                                        <td>₱<?php echo number_format($style['base_price_large'], 2); ?></td>
                                        <td>₱<?php echo number_format($style['base_price_xl'], 2); ?></td>
                                        <td>
                                            <a href="manage_options.php?action=edit&id=<?php echo $style['style_id']; ?>" class="btn small-btn edit-btn">Edit</a>
                                            <a href="manage_options.php?action=delete&id=<?php echo $style['style_id']; ?>" onclick="return confirm('Are you sure you want to delete this style? This cannot be undone.');" class="btn small-btn delete-btn">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No wrapping styles defined.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
<?php 
unset($pdo); 
?>