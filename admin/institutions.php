<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}

$host = 'localhost'; $dbname = 'ecom'; $user = 'root'; $pass = '';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)$_POST['id'];
    if ($_POST['action'] === 'approve') {
        $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'institution'")->execute([$id]);
    } elseif ($_POST['action'] === 'suspend') {
        $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ? AND role = 'institution'")->execute([$id]);
    } elseif ($_POST['action'] === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'institution'")->execute([$id]);
    }
    header("Location: institutions.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institutions Management - ZACK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fe; margin:0; padding:20px; }
        .card { background:white; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); padding:25px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:15px; text-align:left; border-bottom:1px solid #eee; }
        th { background:#f8f9fa; }
        .btn { padding:8px 16px; border:none; border-radius:8px; cursor:pointer; margin:2px; }
        .btn-approve { background:#10b981; color:white; }
        .btn-suspend { background:#f59e0b; color:white; }
        .btn-delete { background:#ef4444; color:white; }
        .status-active { color:#10b981; font-weight:600; }
        .status-pending { color:#f59e0b; font-weight:600; }
        .status-suspended { color:#ef4444; font-weight:600; }
    </style>
</head>
<body>
<div class="card">
    <h2><i class="fas fa-school"></i> Institutions Management</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th><th>Email</th><th>Phone</th><th>School Code</th>
                <th>County</th><th>Status</th><th>Joined</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM users WHERE role = 'institution' ORDER BY created_at DESC");
            while ($row = $stmt->fetch()):
            ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['school_code'] ?? '—') ?></td>
                <td><?= htmlspecialchars(ucfirst($row['county'] ?? '')) ?></td>
                <td><span class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                <td>
                    <?php if ($row['status'] === 'pending'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="suspend" class="btn btn-suspend">Suspend</button>
                    </form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete permanently?')">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="delete" class="btn btn-delete">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>