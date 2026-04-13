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
    <title>Users Management - ZACK Admin</title>
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
    <h2><i class="fas fa-users"></i> Parents & Students Management</h2>
    <table>
        <thead>
            <tr><th>Name</th><th>Role</th><th>Email / Phone</th><th>Linked Institution</th><th>County</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php
            $users = $pdo->query("
                SELECT u.*, i.name as school_name 
                FROM users u 
                LEFT JOIN users i ON u.institution_id = i.id 
                WHERE u.role IN ('parent', 'student')
                ORDER BY u.created_at DESC
            ")->fetchAll();
            foreach ($users as $u):
            ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= ucfirst($u['role']) ?></td>
                <td><?= htmlspecialchars($u['email'] ?? $u['phone']) ?></td>
                <td><?= htmlspecialchars($u['school_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars(ucfirst($u['county'] ?? '')) ?></td>
                <td><?= ucfirst($u['status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>