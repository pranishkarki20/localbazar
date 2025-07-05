<?php
session_start();
header('Content-Type: application/json');

// ✅ Step 1: Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// ✅ Step 2: Read and validate JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['phone'], $data['address'], $data['cart']) || !is_array($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$user_id = $_SESSION['user_id'];
$phone = trim($data['phone']);
$address = trim($data['address']);
$cart = $data['cart'];

// ✅ Step 3: Connect to the database
$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

// ✅ Step 4: Insert into `orders` table
$order_date = date('Y-m-d H:i:s');
$status = 'Pending';

$stmt = $conn->prepare("INSERT INTO orders (user_id, phone, address, order_date, status) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param('issss', $user_id, $phone, $address, $order_date, $status);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Order insert failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}
$order_id = $stmt->insert_id;
$stmt->close();

// ✅ Step 5: Insert each product from cart as a separate order in `orders`
foreach ($cart as $item) {
    if (!isset($item['id'])) continue;
    $product_id = intval($item['id']);
    $qty = isset($item['quantity']) ? intval($item['quantity']) : 1; // default to 1
    $stmt = $conn->prepare("INSERT INTO orders (user_id, phone, address, order_date, status, product_id, qty) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed for order: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('issssii', $user_id, $phone, $address, $order_date, $status, $product_id, $qty);
    $stmt->execute();
    $stmt->close();
}

// ✅ Step 6: Close DB and return success
$conn->close();

echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
