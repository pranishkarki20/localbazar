<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    die('DB Error: ' . $conn->connect_error);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $brand_name = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : null;

    $valid = $email && $username && $password && in_array($role, ['buyer', 'seller']);
    if ($role === 'seller' && !$brand_name) {
        $valid = false;
    }
    if ($valid) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email=?');
        if (!$stmt) {
            die('Prepare failed (email check): ' . $conn->error);
        }
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = 'Email already exists.';
            $stmt->close();
        } else {
            $stmt->close();

            // Check if username exists
            $stmt = $conn->prepare('SELECT id FROM users WHERE username=?');
            if (!$stmt) {
                die('Prepare failed (username check): ' . $conn->error);
            }
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = 'Username already exists.';
                $stmt->close();
            } else {
                $stmt->close();

                // Insert user
                if ($role === 'seller') {
                    $stmt = $conn->prepare('INSERT INTO users (username, email, password, role, brand_name) VALUES (?, ?, ?, ?, ?)');
                    if (!$stmt) {
                        die('Prepare failed (insert): ' . $conn->error);
                    }
                    $stmt->bind_param('sssss', $username, $email, $hash, $role, $brand_name);
                } else {
                    $stmt = $conn->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
                    if (!$stmt) {
                        die('Prepare failed (insert): ' . $conn->error);
                    }
                    $stmt->bind_param('ssss', $username, $email, $hash, $role);
                }
                if ($stmt->execute()) {
                    $message = 'Registration successful! <a href="login.php">Login here</a>.';
                } else {
                    $message = 'Registration failed.';
                }
                $stmt->close();
            }
        }
    } else {
        $message = 'Please fill all fields correctly.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Local Bazar</title>
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #f0fff5 60%, #e0f7f4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: #fff;
            box-shadow: 0 8px 32px #2e8b5744;
            border-radius: 18px;
            padding: 2.5rem 2rem;
            max-width: 350px;
            width: 100%;
            text-align: center;
        }
        .auth-card h2 {
            color: #2e8b57;
            margin-bottom: 1.5rem;
        }
        .auth-card input,
        .auth-card select {
            width: 90%;
            padding: 0.8rem 1rem;
            margin: 0.7rem 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1.1rem;
            background: #f8f8ff;
            transition: border 0.2s;
        }
        .auth-card input:focus,
        .auth-card select:focus {
            border: 1.5px solid #2e8b57;
            outline: none;
            background: #f0fff5;
        }
        .auth-card button {
            width: 100%;
            background: linear-gradient(90deg, #2e8b57 60%, #646cff 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.9rem 0;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 1rem;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .auth-card button:hover {
            background: #646cff;
            color: #fffbe7;
        }
        .auth-card .switch-link {
            margin-top: 1.2rem;
            display: block;
            color: #646cff;
            text-decoration: underline;
        }
        .auth-card .success {
            color: #27ae60;
            margin-top: 0.7rem;
        }
        .auth-card .error {
            color: #e74c3c;
            margin-top: 0.7rem;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Create Your Account</h2>
        <form method="post" id="registerForm" style="display:flex;flex-direction:column;align-items:center;gap:0;">
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" id="roleSelect" required onchange="toggleBrandField()" style="margin-bottom:10px;">
                <option value="buyer">Buyer</option>
                <option value="seller">Seller</option>
                <option value="admin">Admin</option>
            </select>
            <input type="text" name="brand_name" id="brandNameField" placeholder="Brand Name (for sellers)" style="display:none;">
            <button type="submit" class="submit-btn">Register</button>
        </form>
        <script>
        function toggleBrandField() {
            var role = document.getElementById('roleSelect').value;
            var brandField = document.getElementById('brandNameField');
            if (role === 'seller') {
                brandField.style.display = '';
                brandField.required = true;
            } else {
                brandField.style.display = 'none';
                brandField.required = false;
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleBrandField();
        });
        // Prevent admin registration
        document.getElementById('registerForm').onsubmit = function(e) {
            var role = document.getElementById('roleSelect').value;
            if (role === 'admin') {
                alert('You cannot register as admin.');
                e.preventDefault();
                return false;
            }
        };
        </script>

        <?php if ($message): ?>
            <div class="<?= strpos($message, 'successful') !== false ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <a class="switch-link" href="login.php">Already have an account? Login</a>
    </div>
</body>
</html>
