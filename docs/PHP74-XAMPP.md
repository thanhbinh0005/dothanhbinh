# Cài plugin và theme trên XAMPP PHP 7.4.30

Bản 1.0.1 khai báo PHP tối thiểu 7.4 cho cả plugin và theme. Yêu cầu WordPress 6.6 trở lên; WordPress cũng cần hỗ trợ PHP đang dùng. Bộ cài giữ nguyên tên thư mục để cập nhật tiếp vào bản đang có và giữ dữ liệu.

## Cài trong WordPress

1. Bật Apache và MySQL trong XAMPP. Với cấu hình đã gửi, địa chỉ quản trị là `http://localhost:8899/thanhbinh/wp-admin/`.
2. Tải `dist/thanh-binh-orders-php74.zip`. Vào Plugin → Cài mới → Tải plugin lên, chọn ZIP này, cài rồi kích hoạt. Nếu WordPress nhận ra bản cũ đã có, chọn thay thế bản hiện tại bằng bản tải lên sau khi sao lưu website.
3. Tải `dist/thanh-binh-theme-php74.zip`. Vào Giao diện → Thêm mới → Tải giao diện lên, chọn ZIP này, cài rồi kích hoạt. Nếu có bản cũ, chọn thay thế bằng bản tải lên.
4. Kiểm tra plugin và theme đều có phiên bản **1.0.1**. Không cần đổi PHP hoặc sửa file XAMPP cho hai bộ cài này.
5. Nếu sản phẩm và bài viết đã có, giữ nguyên nội dung đó. Chỉ nhập `thanh-binh-content.xml` ở Công cụ → Nhập → WordPress khi cần nội dung khởi tạo. Sau đó lưu lại Cài đặt → Đường dẫn tĩnh.
6. Mở trang chủ, kiểm tra sản phẩm và gửi một yêu cầu báo giá thử. Yêu cầu cần xuất hiện trong menu Yêu cầu báo giá; email chỉ hoạt động sau khi cấu hình SMTP và địa chỉ nhận.

ZIP có hậu tố `-php74` chứa cùng mã nguồn với các ZIP chuẩn bản 1.0.1. Tên plugin/theme trong WordPress và tên thư mục bên trong ZIP không đổi, tránh tạo thêm một plugin hoặc theme trùng.

Ảnh và logo gốc vẫn cần được nhập qua Thư viện khi có tệp; bản cập nhật PHP không thêm ảnh thay thế giả làm logo công ty. PHP 7.4 đã hết hỗ trợ từ dự án PHP; bản này đáp ứng môi trường XAMPP hiện tại, không cập nhật bảo mật cho chính PHP/XAMPP.

## Tái hiện kiểm thử tương thích

Môi trường kiểm thử dùng đúng PHP **7.4.30** trên Linux/Apache, cùng WordPress từ image đã cố định trong `compose.yaml`; không phải kiểm thử trực tiếp trên máy Windows của chủ website. Tách riêng project, DB, volume và cổng để giữ nguyên website PHP 8.3 đang có.

Đã xác minh trên WordPress 7.1.3: tải ZIP và kích hoạt plugin/theme, thay thế bản đã cài, lưu đường dẫn tĩnh, mở Tùy biến, trang chủ/sản phẩm/bài viết, bố cục điện thoại, gửi và lưu yêu cầu, kiểm tra dữ liệu sai, giới hạn gửi, quyền riêng tư và cập nhật trạng thái quản trị. Các kiểm tra chức năng tương ứng cũng qua trên PHP 8.3. File plugin đã cài được đối chiếu SHA-256 với mã nguồn đóng gói.

Dịch vụ kiểm tra cập nhật WordPress.org trong máy thử bị proxy trả HTTP 403; đó là kiểm tra mạng bên ngoài, tách biệt với việc cài ZIP cục bộ. Không tắt xác minh TLS để vượt lỗi này.

```bash
docker compose -p thanh-binh-php74 -f compose.yaml -f compose.php74.yaml build wordpress
docker compose -p thanh-binh-php74 -f compose.yaml -f compose.php74.yaml up -d --wait db wordpress
```

Sau khi cài WordPress thử nghiệm với tài khoản `siteadmin`, lưu mật khẩu riêng trong `.runtime/php74-admin-password` (không commit), rồi chạy:

```bash
node tests/php74-install.cjs
docker compose -p thanh-binh-php74 -f compose.yaml -f compose.php74.yaml run --rm -T cli eval-file /var/www/html/wp-content/themes/thanh-binh/seed.php
docker compose -p thanh-binh-php74 -f compose.yaml -f compose.php74.yaml run --rm -T cli rewrite structure '/%postname%/' --hard
TEST_COMPOSE_PROJECT=thanh-binh-php74 TEST_COMPOSE_FILES=compose.yaml,compose.php74.yaml TEST_BASE_URL=http://localhost:8081 TEST_ADMIN_PASSWORD_FILE=.runtime/php74-admin-password TEST_SCREENSHOT_DIR=.runtime/screenshots-php74 npm test
```

Suite đầu tải ZIP thật và kích hoạt bằng giao diện WordPress chạy PHP 7.4.30. Suite sau kiểm tra trang, sản phẩm, biểu mẫu, xác thực, chống spam, quyền riêng tư và trạng thái yêu cầu. Chỉ chạy trên môi trường thử nghiệm.

## Thay đổi trong 1.0.1

- Plugin và theme khai báo PHP tối thiểu 7.4, được chạy kiểm thử bằng PHP 7.4.30.
- Hook kích hoạt plugin chỉ đăng ký loại nội dung của plugin, tránh gọi lại toàn bộ sự kiện `init` của WordPress và tạo cảnh báo đăng ký khối lặp.
- Gói ZIP giữ nguyên thư mục cài đặt, không xóa hoặc khởi tạo lại sản phẩm, bài viết, thiết lập giao diện hay yêu cầu báo giá khi cập nhật.
