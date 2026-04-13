<?php
/**
 * admin-login.php - Dedicated Login Page for Admin Only
 */

session_start();

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard/admin.php");
    exit;
}

require __DIR__. '/./auth/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter email and password";
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE email = ? AND role = 'admin' 
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            
            if ($admin['status'] !== 'active') {
                $error = "Your account is " . $admin['status'] . ". Please contact support.";
            } else {
                // Successful admin login
                $_SESSION['user_id']   = $admin['id'];
                $_SESSION['user_name'] = $admin['name'];
                $_SESSION['role']      = 'admin';

                header("Location: admin/admin.php");
                exit;
            }
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ZACK Platform</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a237e, #0d1440);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .login-box {
            background: white;
            color: #333;
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
        }

        .logo {
            text-align: center;
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #f68b1e, #ff5722);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #1a237e;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #f68b1e;
            box-shadow: 0 0 0 3px rgba(246, 139, 30, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: #f68b1e;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .error {
            background: #fee2e2;
            color: #ef4444;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="logo">ZACK</div>
    <h2>Admin Login</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="email" placeholder="admin@zack.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn-login">Login as Admin</button>
    </form>

    <div class="footer">
        Default Password: <strong>password</strong><br>
        <small>Change it immediately after first login for security.</small>
    </div>
</div>

</body>
</html>