<?php
session_start();

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

// Get cart from session
$cart_items = $_SESSION['cart'] ?? [];
$total_amount = 0;

foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - ZACK</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 { margin: 0; font-size: 24px; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 18px 25px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        .product-name { font-weight: 500; }
        
        .quantity-input {
            width: 70px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .total {
            font-size: 22px;
            font-weight: 700;
            color: #f68b1e;
            text-align: right;
            padding: 25px;
        }
        
        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            margin: 5px;
        }
        
        .btn-primary {
            background: #f68b1e;
            color: white;
        }
        
        .btn-secondary {
            background: #ddd;
            color: #333;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #777;
        }
        
        .empty-cart h2 { font-size: 28px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>My Shopping Cart</h1>
        <a href="student.php" style="color:white; text-decoration:none;">← Continue Shopping</a>
    </div>

    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <h2>Your cart is empty</h2>
            <p>Browse products and add items to your cart</p>
            <a href="student.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $index => $item): ?>
                <tr>
                    <td class="product-name"><?= htmlspecialchars($item['name']) ?></td>
                    <td>KES <?= number_format($item['price'], 2) ?></td>
                    <td>
                        <input type="number" class="quantity-input" 
                               value="<?= $item['quantity'] ?>" 
                               min="1" 
                               onchange="updateQuantity(<?= $index ?>, this.value)">
                    </td>
                    <td>KES <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    <td>
                        <button onclick="removeFromCart(<?= $index ?>)" 
                                style="background:#ef4444; color:white; border:none; padding:8px 14px; border-radius:8px; cursor:pointer;">
                            Remove
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            Total: KES <?= number_format($total_amount, 2) ?>
        </div>

        <div style="padding: 0 30px 30px; text-align:right;">
            <button onclick="alert('Proceeding to checkout... (Feature coming soon)')" class="btn btn-primary">
                Proceed to Checkout
            </button>
            <a href="student.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    <?php endif; ?>
</div>

<script>
// Update quantity in session (simple client-side for demo)
function updateQuantity(index, newQty) {
    if (newQty < 1) return;
    // In production: send AJAX to update cart
    console.log(`Updated item ${index} quantity to ${newQty}`);
}

function removeFromCart(index) {
    if (confirm('Remove this item from cart?')) {
        // In production: send AJAX to remove
        console.log(`Removed item at index ${index}`);
        location.reload(); // Temporary refresh
    }
}
</script>

</body>
</html>