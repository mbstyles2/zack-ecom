<?php
session_start();

// Redirect if not logged in as parent or student
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['parent', 'student'])) {
    header("Location: ../login.php");
    exit;
}

$host = 'localhost';
$dbname = 'ecom';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed.");
}

// Get logged-in user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

// Get linked institution (school)
$school = null;
if ($current_user['institution_id']) {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ? AND role = 'institution'");
    $stmt->execute([$current_user['institution_id']]);
    $school = $stmt->fetch();
}

// Fetch available products
$products = $pdo->query("
    SELECT * FROM products 
    WHERE is_active = TRUE 
    ORDER BY created_at DESC 
    LIMIT 20
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZACK | <?= ucfirst($_SESSION['role']) ?> Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --jumia-orange: #f68b1e;
            --primary-grad: linear-gradient(135deg, #6e8efb, #a777e3);
            --dark-text: #2d3436;
            --light-bg: #f4f6f8;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }

        /* Navbar */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--white);
            padding: 12px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .logo {
            font-size: 26px;
            font-weight: 800;
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .search-wrapper {
            flex-grow: 1;
            max-width: 580px;
            margin: 0 20px;
            position: relative;
        }

        .search-wrapper input {
            width: 100%;
            padding: 14px 20px;
            border-radius: 50px;
            border: 2px solid #eee;
            outline: none;
            font-size: 15px;
        }

        .search-wrapper input:focus {
            border-color: #a777e3;
        }

        .icon-group {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-icon {
            font-size: 24px;
            cursor: pointer;
            position: relative;
            transition: 0.3s;
        }

        .nav-icon:hover { color: var(--jumia-orange); }

        .badge {
            position: absolute;
            top: -6px;
            right: -10px;
            background: var(--jumia-orange);
            color: white;
            font-size: 10px;
            padding: 1px 6px;
            border-radius: 50%;
            font-weight: bold;
        }

        /* Welcome Section */
        .welcome-section {
            padding: 30px 5%;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .welcome-section h1 {
            margin: 0 0 8px 0;
            font-size: 28px;
        }

        .school-info {
            color: #666;
            font-size: 15px;
        }

        /* Filter Card */
        .filter-container {
            padding: 40px 5%;
            display: flex;
            justify-content: center;
        }

        .glass-card {
            background: var(--white);
            width: 100%;
            max-width: 460px;
            padding: 35px 30px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
        }

        .glass-card h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .glass-card select {
            width: 100%;
            padding: 14px;
            margin: 12px 0;
            border-radius: 12px;
            border: 1px solid #ddd;
            background: #fafafa;
            font-size: 15px;
        }

        .btn-continue {
            width: 100%;
            padding: 16px;
            margin-top: 20px;
            border: none;
            border-radius: 12px;
            background: var(--primary-grad);
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
        }

        /* Product Grid */
        main {
            padding: 20px 5%;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(245px, 1fr));
            gap: 25px;
        }

        .product-card {
            background: var(--white);
            border-radius: 20px;
            padding: 18px;
            text-align: center;
            transition: all 0.4s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.04);
        }

        .product-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            margin-bottom: 15px;
            border-radius: 12px;
        }

        .product-card h4 {
            margin: 12px 0 8px;
            font-size: 16.5px;
            color: #222;
        }

        .price {
            font-size: 22px;
            font-weight: 800;
            color: var(--jumia-orange);
            margin: 10px 0;
        }

        .add-btn {
            background: #2d3436;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 50px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: 0.3s;
        }

        .add-btn:hover {
            background: var(--jumia-orange);
        }

        /* Mobile */
        @media (max-width: 768px) {
            main { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 15px; 
                padding: 15px; 
            }
            .product-card img { height: 130px; }
            .price { font-size: 18px; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">ZACK</div>
    
    <div class="search-wrapper">
        <input type="text" id="searchInput" placeholder="Search stationery, uniform, bags...">
    </div>

    <div class="icon-group">
        <div class="nav-icon" title="My Account">👤</div>
        <div class="nav-icon" title="My Cart" onclick="window.location.href='cart.php'">
            🛒 <span class="badge" id="cartCount">0</span>
        </div>
        <div class="nav-icon" onclick="logout()" title="Logout">🚪</div>
    </div>
</div>

<!-- Welcome Section -->
<div class="welcome-section">
    <h1>Welcome back, <?= htmlspecialchars($current_user['name']) ?>!</h1>
    <p class="school-info">
        <?= $school ? 'School: ' . htmlspecialchars($school['name']) : 'No school linked yet' ?> 
        • <?= ucfirst($_SESSION['role']) ?>
    </p>
</div>

<!-- Filter Card -->
<section class="filter-container">
    <div class="glass-card">
        <h2>School Requirements</h2>
        
        <select id="institutionSelect">
            <option value=""><?= $school ? htmlspecialchars($school['name']) : 'Select Institution' ?></option>
        </select>
        
        <select id="levelSelect">
            <option value="">Select Level</option>
            <option value="Pre Primary">Pre Primary</option>
            <option value="Primary">Primary</option>
            <option value="Junior Secondary">Junior Secondary</option>
            <option value="High School">High School</option>
        </select>
        
        <select id="classSelect">
            <option value="">Select Class / Grade</option>
            <option value="Grade 1">Grade 1</option>
            <option value="Grade 2">Grade 2</option>
            <option value="Form 1">Form 1</option>
            <option value="Form 2">Form 2</option>
            <option value="Form 3">Form 3</option>
            <option value="Form 4">Form 4</option>
        </select>
        
        <select id="orderType">
            <option value="full_kit">Full Kit</option>
            <option value="individual_items">Individual Items</option>
            <option value="top_up_restock">Top-up / Restock</option>
        </select>

        <button class="btn-continue" onclick="alert('Order flow is under development. For now, use the product grid below.')">
            CONTINUE TO ORDER
        </button>
    </div>
</section>

<!-- Product Marketplace -->
<main id="productDisplay">
    <?php foreach ($products as $product): ?>
    <div class="product-card">
        <img src="<?= htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/200x200/f0f0f0/f68b1e?text=' . urlencode(substr($product['name'], 0, 15))) ?>" 
             alt="<?= htmlspecialchars($product['name']) ?>">
        <h4><?= htmlspecialchars($product['name']) ?></h4>
        <div class="price">KSH <?= number_format($product['base_price'], 2) ?></div>
        <button class="add-btn" onclick="addToCart(<?= $product['id'] ?>, '<?= addslashes(htmlspecialchars($product['name'])) ?>')">
            Add to Cart
        </button>
    </div>
    <?php endforeach; ?>
</main>

<script>
// Real-time search
document.getElementById('searchInput').addEventListener('keyup', function() {
    const term = this.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.product-card');
    
    cards.forEach(card => {
        const title = card.querySelector('h4').textContent.toLowerCase();
        card.style.display = title.includes(term) ? 'block' : 'none';
    });
});

// Add to Cart with AJAX
function addToCart(productId, name) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            document.getElementById('cartCount').textContent = data.cart_count || 0;
            
            // Nice feedback
            const notification = document.createElement('div');
            notification.style.position = 'fixed';
            notification.style.bottom = '20px';
            notification.style.right = '20px';
            notification.style.background = '#10b981';
            notification.style.color = 'white';
            notification.style.padding = '12px 20px';
            notification.style.borderRadius = '12px';
            notification.style.boxShadow = '0 10px 20px rgba(0,0,0,0.2)';
            notification.style.zIndex = '9999';
            notification.textContent = `✅ ${name} added to cart!`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transition = 'opacity 0.5s';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 2000);
        } else {
            alert(data.message || 'Failed to add to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
    });
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../logout.php';
    }
}

// Load initial cart count when page loads
window.onload = function() {
    // Optional: You can fetch current cart count from server if needed
    console.log("ZACK <?= ucfirst($_SESSION['role']) ?> Dashboard Loaded Successfully");
};
</script>

</body>
</html>