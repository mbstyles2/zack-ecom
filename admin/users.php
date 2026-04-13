<?php
session_start();

$host = 'localhost';
$dbname = 'ecom';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$message = '';
$msg_type = 'success';
$editUser = null;

// ====================== HANDLE ACTIONS ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    if ($user_id > 0) {
        try {
            switch ($_POST['action']) {
                case 'suspend':
                    $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?")->execute([$user_id]);
                    $message = "User has been suspended.";
                    break;

                case 'activate':
                    $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$user_id]);
                    $message = "User has been activated.";
                    break;

                case 'delete':
                    $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([$user_id]);
                    $message = "User deleted successfully.";
                    break;
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $msg_type = 'error';
        }
    }
}

// ====================== LOAD USER FOR EDIT ======================
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("
        SELECT u.*, i.name as school_name 
        FROM users u 
        LEFT JOIN users i ON u.institution_id = i.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$id]);
    $editUser = $stmt->fetch();
}

// ====================== PAGINATION ======================
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// Total count
$totalStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('parent', 'student')");
$totalRecords = $totalStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Fetch users
$stmt = $pdo->prepare("
    SELECT u.*, i.name as school_name 
    FROM users u 
    LEFT JOIN users i ON u.institution_id = i.id 
    WHERE u.role IN ('parent', 'student')
    ORDER BY u.id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - ZACK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f9; margin:0; padding:20px; color:#333; }
        .container { max-width:1400px; margin:0 auto; background:white; padding:24px; border-radius:8px; box-shadow:0 2px 12px rgba(0,0,0,0.08); }
        h1 { color:#0d6efd; margin-bottom:1.5rem; }
        table { width:100%; border-collapse:collapse; margin-top:1rem; }
        th, td { padding:12px 15px; text-align:left; border-bottom:1px solid #ddd; }
        th { background:#e9ecef; font-weight:600; color:#495057; }
        tr:hover { background:#f8f9fa; }
        .actions button, .actions a {
            padding:6px 12px;
            border-radius:4px;
            color:white;
            border:none;
            cursor:pointer;
            font-size:0.9rem;
            text-decoration:none;
            display:inline-block;
            margin:2px;
        }
        .btn-edit { background:#0d6efd; }
        .btn-suspend { background:#f59e0b; }
        .btn-activate { background:#10b981; }
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
    <h1><i class="fas fa-users"></i> Parents & Students Management</h1>

    <?php if (!empty($message)): ?>
        <div class="msg <?= $msg_type === 'success' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <div class="no-data">No users found.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email / Phone</th>
                    <th>Linked School</th>
                    <th>County</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= ucfirst($u['role']) ?></td>
                    <td><?= htmlspecialchars($u['email'] ?? $u['phone']) ?></td>
                    <td><?= htmlspecialchars($u['school_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars(ucfirst($u['county'] ?? '')) ?></td>
                    <td><?= ucfirst($u['status']) ?></td>
                    <td class="actions">
                        <!-- Edit -->
                        <a href="users.php?edit=<?= $u['id'] ?>" class="btn-edit">Edit</a>

                        <!-- Suspend / Activate -->
                        <?php if ($u['status'] === 'active'): ?>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Suspend this user?');">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="suspend">
                                <button type="submit" class="btn-suspend">Suspend</button>
                            </form>
                        <?php else: ?>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Activate this user?');">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="activate">
                                <button type="submit" class="btn-activate">Activate</button>
                            </form>
                        <?php endif; ?>

                        <!-- Delete -->
                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this user permanently?');">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="margin-top:1.5rem; color:#6c757d; text-align:right;">
            Total users: <?= $totalRecords ?>
        </p>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination" style="margin-top:25px; text-align:center;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>">« Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
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