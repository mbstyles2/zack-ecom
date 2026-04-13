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
$editKit = null;

// ====================== PAGINATION ======================
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// Get total count
try {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM kits");
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    $totalRecords = 0;
    $totalPages = 1;
}

// ====================== HANDLE FORM SUBMISSIONS ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kit_id      = isset($_POST['kit_id']) ? (int)$_POST['kit_id'] : null;
    $name        = trim($_POST['name'] ?? '');
    $level       = trim($_POST['level'] ?? '');
    $class_grade = trim($_POST['class_grade'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $total_price = (float)($_POST['total_base_price'] ?? 0);

    if (empty($name) || empty($level) || empty($class_grade)) {
        $message = "Kit name, level and class/grade are required.";
        $msg_type = 'error';
    } else {
        try {
            if ($kit_id) {
                $stmt = $pdo->prepare("UPDATE kits SET name=?, level=?, class_grade=?, description=?, total_base_price=? WHERE id=?");
                $stmt->execute([$name, $level, $class_grade, $description, $total_price, $kit_id]);
                $message = "Kit updated successfully.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO kits (name, level, class_grade, description, total_base_price) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $level, $class_grade, $description, $total_price]);
                $message = "New kit created successfully.";
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
        $pdo->prepare("DELETE FROM kits WHERE id = ?")->execute([$id]);
        $message = "Kit deleted successfully.";
    } catch (Exception $e) {
        $message = "Cannot delete kit.";
        $msg_type = 'error';
    }
}

// ====================== LOAD KIT FOR EDIT ======================
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM kits WHERE id = ?");
    $stmt->execute([$id]);
    $editKit = $stmt->fetch();
}

// ====================== FETCH KITS WITH PAGINATION ======================
try {
    $stmt = $pdo->prepare("
        SELECT * FROM kits 
        ORDER BY id ASC          -- New kits appear at bottom (natural ordering)
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $kits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kits = [];
    $message = "Failed to load kits.";
    $msg_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kit Management - ZACK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
           body { font-family: 'Poppins', sans-serif; background: #f4f7fe; margin:0; padding:20px; }
        .card { background:white; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); padding:25px; margin-bottom:25px; }
        h2 { margin-top:0; color:#1e2937; }
        .form-group { margin-bottom:18px; }
        label { display:block; margin-bottom:6px; font-weight:600; color:#374151; }
        input, select, textarea { width:100%; padding:12px; border:1px solid #d1d5db; border-radius:8px; font-size:15px; }
        .btn { padding:12px 24px; border:none; border-radius:8px; cursor:pointer; font-weight:600; }
        .btn-primary { background:#f68b1e; color:white; }
        .btn-secondary { background:#64748b; color:white; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:15px; text-align:left; border-bottom:1px solid #e5e7eb; }
        th { background:#f8fafc; }
       
        .actions button {
            padding: 6px 12px;
            border-radius: 4px;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-delete { background: #dc3545; }
        .msg {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        .success { background: #d4edda; color: #155724; }
        .error   { background: #f8d7da; color: #842029; }
        .no-data { text-align: center; padding: 40px; color: #6c757d; font-style: italic; }

        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            text-decoration: none;
            color: #0d6efd;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="container">

    <h1><i class="fas fa-boxes"></i> Kit Management</h1>

    <?php if (!empty($message)): ?>
        <div class="msg <?= $msg_type === 'success' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Add / Edit Form -->
    <form method="POST" style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:30px;">
        <input type="hidden" name="kit_id" value="<?= $editKit['id'] ?? '' ?>">

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:15px;">
            <div>
                <label><strong>Kit Name</strong> <span style="color:red">*</span></label>
                <input type="text" name="name" required value="<?= htmlspecialchars($editKit['name'] ?? '') ?>" placeholder="e.g. Form 1 Full Kit">
            </div>
            <div>
                <label><strong>Level</strong> <span style="color:red">*</span></label>
                <select name="level" required>
                    <option value="">Select Level...</option>
                    <option value="Pre Primary" <?= ($editKit['level'] ?? '') === 'Pre Primary' ? 'selected' : '' ?>>Pre Primary</option>
                    <option value="Primary" <?= ($editKit['level'] ?? '') === 'Primary' ? 'selected' : '' ?>>Primary</option>
                    <option value="Junior Secondary" <?= ($editKit['level'] ?? '') === 'Junior Secondary' ? 'selected' : '' ?>>Junior Secondary</option>
                    <option value="High School" <?= ($editKit['level'] ?? '') === 'High School' ? 'selected' : '' ?>>High School</option>
                </select>
            </div>
            <div>
                <label><strong>Class / Grade</strong> <span style="color:red">*</span></label>
                <input type="text" name="class_grade" required value="<?= htmlspecialchars($editKit['class_grade'] ?? '') ?>" placeholder="e.g. Form 1">
            </div>
        </div>

        <div style="margin-top:15px;">
            <label><strong>Description</strong></label>
            <textarea name="description" rows="3"><?= htmlspecialchars($editKit['description'] ?? '') ?></textarea>
        </div>

        <div style="margin-top:15px;">
            <label><strong>Total Base Price (KES)</strong></label>
            <input type="number" step="0.01" name="total_base_price" value="<?= $editKit['total_base_price'] ?? '0.00' ?>">
        </div>

        <div style="margin-top:20px;">
            <?php if ($editKit): ?>
                <button type="submit" style="background:#0d6efd;color:white;padding:10px 20px;border:none;border-radius:4px;">Update Kit</button>
                <a href="kits.php" style="margin-left:10px;color:#6c757d;">Cancel</a>
            <?php else: ?>
                <button type="submit" style="background:#0d6efd;color:white;padding:10px 20px;border:none;border-radius:4px;">Create New Kit</button>
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($kits)): ?>
        <div class="no-data">No kits found.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kit Name</th>
                    <th>Level</th>
                    <th>Class/Grade</th>
                    <th>Total Price</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kits as $kit): ?>
                    <tr>
                        <td><?= htmlspecialchars($kit['id']) ?></td>
                        <td><?= htmlspecialchars($kit['name']) ?></td>
                        <td><?= htmlspecialchars($kit['level']) ?></td>
                        <td><?= htmlspecialchars($kit['class_grade']) ?></td>
                        <td>KES <?= number_format($kit['total_base_price'], 2) ?></td>
                        <td><?= date('d M Y H:i', strtotime($kit['created_at'])) ?></td>
                        <td class="actions">
                            <a href="kits.php?edit=<?= $kit['id'] ?>" style="color:#0d6efd;margin-right:12px;">Edit</a>
                            <form method="get" style="display:inline;" onsubmit="return confirm('Delete this kit permanently?');">
                                <input type="hidden" name="delete" value="<?= $kit['id'] ?>">
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="margin-top:1.5rem; color:#6c757d; text-align:right;">
            Total kits: <?= $totalRecords ?>
        </p>

        <!-- Pagination - Same style as user-interest.php -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination" style="margin-top:25px; text-align:center;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>">« Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>">Next »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

</body>
</html>


 