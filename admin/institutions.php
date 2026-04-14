<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$host = 'localhost';
$dbname = 'ecom';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$message = '';
$msg_type = 'success';

// ====================== HANDLE CREATE INSTITUTION ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_institution'])) {
    $name        = trim($_POST['name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $po_box      = trim($_POST['po_box'] ?? '');
    $county      = trim($_POST['county'] ?? '');
    $school_code = trim($_POST['school_code'] ?? '');

    if (empty($name) || empty($email) || empty($phone)) {
        $message = "Institution name, email and phone are required.";
        $msg_type = 'error';
    } else {
        try {
            $password_hash = password_hash('password', PASSWORD_DEFAULT); // Default password

            $stmt = $pdo->prepare("
                INSERT INTO users 
                (name, email, phone, password_hash, role, po_box, county, school_code, status)
                VALUES (?, ?, ?, ?, 'institution', ?, ?, ?, 'pending')
            ");
            $stmt->execute([$name, $email, $phone, $password_hash, $po_box, $county, $school_code]);

            $message = "Institution created successfully! Default password is: <strong>password</strong>";
        } catch (PDOException $e) {
            $message = "Error creating institution: " . $e->getMessage();
            $msg_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution Management - ZACK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f9; margin:0; padding:20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        h1 { color: #0d6efd; }
        .form-container { background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 30px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-primary { background: #0d6efd; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #e9ecef; }
        .msg { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>

<div class="container">
    <h1><i class="fas fa-school"></i> Institution Management</h1>

    <?php if (!empty($message)): ?>
        <div class="msg <?= $msg_type === 'success' ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- Create New Institution Form -->
    <div class="form-container">
        <h2>Create New Institution</h2>
        <form method="POST">
            <input type="hidden" name="create_institution" value="1">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Institution Name <span style="color:red">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Nairobi High School">
                </div>
                <div class="form-group">
                    <label>Official Email <span style="color:red">*</span></label>
                    <input type="email" name="email" required placeholder="admin@school.ac.ke">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Phone Number <span style="color:red">*</span></label>
                    <input type="text" name="phone" required placeholder="0712345678">
                </div>
                <div class="form-group">
                    <label>P.O. Box</label>
                    <input type="text" name="po_box" placeholder="P.O. Box 1234-00100">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>County</label>
                    <select name="county">
                        <option value="">Select County...</option>
                        <?php
                        $counties = ["Nairobi","Kiambu","Machakos","Kajiado","Nakuru","Uasin Gishu","Kisumu","Mombasa","Kakamega","Bungoma"];
                        foreach ($counties as $c) {
                            echo "<option value='$c'>$c</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>School Code (Optional)</label>
                    <input type="text" name="school_code" placeholder="e.g. 45678">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Institution</button>
        </form>
    </div>

    <!-- List of Institutions -->
    <h2>All Institutions</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>School Code</th>
                <th>County</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $institutions = $pdo->query("
                SELECT * FROM users 
                WHERE role = 'institution' 
                ORDER BY created_at DESC
            ")->fetchAll();

            foreach ($institutions as $inst):
            ?>
            <tr>
                <td><?= htmlspecialchars($inst['name']) ?></td>
                <td><?= htmlspecialchars($inst['email']) ?></td>
                <td><?= htmlspecialchars($inst['phone']) ?></td>
                <td><?= htmlspecialchars($inst['school_code'] ?? '—') ?></td>
                <td><?= htmlspecialchars(ucfirst($inst['county'] ?? '')) ?></td>
                <td><?= ucfirst($inst['status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>