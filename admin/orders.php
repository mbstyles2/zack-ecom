<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit; }

$host = 'localhost'; $dbname = 'ecom'; $user = 'root'; $pass = '';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Update order status
if (isset($_POST['update_status'])) {
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")
        ->execute([$_POST['new_status'], $_POST['order_id']]);
    header("Location: orders.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - ZACK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fe; margin:0; padding:20px; }
        .card { background:white; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); padding:25px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:15px; text-align:left; border-bottom:1px solid #eee; }
        th { background:#f8f9fa; }
        select, button { padding:8px; border-radius:8px; }
    </style>
</head>
<body>
<div class="card">
    <h2><i class="fas fa-shopping-cart"></i> Orders Management</h2>
    <table>
        <thead>
            <tr><th>Order ID</th><th>Customer</th><th>School</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php
            $orders = $pdo->query("
                SELECT o.*, u.name as customer, i.name as school 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                JOIN users i ON o.institution_id = i.id 
                ORDER BY o.created_at DESC
            ")->fetchAll();
            foreach ($orders as $o):
            ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['customer']) ?></td>
                <td><?= htmlspecialchars($o['school']) ?></td>
                <td><?= $o['order_type'] ?></td>
                <td>KES <?= number_format($o['total_amount'], 2) ?></td>
                <td><?= ucfirst($o['status']) ?></td>
                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <select name="new_status">
                            <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>Pending</option>
                            <option value="quoted">Quoted</option>
                            <option value="approved">Approved</option>
                            <option value="paid">Paid</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                        </select>
                        <button type="submit" name="update_status" class="btn">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>