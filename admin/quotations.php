<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit; }

$host = 'localhost'; $dbname = 'ecom'; $user = 'root'; $pass = '';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Requests - ZACK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fe; margin:0; padding:20px; }
        .card { background:white; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); padding:25px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:15px; text-align:left; border-bottom:1px solid #eee; }
        th { background:#f8f9fa; }
        .btn { padding:10px 18px; background:#f68b1e; color:white; border:none; border-radius:8px; cursor:pointer; }
    </style>
</head>
<body>
<div class="card">
    <h2><i class="fas fa-file-invoice"></i> Quotation Requests</h2>
    <table>
        <thead>
            <tr><th>Institution</th><th>Order Type</th><th>Items Requested</th><th>Date</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php
            // For now show quotation-like orders (you can filter better later)
            $reqs = $pdo->query("
                SELECT o.*, u.name as inst_name 
                FROM orders o 
                JOIN users u ON o.institution_id = u.id 
                WHERE o.order_type IN ('quotation_request', 'full_kit', 'bulk_order')
                ORDER BY o.created_at DESC
            ")->fetchAll();
            foreach ($reqs as $r):
            ?>
            <tr>
                <td><?= htmlspecialchars($r['inst_name']) ?></td>
                <td><?= $r['order_type'] ?></td>
                <td><?= $r['notes'] ? htmlspecialchars($r['notes']) : 'Full kit / Bulk' ?></td>
                <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                <td>
                    <button onclick="alert('Respond to quotation #<?= $r['id'] ?> - implement response form')" class="btn">Respond with Quote</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>