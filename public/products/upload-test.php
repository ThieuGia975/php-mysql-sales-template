<?php
$pageTitle = 'Kiểm tra Upload & Validation File';

require_once '/var/www/src/includes/header.php';
require_once '/var/www/src/includes/navbar.php';
?>

<div class="container mt-4 mb-5">

    <h2>Kiểm tra Upload File (Có Validation)</h2>

    <!-- Form upload file bắt buộc phải có enctype="multipart/form-data" -->
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="productImage" class="form-label fw-bold">
                Chọn ảnh sản phẩm (Tối đa 2MB, chấp nhận JPG, PNG, WebP)
            </label>
            <input
                type="file"
                class="form-control"
                id="productImage"
                name="product_image"
                accept="image/jpeg, image/png, image/webp"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Upload & Kiểm Tra File
        </button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>

        <hr>
        <h4>1. Thông tin trong mảng $_FILES:</h4>
        <pre class="bg-light p-3 border rounded"><?php print_r($_FILES); ?></pre>

        <h4>2. Kết quả kiểm tra và xử lý lưu file:</h4>

        <?php
        if (
            isset($_FILES['product_image'])
            && $_FILES['product_image']['error'] === UPLOAD_ERR_OK
        ) {
            $file = $_FILES['product_image'];

            // 1. Kiểm tra kích thước file (Tối đa 2MB)
            $maxSize = 2 * 1024 * 1024; // 2 MB
            if ($file['size'] > $maxSize) {
                echo '<div class="alert alert-danger mt-3">';
                echo 'Lỗi: Dung lượng file (' . round($file['size'] / 1024 / 1024, 2) . ' MB) vượt quá giới hạn cho phép là 2 MB.';
                echo '</div>';
            } else {
                // 2. Kiểm tra MIME type thực tế ở Server bằng finfo
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);

                // Ánh xạ MIME type sang đuôi file phù hợp
                $extensionMap = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                // Hiển thị MIME type nhận diện được
                echo '<p class="text-info"><strong>MIME type phát hiện bởi Server:</strong> <code>' . htmlspecialchars($mimeType) . '</code></p>';

                if (!array_key_exists($mimeType, $extensionMap)) {
                    echo '<div class="alert alert-danger mt-3">';
                    echo 'Lỗi: Định dạng file không hợp lệ! Chỉ cho phép upload file JPG, JPEG, PNG hoặc WebP.';
                    echo '</div>';
                } else {
                    // 3. Tạo tên file mới ngẫu nhiên tránh trùng lặp
                    $extension   = $extensionMap[$mimeType];
                    $newFileName = 'product-' . bin2hex(random_bytes(8)) . '.' . $extension;

                    $uploadDir   = '/var/www/html/uploads/products/';

                    // Tự động tạo thư mục nếu chưa tồn tại
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $destination = $uploadDir . $newFileName;

                    // 4. Di chuyển file từ thư mục tạm sang thư mục lưu trữ
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        echo '<div class="alert alert-success mt-3">';
                        echo 'Upload file thành công!<br>';
                        echo 'Tên file mới: <code>' . htmlspecialchars($newFileName) . '</code><br>';
                        echo 'Đường dẫn lưu file: <code>' . htmlspecialchars($destination) . '</code>';
                        echo '</div>';

                        // Hiển thị ảnh xem trước
                        echo '<div class="mt-3">';
                        echo '<p class="fw-bold">Ảnh xem trước:</p>';
                        echo '<img src="/uploads/products/' . htmlspecialchars($newFileName) . '" alt="Uploaded Image" class="img-thumbnail" style="max-width: 250px;">';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-danger mt-3">';
                        echo 'Không thể lưu file vào thư mục đích (Hãy kiểm tra lại quyền ghi thư mục trên Server).';
                        echo '</div>';
                    }
                }
            }
        } else {
            $errorCode = $_FILES['product_image']['error'] ?? 'N/A';
            echo '<div class="alert alert-warning mt-3">';
            echo 'Chưa chọn file hoặc xảy ra lỗi trong quá trình upload (Mã lỗi: ' . $errorCode . ').';
            echo '</div>';
        }
        ?>

    <?php endif; ?>

</div>

<?php
require_once '/var/www/src/includes/footer.php';
?>