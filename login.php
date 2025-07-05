<?php
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'localbazar');
    if ($conn->connect_error) {
        die('DB Error: ' . $conn->connect_error);
    }
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    // Special case: hardcoded admin login
    if ($email === 'admin@gmail.com' && $password === 'adinm' && $role === 'admin') {
        $_SESSION['user_id'] = 0;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        header('Location: admin.php');
        exit;
    }
    // Login only by email for buyer/seller
    $stmt = $conn->prepare('SELECT id, username, password, role FROM users WHERE email=?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hash, $dbrole);
        $stmt->fetch();
        if (password_verify($password, $hash)) {
            if ($dbrole !== $role) {
                $error = 'Role does not match for this user.';
            } else {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $dbrole;
                if ($dbrole === 'admin') {
                    header('Location: admin.php');
                    exit;
                } elseif ($dbrole === 'seller') {
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
                    if ($redirect === 'orders_seller.php') {
                        header('Location: orders_seller.php');
                        exit;
                    } else {
                        header('Location: seller_dashboard.php');
                        exit;
                    }
                } else {
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
                    if ($redirect) {
                        header('Location: ' . $redirect);
                        exit;
                    } else {
                        header('Location: shop.php');
                        exit;
                    }
                }
            }
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'User not found.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Local Bazar</title>
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
    .auth-card .error {
      color: #e74c3c;
      margin-top: 0.7rem;
    }
  </style>
</head>
<body>
  <div class="auth-card">
    <h2>Login</h2>
    <?php if($error): ?><div class="error"> <?= htmlspecialchars($error) ?> </div><?php endif; ?>
    <form method="post" style="display:flex;flex-direction:column;align-items:center;gap:0;">
      <input type="email" name="email" placeholder="Email" required autofocus>
      <input type="password" name="password" placeholder="Password" required>
      <select name="role" required style="margin-bottom:10px;">
        <option value="buyer">Buyer</option>
        <option value="seller">Seller</option>
        <option value="admin">Admin</option>
      </select>
      <button type="submit">Login</button>
    </form>
    <a class="switch-link" href="register.php">Create an account</a>
  </div>
</body>
</html>
