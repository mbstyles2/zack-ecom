<?php
/**
 * submit_quotation.php - Handles quotation requests from institutions
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'institution') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
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

$product_ids = json_decode($_POST['products'] ?? '[]', true);
$notes = trim($_POST['notes'] ?? '');

if (empty($product_ids)) {
    echo json_encode(['success' => false, 'message' => 'No items selected']);
    exit;
}

// Create a new order of type 'quotation_request'
$stmt = $pdo->prepare("
    INSERT INTO orders 
    (user_id, order_type, institution_id, status, total_amount, notes)
    VALUES (?, 'quotation_request', ?, 'pending', 0.00, ?)
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $notes]);

$order_id = $pdo->lastInsertId();

// Add selected products as order items (quantity = 1 for quotation)
foreach ($product_ids as $pid) {
    $price_stmt = $pdo->prepare("SELECT base_price FROM products WHERE id = ?");
    $price_stmt->execute([$pid]);
    $price = $price_stmt->fetchColumn() ?: 0;

    $item_stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, 1, ?)
    ");
    $item_stmt->execute([$order_id, $pid, $price]);
}

// Update total amount (optional for quotation)
$total = array_sum(array_map(function($pid) use ($pdo) {
    $stmt = $pdo->prepare("SELECT base_price FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    return $stmt->fetchColumn() ?: 0;
}, $product_ids));

$pdo->prepare("UPDATE orders SET total_amount = ? WHERE id = ?")
    ->execute([$total, $order_id]);

echo json_encode([
    'success' => true,
    'message' => 'Quotation request submitted successfully',
    'order_id' => $order_id
]);
?>