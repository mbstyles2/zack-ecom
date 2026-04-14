<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - ZACK</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fe;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .message {
            text-align: center;
            background: white;
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h2 { color: #1a237e; }
        p { color: #666; margin: 15px 0 25px; }
        a {
            color: #f68b1e;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="message">
        <h2>You have been logged out successfully</h2>
        <p>Thank you for using ZACK Platform</p>
        <a href="./login.php">→ Back to Login</a>
    </div>

    <script>
        // Auto redirect to login after 2 seconds
        setTimeout(() => {
            window.location.href = './login.php';
        }, 30000);
    </script>
</body>
</html>