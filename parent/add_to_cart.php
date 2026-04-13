<?php
/**
 * add_to_cart.php - Handles adding products to cart via AJAX
 * Works with student/parent dashboard
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['parent', 'student'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
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
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

// Get POST data
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity   = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check if product exists
$stmt = $pdo->prepare("SELECT id, name, base_price FROM products WHERE id = ? AND is_active = TRUE");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// For simplicity, we'll use a session-based cart first (you can later move to a proper `cart` table)
// This is good for quick MVP

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['product_id'] == $product_id) {
        $item['quantity'] += $quantity;
        $found = true;
        break;
    }
}

if (!$found) {
    $_SESSION['cart'][] = [
        'product_id' => $product_id,
        'name'       => $product['name'],
        'price'      => $product['base_price'],
        'quantity'   => $quantity
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Added to cart',
    'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
]);
?>