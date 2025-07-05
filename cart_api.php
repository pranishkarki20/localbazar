<?php
// cart_api.php - returns product details for given IDs
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['ids']) || !is_array($data['ids']) || count($data['ids']) === 0) {
    echo json_encode(['success'=>false,'message'=>'No product IDs']);
    exit;
}
$ids = array_map('intval', $data['ids']);
$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    echo json_encode(['success'=>false,'message'=>'DB error']);
    exit;
}

// Build query dynamically for IN clause
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = 'SELECT id, name, price, img FROM products WHERE id IN (' . $placeholders . ')';
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['success'=>false,'message'=>'Prepare failed']);
    exit;
}
$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$res = $stmt->get_result();
$products = [];
while($row = $res->fetch_assoc()) $products[] = $row;
$stmt->close();
$conn->close();
echo json_encode(['success'=>true,'products'=>$products]);
