<?php
/**
 * submit_quotation.php - Save quotation request from institution
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'institution') {
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
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$products_json = $_POST['products'] ?? '[]';
$notes = trim($_POST['notes'] ?? '');

$product_ids = json_decode($products_json, true);

if (empty($product_ids) || !is_array($product_ids)) {
    echo json_encode(['success' => false, 'message' => 'No items selected']);
    exit;
}

// Create quotation order
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (user_id, order_type, institution_id, status, total_amount, notes)
        VALUES (?, 'quotation_request', ?, 'pending', 0.00, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $notes]);

    $order_id = $pdo->lastInsertId();

    // Add selected products
    $item_stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, 1, (
            SELECT base_price FROM products WHERE id = ?
        ))
    ");

    foreach ($product_ids as $pid) {
        $item_stmt->execute([$order_id, $pid, $pid]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Quotation request submitted',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save quotation: ' . $e->getMessage()
    ]);
}
?>