<?php
/**
 * create_order.php - Creates order from cart and saves to database
 */

session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['parent', 'student'])) {
    header("Location: student-login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
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

$user_id = $_SESSION['user_id'];
$cart_items = $_SESSION['cart'];
$total_amount = 0;

// Calculate total amount
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Get user's linked institution (school)
$stmt = $pdo->prepare("SELECT institution_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$institution_id = $user['institution_id'] ?? null;

if (!$institution_id) {
    die("Error: Your account is not linked to any school. Please contact admin.");
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Create main order
    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (user_id, order_type, institution_id, status, total_amount, notes, created_at)
        VALUES (?, 'individual_items', ?, 'pending', ?, 'Order placed from cart', NOW())
    ");
    $stmt->execute([$user_id, $institution_id, $total_amount]);

    $order_id = $pdo->lastInsertId();

    // Insert order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cart_items as $item) {
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);
    }

    // Commit transaction
    $pdo->commit();

    // Clear the cart after successful order
    unset($_SESSION['cart']);

    // Success message and redirect
    $_SESSION['success_msg'] = "Your order (#$order_id) has been placed successfully! We will confirm payment shortly.";

    header("Location: student.php?order_success=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Order failed: " . $e->getMessage());
}
?>