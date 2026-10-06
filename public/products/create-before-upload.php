<?php

$pageTitle = 'Thêm sản phẩm';

require_once '/var/www/src/config/database.php';

$error = '';

// 1. Lấy danh sách Categories để đưa vào thẻ <select>
$sqlCategories = "
    SELECT CategoryID, CategoryName
    FROM categories
    ORDER BY CategoryName
";
$categories = $conn->query($sqlCategories);

// 2. Lấy danh sách Suppliers để đưa vào thẻ <select>
$sqlSuppliers = "
    SELECT SupplierID, SupplierName
    FROM suppliers
    ORDER BY SupplierName
";
$suppliers = $conn->query($sqlSuppliers);

// 3. Xử lý dữ liệu khi người dùng submit Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $productCode = trim($_POST['product_code'] ?? '');
    $productName = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $unit = trim($_POST['unit'] ?? '');

    $price = (float) ($_POST['price'] ?? 0);
    $stockQuantity = (int) ($_POST['stock_quantity'] ?? 0);

    $categoryID = (int) ($_POST['category_id'] ?? 0);
    $supplierID = (int) ($_POST['supplier_id'] ?? 0);

    $isActive = isset($_POST['is_active']) ? 1 : 0;

    // Kiểm tra hợp lệ dữ liệu (Validation)
    if ($productCode === '') {
        $error = 'Mã sản phẩm không được để trống.';
    } elseif ($productName === '') {
        $error = 'Tên sản phẩm không được để trống.';
    } elseif ($price < 0) {
        $error = 'Giá sản phẩm không hợp lệ.';
    } elseif ($stockQuantity < 0) {
        $error = 'Số lượng tồn kho không hợp lệ.';
    } elseif ($categoryID <= 0) {
        $error = 'Vui lòng chọn danh mục.';
    } elseif ($supplierID <= 0) {
        $error = 'Vui lòng chọn nhà cung cấp.';
    } else {
        // Thực hiện lưu vào CSDL
        $sql = "
            INSERT INTO products
            (
                ProductCode,
                ProductName,
                Description,
                Unit,
                Price,
                StockQuantity,
                IsActive,
                SupplierID,
                CategoryID
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        // s: string, d: double/float, i: integer
        $stmt->bind_param(
            'ssssdiiii',
            $productCode,
            $productName,
            $description,
            $unit,
            $price,
            $stockQuantity,
            $isActive,
            $supplierID,
            $categoryID
        );

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: /products/');
            exit;
        }

        $error = 'Không thể thêm sản phẩm.';
        $stmt->close();
    }
}

require_once '/var/www/src/includes/header.php';
require_once '/var/www/src/includes/navbar.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-3">Thêm sản phẩm</h2>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" class="card card-body shadow-sm">
                <div class="mb-3">
                    <label for="product_code" class="form-label">Mã sản phẩm (*)</label>
                    <input type="text" class="form-control" id="product_code" name="product_code" value="<?= htmlspecialchars($_POST['product_code'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="product_name" class="form-label">Tên sản phẩm (*)</label>
                    <input type="text" class="form-control" id="product_name" name="product_name" value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="unit" class="form-label">Đơn vị tính</label>
                        <input type="text" class="form-control" id="unit" name="unit" placeholder="Cái, Chiếc, Bộc..." value="<?= htmlspecialchars($_POST['unit'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Giá (VNĐ) (*)</label>
                        <input type="number" step="1000" class="form-control" id="price" name="price" value="<?= htmlspecialchars($_POST['price'] ?? '0') ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="stock_quantity" class="form-label">Số lượng tồn kho (*)</label>
                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?= htmlspecialchars($_POST['stock_quantity'] ?? '0') ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Danh mục (*)</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <option value="<?= $category['CategoryID'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['CategoryID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['CategoryName']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="supplier_id" class="form-label">Nhà cung cấp (*)</label>
                    <select name="supplier_id" id="supplier_id" class="form-select" required>
                        <option value="">-- Chọn nhà cung cấp --</option>
                        <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                            <option value="<?= $supplier['SupplierID'] ?>" <?= (isset($_POST['supplier_id']) && $_POST['supplier_id'] == $supplier['SupplierID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($supplier['SupplierName']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= isset($_POST['is_active']) || $_SERVER['REQUEST_METHOD'] === 'GET' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">
                        Đang kinh doanh
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                    <a href="/products/" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once '/var/www/src/includes/footer.php';
$conn->close();
?>