<?php
/**
 * admin.php - Full Functional Admin Dashboard for ZACK Platform
 * Built to work with ecom.sql schema
 */

session_start();

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require __DIR__. '/../auth/db.php';
// ==================== STATISTICS ====================
$stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role = 'institution') as total_institutions,
        (SELECT COUNT(*) FROM users WHERE role IN ('parent', 'student')) as total_users,
        (SELECT COUNT(*) FROM orders) as total_orders,
        (SELECT SUM(total_amount) FROM orders WHERE status = 'delivered') as total_revenue,
        (SELECT COUNT(*) FROM users WHERE status = 'pending') as pending_approvals
")->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZACK ADMIN | Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary: #1a237e;
            --accent: #f68b1e;
            --bg: #f4f7fe;
            --white: #ffffff;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar */
        nav {
            width: 80px;
            background: linear-gradient(180deg, #1a237e 0%, #0d1440 100%);
            color: white;
            transition: all 0.4s;
            z-index: 1000;
        }
        nav.expanded { width: 260px; }

        .nav-header {
            padding: 25px 20px;
            font-size: 20px;
            font-weight: 700;
            color: var(--accent);
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .nav-item {
            padding: 18px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            transition: 0.3s;
        }
        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.1);
            color: var(--accent);
        }
        .nav-item i { font-size: 22px; width: 30px; }
        .nav-item span { opacity: 0; transition: 0.3s; font-weight: 500; }
        nav.expanded .nav-item span { opacity: 1; }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .top-bar {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .toggle-btn { font-size: 28px; cursor: pointer; color: var(--primary); }

        .view-panel {
            padding: 30px;
            overflow-y: auto;
            flex: 1;
            display: none;
        }
        .view-panel.active { display: block; }

        /* Cards & Stats */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        .stat-card h3 { font-size: 2rem; margin: 10px 0; color: var(--primary); }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th { background: #f8f9fa; font-weight: 600; color: #555; }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-approve { background: #10b981; color: white; }
        .btn-suspend { background: #f59e0b; color: white; }
        .btn-delete { background: #ef4444; color: white; }

        .status-pending { color: #f59e0b; font-weight: 600; }
        .status-active { color: #10b981; font-weight: 600; }
    </style>
</head>
<body>

<nav id="sidebar">
    <div class="nav-header">ZACK ADMIN</div>
    <div class="nav-item active" data-target="dashView"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></div>
    <div class="nav-item" data-target="instView"><i class="fas fa-school"></i> <span>Institutions</span></div>
    <div class="nav-item" data-target="prodView"><i class="fas fa-box"></i> <span>Products</span></div>
    <div class="nav-item" data-target="orderView"><i class="fas fa-shopping-cart"></i> <span>Orders</span></div>
    <div class="nav-item" data-target="userView"><i class="fas fa-users"></i> <span>Users</span></div>
    <div class="nav-item" onclick="logout()"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></div>
</nav>

<div class="main-content">
    <div class="top-bar">
        <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
        <div style="font-weight:600; color:#1a237e;">Super Administrator</div>
    </div>

    <!-- ==================== DASHBOARD VIEW ==================== -->
    <div id="dashView" class="view-panel active">
        <h2>Admin Dashboard</h2>
        <div class="stat-grid">
            <div class="stat-card">
                <p>Total Revenue</p>
                <h3>KES <?= number_format($stats['total_revenue'] ?? 0) ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Orders</p>
                <h3><?= number_format($stats['total_orders']) ?></h3>
            </div>
            <div class="stat-card">
                <p>Institutions</p>
                <h3><?= number_format($stats['total_institutions']) ?></h3>
            </div>
            <div class="stat-card">
                <p>Users (Parents + Students)</p>
                <h3><?= number_format($stats['total_users']) ?></h3>
            </div>
            <div class="stat-card">
                <p>Pending Approvals</p>
                <h3 style="color:#f59e0b;"><?= number_format($stats['pending_approvals']) ?></h3>
            </div>
        </div>
    </div>

    <!-- ==================== INSTITUTIONS VIEW ==================== -->
    <div id="instView" class="view-panel">
        <h2>Institutions Management</h2>
        <!-- <a href="institutions.php">inst</a> -->
  
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

    <!-- ==================== PRODUCTS VIEW ==================== -->
    <div id="prodView" class="view-panel">
        <h2>Products Management</h2>
        <p><a href="kits.php">products</a></p>
        <!-- You can expand this later with full product form and table -->
    </div>

    <!-- ==================== ORDERS VIEW ==================== -->
    <div id="orderView" class="view-panel">
        <h2>All Orders</h2>
        <a href="orders.php">o</a>
    </div>

    <!-- ==================== USERS VIEW ==================== -->
    <div id="userView" class="view-panel">
        <h2>Parents & Students</h2>
        <!-- <a href="users.php">us</a> -->
        <table>
            <thead>
                <tr><th>Name</th><th>Role</th><th>Phone</th><th>Linked School</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php
                $users = $pdo->query("
                    SELECT u.*, i.name as school_name 
                    FROM users u 
                    LEFT JOIN users i ON u.institution_id = i.id 
                    WHERE u.role IN ('parent','student')
                ")->fetchAll();
                foreach ($users as $u):
                ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= ucfirst($u['role']) ?></td>
                    <td><?= htmlspecialchars($u['phone']) ?></td>
                    <td><?= htmlspecialchars($u['school_name'] ?? 'Not linked') ?></td>
                    <td><?= ucfirst($u['status']) ?></td>
                    
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('expanded');
}

// Navigation
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => {
        document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        document.querySelectorAll('.view-panel').forEach(panel => panel.classList.remove('active'));
        const target = item.getAttribute('data-target');
        if (target) document.getElementById(target).classList.add('active');
    });
});

// Update user status (Approve / Suspend)
function updateStatus(userId, newStatus) {
    if (confirm(`Change status to ${newStatus}?`)) {
        fetch('update_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `user_id=\( {userId}&status= \){newStatus}`
        }).then(() => location.reload());
    }
}

// Delete user
function deleteUser(userId) {
    if (confirm('Delete this user permanently?')) {
        fetch('delete_user.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `user_id=${userId}`
        }).then(() => location.reload());
    }
}

function logout() {
    if (confirm('Logout?')) {
        window.location.href = '../logout.php';
    }
}
</script>

</body>
</html>