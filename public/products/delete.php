<?php

// Dùng __DIR__ để xác định chính xác đường dẫn tuyệt đối đến file database.php
require_once __DIR__ . '/../../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /products/');
    exit;
}

$productID = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($productID <= 0) {
    header('Location: /products/');
    exit;
}

// Xóa ảnh liên quan trong DB trước (nếu không có ON DELETE CASCADE)
$sqlDeleteImages = "DELETE FROM product_images WHERE ProductID = ?";
$stmtImages = $conn->prepare($sqlDeleteImages);
$stmtImages->bind_param('i', $productID);
$stmtImages->execute();
$stmtImages->close();

// Xóa sản phẩm
$sql = "DELETE FROM products WHERE ProductID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $productID);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: /products/');
    exit;
}

$stmt->close();
$conn->close();

die('Không thể xóa sản phẩm này do sản phẩm đã phát sinh đơn hàng trong hệ thống.');