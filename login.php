<?php
/**
 * login.php - Complete Login Module for ZACK Platform
 * Matches ecom.sql schema perfectly
 */

session_start();

// ==================== DATABASE CONNECTION ====================

require __DIR__. '/./auth/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';
    $login_type = $_POST['login_type'] ?? 'institution';

    if (empty($identifier) || empty($password)) {
        $error = "Please enter both identifier and password";
    } else {
        // Determine which column to search based on login type
        if ($login_type === 'institution') {
            // Institution can login with School Code or Email
            $sql = "SELECT * FROM users 
                    WHERE (school_code = ? OR email = ?) 
                    AND role IN ('institution', 'admin') 
                    LIMIT 1";
        } else {
            // Parent/Student can login with Phone or Email
            $sql = "SELECT * FROM users 
                    WHERE (phone = ? OR email = ?) 
                    AND role IN ('parent', 'student') 
                    LIMIT 1";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            
            // Check account status
            if ($user['status'] !== 'active') {
                $error = "Your account is " . htmlspecialchars($user['status']) . ". Please contact admin.";
            } else {
                // Successful login - Set session variables
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['institution_id'] = $user['institution_id'] ?? $user['id'];

                // Redirect based on role
                if ($user['role'] === 'admin' || $user['role'] === 'institution') {
                    header("Location: dashboard/institution.php");
                    exit;
                }
                 else {
                    header("Location: parent/student.php");
                    exit;
                }
            }
        } else {
            $error = "Invalid credentials. Please check your details and try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ZACK Platform</title>
    <style>
        :root {
            --blue: #2563eb;
            --orange: #f97316;
            --light-bg: #f8fafc;
            --border: #e2e8f0;
            --text-main: #1e293b;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            background: white;
            width: 100%;
            max-width: 400px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            padding: 30px 30px 10px 30px;
            text-align: center;
        }

        .header h2 {
            margin: 0;
            color: var(--text-main);
            font-size: 1.5rem;
        }

        .header p {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 8px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            position: relative;
            border-bottom: 1px solid var(--border);
        }

        .tab {
            flex: 1;
            padding: 15px 0;
            text-align: center;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.85rem;
            color: #94a3b8;
            transition: 0.3s;
        }

        .tab.active { color: var(--blue); }
        .tab:nth-child(2).active { color: var(--orange); }

        .indicator {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 50%;
            background: var(--blue);
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .slider-container {
            width: 100%;
            overflow: hidden;
        }

        .slider-inner {
            display: flex;
            width: 200%;
            transition: 0.5s ease-in-out;
        }

        section {
            width: 50%;
            padding: 30px;
            box-sizing: border-box;
        }

        /* Forms */
        .field { margin-bottom: 20px; position: relative; }

        label {
            display: block;
            font-size: 0.85rem;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text-main);
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            background-color: var(--light-bg);
            box-sizing: border-box;
            transition: 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--blue);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .toggle-btn {
            position: absolute;
            right: 14px;
            top: 38px;
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .forgot-pass {
            display: block;
            text-align: right;
            font-size: 0.8rem;
            color: var(--blue);
            text-decoration: none;
            margin-top: -10px;
            margin-bottom: 20px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .orange-theme .btn-login { background: var(--orange); }

        .error-msg {
            color: #ef4444;
            font-size: 0.85rem;
            margin: 10px 30px;
            text-align: center;
            padding: 10px;
            background: #fee2e2;
            border-radius: 8px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            font-size: 0.9rem;
            color: #64748b;
            border-top: 1px solid var(--border);
        }

        .footer a {
            color: var(--blue);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="header">
        <h2>Welcome Back</h2>
        <p>Please enter your details to log in</p>
    </div>

    <div class="tabs">
        <div class="tab active" onclick="slide(0)">Institution</div>
        <div class="tab" onclick="slide(1)">Parent / Student</div>
        <div class="indicator" id="indicator"></div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
        <input type="hidden" name="login_type" id="login_type" value="institution">

        <div class="slider-container">
            <div class="slider-inner" id="sliderInner">
                
                <!-- Institution Login -->
                <section>
                    <div class="field">
                        <label>School Code or Official Email</label>
                        <input type="text" name="identifier" 
                               placeholder="e.g. 45678 or admin@school.ac.ke" required>
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" class="pass-input" 
                               placeholder="Enter your password" required>
                        <button type="button" class="toggle-btn" onclick="togglePass(this)">👁️</button>
                    </div>
                    <a href="#" class="forgot-pass">Forgot password?</a>
                    <button type="submit" class="btn-login">Login to Dashboard</button>
                </section>
    
          
    
            </div>
        </div>
    </form>

    <div class="footer">
        Don't have an account? <a href="sign-up.php">Sign up here</a>
        Admin? <a href="admin-login.php">Admin</a>
    </div>
</div>


<div class="login-card">
    <form method="POST" id="loginForm">
    <input type="hidden" name="login_type" id="login_type" value="institution">
              <!-- Parent / Student Login -->
                <section class="orange-theme">
                    <div class="field">
                        <label>Phone Number or Email</label>
                        <input type="text" name="identifier" 
                               placeholder="e.g. 0712345678 or your@email.com" required>
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" class="pass-input" 
                               placeholder="Enter your password" required>
                        <button type="button" class="toggle-btn" onclick="togglePass(this)">👁️</button>
                    </div>
                    <a href="#" class="forgot-pass" style="color: var(--orange);">Forgot password?</a>
                    <button type="submit" class="btn-login">Access Account</button>
                </section>
    </form>
</div>

<script>
    function slide(index) {
        const inner = document.getElementById('sliderInner');
        const indicator = document.getElementById('indicator');
        const tabs = document.querySelectorAll('.tab');
        const loginType = document.getElementById('login_type');

        inner.style.transform = `translateX(-${index * 50}%)`;
        indicator.style.left = `${index * 50}%`;
        
        tabs.forEach(t => t.classList.remove('active'));
        tabs[index].classList.add('active');

        indicator.style.background = (index === 1) ? 'var(--orange)' : 'var(--blue)';
        
        // Update hidden field for backend
        loginType.value = (index === 0) ? 'institution' : 'parent_student';
    }

    function togglePass(btn) {
        const input = btn.parentElement.querySelector('.pass-input');
        if (input.type === "password") {
            input.type = "text";
            btn.textContent = "🙈";
        } else {
            input.type = "password";
            btn.textContent = "👁️";
        }
    }

    // Submit form on Enter key
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });
    });
</script>

</body>
</html>