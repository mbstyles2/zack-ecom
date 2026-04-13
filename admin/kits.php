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
    <title>Kit Management - ZACK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fe; margin:0; padding:20px; }
        .card { background:white; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); padding:25px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:15px; text-align:left; border-bottom:1px solid #eee; }
        th { background:#f8f9fa; }
    </style>
</head>
<body>
<div class="card">
    <h2><i class="fas fa-boxes"></i> Kit Management</h2>
    <p><strong>Note:</strong> Full kit CRUD (create/edit/delete) can be expanded here. Below is the current list.</p>
    <table>
        <thead>
            <tr><th>Kit Name</th><th>Level</th><th>Class/Grade</th><th>Total Price</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php
            $kits = $pdo->query("SELECT * FROM kits ORDER BY created_at DESC")->fetchAll();
            foreach ($kits as $kit):
            ?>
            <tr>
                <td><?= htmlspecialchars($kit['name']) ?></td>
                <td><?= htmlspecialchars($kit['level']) ?></td>
                <td><?= htmlspecialchars($kit['class_grade']) ?></td>
                <td>KES <?= number_format($kit['total_base_price'], 2) ?></td>
                <td>
                    <button onclick="alert('Edit kit <?= $kit['id'] ?> - implement form here')" style="background:#2563eb;color:white;border:none;padding:8px 12px;border-radius:6px;">Edit</button>
                    <button onclick="if(confirm('Delete?')) location.href='delete_kit.php?id=<?= $kit['id'] ?>'" style="background:#ef4444;color:white;border:none;padding:8px 12px;border-radius:6px;">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($kits)): ?>
            <tr><td colspan="5">No kits created yet. Add your first kit.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>