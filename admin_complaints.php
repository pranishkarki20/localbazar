<?php
// admin_complaints.php - Admin view for customer complaints
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    die('DB Error: ' . $conn->connect_error);
}
// Mark as resolved
if (isset($_GET['resolve']) && is_numeric($_GET['resolve'])) {
    $cid = intval($_GET['resolve']);
    $conn->query("UPDATE complaints SET is_resolved=1 WHERE id=$cid");
}
$complaints = $conn->query('SELECT * FROM complaints ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Customer Complaints</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .complaints-table {
            width: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px #646cff22;
            margin: 40px auto;
            max-width: 1100px;
            border-collapse: collapse;
        }
        .complaints-table th, .complaints-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .complaints-table th {
            background: #2e8b57;
            color: #fff;
        }
        .complaints-table tr.resolved {
            background: #e0ffe0;
            color: #888;
        }
        .complaints-table tr.unresolved {
            background: #fffbe7;
        }
        .resolve-btn {
            background: #2e8b57;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 6px 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .resolve-btn:disabled {
            background: #aaa;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-logo">Local Bazar Admin</div>
        <ul class="navbar-links">
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="admin_orders.php">Orders</a></li>
            <li><a href="admin_complaints.php" class="active">Complaints</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <main style="max-width:1100px;margin:40px auto 0 auto;">
        <h1 style="color:#2e8b57;">Customer Complaints</h1>
        <table class="complaints-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Subject</th>
                <th>Complaint</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while($row = $complaints->fetch_assoc()): ?>
            <tr class="<?= $row['is_resolved'] ? 'resolved' : 'unresolved' ?>">
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= isset($row['role']) ? htmlspecialchars($row['role']) : '' ?></td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['complaint'])) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td><?= $row['is_resolved'] ? 'Resolved' : 'Unresolved' ?></td>
                <td>
                    <?php if (!$row['is_resolved']): ?>
                        <a href="admin_complaints.php?resolve=<?= $row['id'] ?>" class="resolve-btn">Mark as Resolved</a>
                    <?php else: ?>
                        <button class="resolve-btn" disabled>Resolved</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </main>
</body>
</html>
