<?php
/**
 * sign-up.php - Complete self-contained registration module
 * Built to exactly match the ecom.sql schema
 * Handles BOTH Institution and Parent/Student registration
 * All validations, password hashing, duplicate checks, and institution linking included
 */

session_start();

// ==================== DATABASE CONNECTION ====================
$host = 'localhost';
$dbname = 'ecom';
$username = 'root';           // ← Change to your MySQL user
$password = '';               // ← Change to your MySQL password (empty for XAMPP/WAMP default)

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('<div style="color:red;padding:20px;text-align:center;">Database connection failed. Please check your credentials.</div>');
}

// ==================== PROCESS REGISTRATION ====================
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $register_type = $_POST['register_type'] ?? '';

    // ==================== INSTITUTION REGISTRATION ====================
    if ($register_type === 'institution') {
        $name        = trim($_POST['name'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $po_box      = trim($_POST['po_box'] ?? '');
        $county      = trim($_POST['county'] ?? '');
        $school_code = trim($_POST['school_code'] ?? '');
        $password    = $_POST['password'] ?? '';
        $confirm     = $_POST['confirm_password'] ?? '';

        // Basic validations
        if (empty($name)) $errors[] = "Institution name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid official email is required";
        if (empty($phone)) $errors[] = "Phone number is required";
        if (empty($password) || $password !== $confirm) $errors[] = "Passwords do not match or are empty";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";

        // Check for existing email/phone
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $stmt->execute([$email, $phone]);
            if ($stmt->fetch()) {
                $errors[] = "An account with this email or phone already exists";
            }
        }

        if (empty($errors)) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users 
                    (name, email, phone, password_hash, role, po_box, county, school_code, status)
                    VALUES (?, ?, ?, ?, 'institution', ?, ?, ?, 'pending')
                ");
                $stmt->execute([$name, $email, $phone, $hash, $po_box, $county, $school_code]);

                $success = "✅ Institution registered successfully!<br>Your account is pending admin approval.";
            } catch (PDOException $e) {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }

    // ==================== PARENT / STUDENT REGISTRATION ====================
    elseif ($register_type === 'parent_student') {
        $role             = $_POST['role'] ?? 'parent';
        $name             = trim($_POST['name'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $phone            = trim($_POST['phone'] ?? '');
        $county           = trim($_POST['county'] ?? '');
        $institution_name = trim($_POST['institution_name'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm          = $_POST['confirm_password'] ?? '';

        // Basic validations
        if (empty($name)) $errors[] = "Full name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($phone)) $errors[] = "Phone number is required";
        if (empty($password) || $password !== $confirm) $errors[] = "Passwords do not match or are empty";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";

        // Check for existing email/phone
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $stmt->execute([$email, $phone]);
            if ($stmt->fetch()) {
                $errors[] = "An account with this email or phone already exists";
            }
        }

        // Try to auto-link to an existing institution (fuzzy name match)
        $institution_id = null;
        if (!empty($institution_name) && empty($errors)) {
            $stmt = $pdo->prepare("
                SELECT id FROM users 
                WHERE role = 'institution' 
                AND name LIKE ? 
                LIMIT 1
            ");
            $stmt->execute(["%" . $institution_name . "%"]);
            if ($row = $stmt->fetch()) {
                $institution_id = $row['id'];
            }
        }

        if (empty($errors)) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users 
                    (name, email, phone, password_hash, role, county, institution_id, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$name, $email, $phone, $hash, $role, $county, $institution_id]);

                $success = "✅ Account created successfully!<br>Your account is pending admin approval.";
            } catch (PDOException $e) {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ZACK Platform</title>
    <style>
        :root {
            --blue: #2563eb;
            --orange: #f97316;
            --light-bg: #f8fafc;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: white;
            width: 100%;
            max-width: 450px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Navigation Tabs */
        .tabs {
            display: flex;
            background: #fff;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .tab {
            flex: 1;
            padding: 20px 0;
            text-align: center;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.9rem;
            color: #64748b;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tab.active { color: var(--blue); }
        .tab:nth-child(2).active { color: var(--orange); }

        .indicator {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            width: 50%;
            background: var(--blue);
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 4px 4px 0 0;
        }

        /* Form Container */
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

        /* Message boxes */
        .message {
            padding: 14px 20px;
            margin: 0 30px 20px;
            border-radius: 10px;
            font-size: 0.95rem;
            text-align: center;
        }
        .success { background: #10b981; color: white; }
        .error { background: #ef4444; color: white; }

        /* Inputs */
        .field { margin-bottom: 18px; position: relative; }

        label {
            display: block;
            font-size: 0.85rem;
            margin-bottom: 6px;
            font-weight: 600;
            color: #1e293b;
        }

        input, select {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: 0.2s;
            background-color: var(--light-bg);
            box-sizing: border-box;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--blue);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        /* Password Toggle */
        .pass-container { position: relative; }
        
        .toggle-btn {
            position: absolute;
            right: 14px;
            top: 38px;
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 0;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .orange-theme .btn-submit { background: var(--orange); box-shadow: 0 4px 6px -1px rgba(249, 115, 22, 0.2); }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .footer a {
            color: var(--blue);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="card">
        <!-- Success / Error Messages -->
        <?php if ($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    • <?= htmlspecialchars($error) ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="slide(0)">Institution</div>
            <div class="tab" onclick="slide(1)">Parent / Student</div>
            <div class="indicator" id="indicator"></div>
        </div>

        <div class="slider-container">
            <div class="slider-inner" id="sliderInner">

                <!-- ==================== INSTITUTION FORM ==================== -->
                <section>
                    <form method="POST" action="">
                        <input type="hidden" name="register_type" value="institution">

                        <div class="field">
                            <label>Institution Name</label>
                            <input type="text" name="name" placeholder="e.g. Nairobi High School" required>
                        </div>
                        <div class="field">
                            <label>Official Email</label>
                            <input type="email" name="email" placeholder="admin@school.ac.ke" required>
                        </div>
                        <div class="field">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" placeholder="0716....." required>
                        </div>
                        <div class="field">
                            <label>P.O.Box</label>
                            <input type="text" name="po_box" placeholder="100-....">
                        </div>
                        <div class="field">
                            <label>County</label>
                            <select name="county" class="county-list" required>
                                <option value="">Select County...</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>School Code</label>
                            <input type="text" name="school_code" placeholder="(must for high schools and below)">
                        </div>
                        <div class="field pass-container">
                            <label>Create Password</label>
                            <input type="password" name="password" class="pass-input" required>
                            <button type="button" class="toggle-btn" onclick="togglePass(this)">👁️</button>
                        </div>
                        <div class="field pass-container">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="pass-input" required>
                            <button type="button" class="toggle-btn" onclick="togglePass(this)">👁️</button>
                        </div>
                        <button type="submit" class="btn-submit">Register Institution</button>
                    </form>
                </section>

                <!-- ==================== PARENT / STUDENT FORM ==================== -->
                <section class="orange-theme">
                    <form method="POST" action="">
                        <input type="hidden" name="register_type" value="parent_student">

                        <div class="field">
                            <label>I am a:</label>
                            <select id="userRole" name="role" required>
                                <option value="parent">Parent / Guardian</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Full Name</label>
                            <input type="text" name="name" placeholder="Enter your official name" required>
                        </div>
                        <div class="field">
                            <label>Official Email</label>
                            <input type="email" name="email" placeholder="your@email.com" required>
                        </div>
                        <div class="field">
                            <label>Institution Name</label>
                            <input type="text" name="institution_name" placeholder="chavakali senior school">
                            <small style="color:#64748b;font-size:0.8rem;">(We will try to link you automatically)</small>
                        </div>
                        <div class="field">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" placeholder="0712 345 678" required>
                        </div>
                        <div class="field">
                            <label>County of Residence</label>
                            <select name="county" class="county-list" required>
                                <option value="">Select County...</option>
                            </select>
                        </div>
                        <div class="field pass-container">
                            <label>Create Password</label>
                            <input type="password" name="password" class="pass-input" required>
                            <button type="button" class="toggle-btn" onclick="togglePass(this)">👁️</button>
                        </div>
                        <div class="field pass-container">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="pass-input" required>
                            <button type="button" class="toggle-btn" onclick="togglePass(this)">👁️</button>
                        </div>
                        <button type="submit" class="btn-submit">Create Account</button>
                    </form>
                </section>

            </div>
        </div>

        <div class="footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

<script>
    const counties = [
        "Mombasa", "Kwale", "Kilifi", "Tana River", "Lamu", "Taita-Taveta", "Garissa", "Wajir", "Mandera", "Marsabit", 
        "Isiolo", "Meru", "Tharaka-Nithi", "Embu", "Kitui", "Machakos", "Makueni", "Nyandarua", "Nyeri", "Kirinyaga", 
        "Murang'a", "Kiambu", "Turkana", "West Pokot", "Samburu", "Trans-Nzoia", "Uasin Gishu", "Elgeyo-Marakwet", "Nandi", "Baringo", 
        "Laikipia", "Nakuru", "Narok", "Kajiado", "Kericho", "Bomet", "Kakamega", "Vihiga", "Bungoma", "Busia", 
        "Siaya", "Kisumu", "Homa Bay", "Migori", "Kisii", "Nyamira", "Nairobi"
    ];

    // Populate all county dropdowns
    document.querySelectorAll('.county-list').forEach(select => {
        counties.sort().forEach(county => {
            let opt = document.createElement('option');
            opt.value = county.toLowerCase();
            opt.innerHTML = county;
            select.appendChild(opt);
        });
    });

    // Slider Logic
    function slide(index) {
        const inner = document.getElementById('sliderInner');
        const indicator = document.getElementById('indicator');
        const tabs = document.querySelectorAll('.tab');

        inner.style.transform = `translateX(-${index * 50}%)`;
        indicator.style.left = `${index * 50}%`;
        
        tabs.forEach(t => t.classList.remove('active'));
        tabs[index].classList.add('active');

        indicator.style.background = (index === 1) ? 'var(--orange)' : 'var(--blue)';
    }

    // Toggle Password Visibility
    function togglePass(btn) {
        const input = btn.parentElement.querySelector('.pass-input');
        if (input.type === "password") {
            input.type = "text";
            btn.innerHTML = "🙈";
        } else {
            input.type = "password";
            btn.innerHTML = "👁️";
        }
    }
</script>

</body>
</html>