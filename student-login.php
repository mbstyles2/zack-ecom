<?php
/**
 * student-login.php - Dedicated Login for Students & Parents
 */

session_start();

// If already logged in as student or parent, redirect to dashboard
if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['parent', 'student'])) {
    header("Location: parent/student.php");
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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = "Please enter your phone number or email and password";
    } else {
        // Allow login with phone or email for parents/students
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE (phone = ? OR email = ?) 
            AND role IN ('parent', 'student')
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            
            if ($user['status'] !== 'active') {
                $error = "Your account is " . htmlspecialchars($user['status']) . ". Please contact your school admin.";
            } else {
                // Successful login
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role']      = $user['role'];

                header("Location: parent/student.php");
                exit;
            }
        } else {
            $error = "Invalid phone/email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student / Parent Login - ZACK</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f68b1e, #ff8c42);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background: white;
            color: #333;
            padding: 45px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 420px;
        }

        .logo {
            text-align: center;
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #f68b1e, #ff5722);
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .tagline {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 15px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #1a237e;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #f68b1e;
            box-shadow: 0 0 0 3px rgba(246, 139, 30, 0.15);
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

        .footer a {
            color: #f68b1e;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="logo">ZACK</div>
    <div class="tagline">For Students & Parents</div>
    <h2>Welcome Back</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Phone Number or Email</label>
            <input type="text" name="identifier" placeholder="0712345678 or your@email.com" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn-login">Login</button>
    </form>

    <div class="footer">
        Don't have an account? <a href="sign-up.php">sign-up here</a><br><br>
       
    </div>
</div>

</body>
</html>