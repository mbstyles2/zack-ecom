<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$host = 'localhost';
$dbname = 'ecom';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$message = '';
$msg_type = 'success';
$editProduct = null;

// ====================== PAGINATION ======================
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// Get total count
try {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    $totalRecords = 0;
    $totalPages = 1;
}

// ====================== HANDLE FORM ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $base_price  = (float)($_POST['base_price'] ?? 0);
    $category    = trim($_POST['category'] ?? '');
    $stock       = (int)($_POST['stock'] ?? 0);
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name) || empty($category) || $base_price <= 0) {
        $message = "Product name, category and price are required.";
        $msg_type = 'error';
    } else {
        try {
            if ($product_id) {
                $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, base_price=?, category=?, stock=?, is_active=? WHERE id=?");
                $stmt->execute([$name, $description, $base_price, $category, $stock, $is_active, $product_id]);
                $message = "Product updated successfully.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, base_price, category, stock, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $base_price, $category, $stock, $is_active]);
                $message = "New product created successfully.";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $msg_type = 'error';
        }
    }
}

// ====================== HANDLE DELETE ======================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $message = "Product deleted successfully.";
    } catch (Exception $e) {
        $message = "Cannot delete: Product may be in use.";
        $msg_type = 'error';
    }
}

// Load for editing
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editProduct = $stmt->fetch();
}

// ====================== FETCH PRODUCTS ======================
try {
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        ORDER BY id ASC          -- Changed to id DESC so newest appears at bottom
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    $message = "Failed to load products.";
    $msg_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - ZACK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f9; margin:0; padding:20px; color:#333; }
        .container { max-width:1400px; margin:0 auto; background:white; padding:24px; border-radius:8px; box-shadow:0 2px 12px rgba(0,0,0,0.08); }
        h1 { color:#0d6efd; margin-bottom:1.5rem; }
        .card { 
            background:white; 
            border-radius:12px; 
            box-shadow:0 4px 15px rgba(0,0,0,0.05); 
            padding:25px; 
            margin-bottom:25px; 
        }
        h2 { margin-top:0; color:#1e2937; }
        .form-group { margin-bottom:18px; }
        label { 
            display:block; 
            margin-bottom:6px; 
            font-weight:600; 
            color:#374151; 
        }
        input, select, textarea { 
            width:60%; 
            padding:12px; 
            margin:20px;
            border:1px solid #d1d5db; 
            border-radius:8px; 
            font-size:15px; 
        }
        table { width:100%; border-collapse:collapse; margin-top:1rem; }
        th, td { padding:12px 15px; text-align:left; border-bottom:1px solid #ddd; }
        th { background:#e9ecef; font-weight:600; color:#495057; }
        tr:hover { background:#f8f9fa; }
        .actions button { padding:6px 12px; border-radius:4px; color:white; border:none; cursor:pointer; font-size:0.9rem; }
        .btn-delete { background:#dc3545; }
        .msg { padding:12px 16px; border-radius:6px; margin-bottom:1.5rem; }
        .success { background:#d4edda; color:#155724; }
        .error   { background:#f8d7da; color:#842029; }
        .no-data { text-align:center; padding:40px; color:#6c757d; font-style:italic; }

        .pagination a {
            margin:0 5px;
            padding:8px 12px;
            text-decoration:none;
            color:#0d6efd;
            border:1px solid #ddd;
            border-radius:4px;
        }
        .pagination a.active {
            background:#0d6efd;
            color:white;
            border-color:#0d6efd;
        }
    </style>
</head>
<body>

<div class="container">

    <h1><i class="fas fa-box"></i> Product Management</h1>

    <?php if (!empty($message)): ?>
        <div class="msg <?= $msg_type === 'success' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:30px;">
        <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?? '' ?>">

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:15px;">
            <div>
                <label><strong>Product Name</strong> <span style="color:red">*</span></label>
                <input type="text" name="name" required value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" placeholder="e.g. School Blazer">
            </div>
            <div>
                <label><strong>Category</strong> <span style="color:red">*</span></label>
                <select name="category" required>
                    <option value="">Select...</option>
                    <option value="stationery" <?= ($editProduct['category']??'')==='stationery'?'selected':'' ?>>Stationery</option>
                    <option value="uniform" <?= ($editProduct['category']??'')==='uniform'?'selected':'' ?>>Uniform</option>
                    <option value="lab" <?= ($editProduct['category']??'')==='lab'?'selected':'' ?>>Lab</option>
                    <option value="kit_component" <?= ($editProduct['category']??'')==='kit_component'?'selected':'' ?>>Kit Component</option>
                    <option value="other" <?= ($editProduct['category']??'')==='other'?'selected':'' ?>>Other</option>
                </select>
            </div>
            <div>
                <label><strong>Base Price (KES)</strong> <span style="color:red">*</span></label>
                <input type="number" step="0.01" name="base_price" required value="<?= $editProduct['base_price'] ?? '' ?>">
            </div>
            <div>
                <label><strong>Stock</strong></label>
                <input type="number" name="stock" value="<?= $editProduct['stock'] ?? 0 ?>">
            </div>
        </div>

        <div style="margin-top:15px;">
            <label>
                <input type="checkbox" name="is_active" <?= ($editProduct['is_active'] ?? 1) ? 'checked' : '' ?>>
                <strong>Active</strong>
            </label>
        </div>

        <div style="margin-top:20px;">
            <?php if ($editProduct): ?>
                <button type="submit" style="background:#0d6efd;color:white;padding:10px 20px;border:none;border-radius:4px;">Update Product</button>
                <a href="product.php" style="margin-left:10px;color:#6c757d;">Cancel</a>
            <?php else: ?>
                <button type="submit" style="background:#0d6efd;color:white;padding:10px 20px;border:none;border-radius:4px;">Create New Product</button>
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($products)): ?>
        <div class="no-data">No products found.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= ucfirst($p['category']) ?></td>
                    <td>KES <?= number_format($p['base_price'], 2) ?></td>
                    <td><?= number_format($p['stock']) ?></td>
                    <td><?= $p['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
                    <td class="actions">
                        <a href="product.php?edit=<?= $p['id'] ?>" style="color:#0d6efd;margin-right:12px;">Edit</a>
                        <form method="get" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="delete" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="margin-top:1.5rem; color:#6c757d; text-align:right;">
            Total products: <?= $totalRecords ?>
        </p>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination" style="margin-top:25px; text-align:center;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page-1 ?>">« Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page+1 ?>">Next »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
</body>
</html>