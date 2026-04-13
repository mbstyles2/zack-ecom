<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['parent', 'student'])) {
    header("Location: student-login.php");
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

$cart_items = $_SESSION['cart'] ?? [];
if (empty($cart_items)) {
    header("Location: cart.php");
    exit;
}

$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Get user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - ZACK</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f8; margin:0; padding:20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #f68b1e; color: white; padding: 20px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .total { font-size: 24px; font-weight: 700; color: #f68b1e; text-align: right; padding: 20px; }
        .btn { 
            padding: 15px 30px; 
            border: none; 
            border-radius: 50px; 
            font-size: 18px; 
            cursor: pointer; 
            width: 100%; 
            margin: 10px 0;
        }
        .btn-primary { background: #f68b1e; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Checkout</h1>
        <p>Review your order</p>
    </div>

    <div style="padding: 30px;">
        <h2>Order Summary</h2>
        <table>
            <thead>
                <tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td>KES <?= number_format($item['price'], 2) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>KES <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            Total Amount: KES <?= number_format($total_amount, 2) ?>
        </div>

        <h3 style="margin-top:30px;">Payment Options</h3>
        <p>We currently support manual payment. Please choose one:</p>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:20px;">
            
            <!-- Option 1: M-Pesa -->
            <div style="border:2px solid #10b981; border-radius:12px; padding:20px; text-align:center;">
                <h3>Pay via M-Pesa</h3>
                <p>Send KES <?= number_format($total_amount, 2) ?> to:</p>
                <h2 style="color:#10b981;">0712 345 678</h2>
                <p><strong>Account Name:</strong> ZACK Supplies</p>
                <small>After payment, send screenshot + Order Reference to WhatsApp</small>
            </div>

            <!-- Option 2: Bank Transfer -->
            <div style="border:2px solid #0d6efd; border-radius:12px; padding:20px; text-align:center;">
                <h3>Bank Transfer</h3>
                <p>Bank: Equity Bank</p>
                <p>Account: 1234567890</p>
                <p>Branch: Nairobi</p>
                <small>Reference: ZACK-<?= $user['id'] ?>-<?= time() ?></small>
            </div>
        </div>

        <div style="margin-top:40px; text-align:center;">
            <button onclick="confirmOrder()" class="btn btn-primary">
                I Have Paid - Confirm Order
            </button>
            <br><br>
            <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
        </div>

        <p style="text-align:center; margin-top:30px; color:#666; font-size:14px;">
            Need help? Contact us on 
            <a href="https://wa.me/254712345678" target="_blank">WhatsApp</a> or 
            <a href="https://instagram.com/zacksupplies" target="_blank">Instagram</a>
        </p>
    </div>
</div>

<script>
function confirmOrder() {
    if (confirm("Have you completed the payment?\n\nClick OK to submit your order to ZACK.")) {
        // Redirect to create_order.php to process the order
        window.location.href = 'create_order.php';
    }
}
</script>

</body>
</html>