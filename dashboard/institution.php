<?php
session_start();

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'institution') {
//     header("Location: ../login.php");
//     exit;
// }

// $host = 'localhost';
// $dbname = 'ecom';
// $username = 'root';
// $password = '';


require __DIR__. '/../auth/db.php';
// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
//         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
//     ]);
// } catch (PDOException $e) {
//     die("Database connection failed.");
// }

// Get institution details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'institution'");
$stmt->execute([$user_id]);
$institution = $stmt->fetch();

// Get recent orders
$orders_stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE institution_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$orders_stmt->execute([$user_id]);
$recent_orders = $orders_stmt->fetchAll();

// Get all active products
$products = $pdo->query("
    SELECT * FROM products 
    WHERE is_active = TRUE 
    ORDER BY name
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZACK | <?= htmlspecialchars($institution['name']) ?> Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --jumia-orange: #f68b1e;
            --primary-grad: linear-gradient(135deg, #6e8efb, #a777e3);
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --card-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: #333;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--white);
            padding: 12px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .logo {
            font-size: 26px;
            font-weight: 800;
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .search-bar {
            flex-grow: 1;
            max-width: 520px;
            margin: 0 20px;
        }

        .search-bar input {
            width: 100%;
            padding: 12px 20px;
            border-radius: 50px;
            border: 1px solid #ddd;
            outline: none;
            font-size: 15px;
        }

        .icons {
            display: flex;
            gap: 22px;
            font-size: 22px;
            cursor: pointer;
        }

        .welcome-section {
            text-align: center;
            padding: 45px 5% 30px;
            background: white;
            margin-bottom: 20px;
        }

        .welcome-section h1 {
            font-size: 30px;
            margin: 0 0 10px 0;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            padding: 0 5% 40px;
        }

        .action-card {
            background: var(--white);
            padding: 40px 25px;
            border-radius: 20px;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .action-card:hover {
            transform: translateY(-12px);
            border-color: var(--jumia-orange);
        }

        .action-card i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--jumia-orange);
        }

        .action-card h3 {
            margin: 0 0 12px 0;
            font-size: 21px;
        }

        .content-area {
            padding: 20px 5%;
            display: none;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 22px;
        }

        .item-card {
            background: white;
            padding: 18px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            text-align: center;
        }

        .item-card img {
            width: 100%;
            height: 140px;
            object-fit: contain;
            margin-bottom: 12px;
        }

        .quote-btn, .add-btn {
            background: var(--primary-grad);
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            margin-top: 12px;
            cursor: pointer;
            font-weight: 600;
        }

        .add-btn { background: #2d3436; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th { background: #f8f9fa; }

        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">ZACK</div>
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search products or orders...">
    </div>
    <div class="icons">
        <span>🛎️</span>
        <span>👤</span>
        <span><a href="logout.php">🚪</a></span>
    </div>
</div>

<div class="welcome-section">
    <h1>Welcome, <?= htmlspecialchars($institution['name']) ?></h1>
    <p>Managing supplies for your school • <?= htmlspecialchars(ucfirst($institution['county'] ?? 'Kenya')) ?></p>
</div>

<div class="action-grid">
    <div class="action-card" onclick="showSection('bulk')">
        <i>📦</i>
        <h3>Order Supplies (Bulk)</h3>
        <p>Purchase stock for your institution at special rates.</p>
    </div>

    <div class="action-card" onclick="showSection('quotation')">
        <i>📄</i>
        <h3>Request Quotation</h3>
        <p>Select items and send request to Admin (No payment required).</p>
    </div>

    <div class="action-card" onclick="showSection('orders')">
        <i>📋</i>
        <h3>View My Orders</h3>
        <p>Track all your previous orders and status.</p>
    </div>
</div>

<div id="displayArea" class="content-area">
    <div class="section-header">
        <h2 id="sectionTitle">Section</h2>
    </div>
    <div id="contentBody"></div>
    
    <button id="submitBtn" class="quote-btn" style="display:none; margin-top:30px;" onclick="submitQuotation()">
        Submit Quotation Request to Admin
    </button>
</div>

<script>
let selectedItems = [];

function showSection(type) {
    const area = document.getElementById('displayArea');
    const body = document.getElementById('contentBody');
    const title = document.getElementById('sectionTitle');
    const submitBtn = document.getElementById('submitBtn');

    area.style.display = 'block';
    body.innerHTML = '';
    submitBtn.style.display = 'none';
    selectedItems = [];

    if (type === 'quotation') {
        title.innerText = "Request Quotation from Admin";
        submitBtn.style.display = 'block';

        let html = '<div class="product-grid">';
        <?php foreach ($products as $p): ?>
            html += `
                <div class="item-card">
                    <img src="<?= htmlspecialchars($p['image_url'] ?? 'https://via.placeholder.com/150/f0f0f0/f68b1e?text=' . urlencode(substr($p['name'],0,15))) ?>" alt="<?= addslashes($p['name']) ?>">
                    <h4><?= addslashes(htmlspecialchars($p['name'])) ?></h4>
                    <p>KSH <?= number_format($p['base_price'], 2) ?></p>
                    <button class="quote-btn" onclick="toggleItem(this, <?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'])) ?>')">Select for Quotation</button>
                </div>`;
        <?php endforeach; ?>
        html += '</div>';
        body.innerHTML = html;

    } else if (type === 'bulk') {
        title.innerText = "Bulk Supply Ordering";
        body.innerHTML = `<p style="padding:40px; background:white; border-radius:12px; text-align:center; color:#666;">
            Bulk ordering with direct payment is coming soon.<br>
            Please use <strong>Request Quotation</strong> for now.
        </p>`;

    } else if (type === 'orders') {
        title.innerText = "My Recent Orders";
        let html = `<table>
            <thead><tr><th>Order ID</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>`;
        
        <?php if (empty($recent_orders)): ?>
        html += `<tr><td colspan="5" style="text-align:center;padding:50px;color:#777;">No orders placed yet.</td></tr>`;
        <?php else: ?>
        <?php foreach ($recent_orders as $order): ?>
        html += `
            <tr>
                <td>#<?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['order_type']) ?></td>
                <td>KES <?= number_format($order['total_amount'], 2) ?></td>
                <td><span class="status"><?= ucfirst($order['status']) ?></span></td>
                <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
            </tr>`;
        <?php endforeach; ?>
        <?php endif; ?>
        
        html += `</tbody></table>`;
        body.innerHTML = html;
    }
}

function toggleItem(btn, productId, productName) {
    if (selectedItems.includes(productId)) {
        selectedItems = selectedItems.filter(id => id !== productId);
        btn.textContent = "Select for Quotation";
        btn.style.background = '';
    } else {
        selectedItems.push(productId);
        btn.textContent = "✓ Selected";
        btn.style.background = '#10b981';
    }
}

function submitQuotation() {
    if (selectedItems.length === 0) {
        alert("Please select at least one item for quotation.");
        return;
    }

    const notes = prompt("Any special notes or requirements for the Admin? (Optional)", "");

    fetch('submit_quotation.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded' 
        },
        body: `products=${encodeURIComponent(JSON.stringify(selectedItems))}&notes=${encodeURIComponent(notes || '')}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("✅ Quotation request submitted successfully!\n\nOrder ID: #" + data.order_id + "\nAdmin will review and respond shortly.");
            selectedItems = [];
            showSection('orders');   // Refresh to show orders
        } else {
            alert(data.message || "Failed to submit quotation request.");
        }
    })
    .catch(() => {
        alert("Connection error. Please check your internet and try again.");
    });
}

function logout() {
    if (confirm("Logout from Institution Dashboard?")) {
        window.location.href = '../logout.php';
    }
}
</script>

</body>
</html>