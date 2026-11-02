# Thanh Bình Hưng Yên — WordPress

Website tiếng Việt giới thiệu pallet gỗ, chân cục pallet, nhận yêu cầu báo giá và quản lý nội dung. Theme riêng gọn nhẹ; không cần Elementor hoặc plugin trả phí.

**Đã chạy:** WordPress 7.1.3, PHP 8.3, MariaDB 11.4. Giao diện hỗ trợ điện thoại; 3 sản phẩm và 3 bài viết khởi tạo. Nội dung, ảnh, logo và liên hệ chỉnh trong quản trị. Yêu cầu báo giá lưu riêng tư, có trạng thái xử lý. Email là kênh thông báo bổ sung, không thay việc lưu yêu cầu.

**Tương thích XAMPP:** plugin và theme bản 1.1.0 hỗ trợ PHP 7.4, bao gồm PHP 7.4.30. Có gói tải riêng `dist/thanh-binh-orders-php74.zip`, `dist/thanh-binh-theme-php74.zip` và bộ đầy đủ `dist/thanh-binh-wordpress-kit-php74.zip`. Xem [cài trên PHP 7.4.30](docs/PHP74-XAMPP.md). Môi trường Docker chính vẫn dùng PHP 8.3.

**Chưa công khai:** chưa triển khai lên hosting hoặc tên miền. Chưa nhập được ảnh gốc từ hội thoại vì ảnh chưa được cung cấp dưới dạng tệp trong workspace; có ảnh pallet minh họa AI và các SVG tự tạo, không phải ảnh xác thực xưởng hoặc logo gốc của công ty. Xem `content/media/README.md` để nhập ảnh gốc. Chưa cấu hình email/SMTP hoặc thông tin liên hệ theo yêu cầu của chủ website. Chính sách bảo mật cần hoàn thiện theo doanh nghiệp thực tế.

## Cài website hoàn chỉnh trong WordPress

Tải **`dist/thanh-binh-website-hoan-chinh-php74.zip`**, cài ở **Plugin → Cài mới → Tải plugin lên**, kích hoạt, rồi vào **Thanh Bình → Thiết lập website**. Một ZIP đã chứa theme, trang chủ, menu, 3 sản phẩm, 3 bài viết và ảnh đại diện mẫu. Không cần nhập XML. Xem [hướng dẫn cài trọn bộ](docs/CAI-WEBSITE-HOAN-CHINH.md).

Ảnh `dist/pallet-go-ke-hang.jpg` là ảnh minh họa tạo bằng AI theo yêu cầu, dùng làm ảnh đại diện của sản phẩm Pallet gỗ kê hàng. Gói cài tự nhập ảnh này vào Thư viện. Các logo và ảnh nguyên bản gửi trước đó vẫn cần tệp gốc để nhập chính xác.

## Chạy trong môi trường phát triển

Yêu cầu Docker Engine + Compose v2, Bash và Python 3. Lần đầu cần Internet tới Docker Hub. Image được cố định bằng digest trong `compose.yaml`.

```bash
cd /workspace/dothanhbinh
bash scripts/setup.sh
```

Script tạo `.env` với mật khẩu ngẫu nhiên, dựng cơ sở dữ liệu và WordPress, kích hoạt theme/plugin, tạo nội dung một lần. Chạy lại giữ nội dung đã chỉnh. Chỉ lắng nghe loopback cổng 8080, không tự đưa website lên Internet.

Tài khoản quản trị phát triển: `siteadmin`. Mật khẩu khởi tạo nằm trong `.runtime/admin-password` trên máy của bạn. Không đưa tệp này lên Git hoặc chia sẻ; thay mật khẩu và email quản trị khi chuyển hosting. Đường dẫn quản trị là `/wp-admin/` trên địa chỉ triển khai. Nếu bạn đổi mật khẩu trong WordPress, tệp khởi tạo không tự cập nhật.

Dừng/khởi động lại các dịch vụ:

```bash
docker compose stop
docker compose up -d --wait db wordpress
```

Dữ liệu nằm trong Docker volumes, không nằm trong mã theme/plugin. Không dùng `docker compose down -v` vì thao tác đó xóa dữ liệu. Sao lưu cơ sở dữ liệu và thư mục uploads riêng.

Để đổi cổng lúc cài mới, sửa `WP_PORT` trong `.env`. Khi thay URL của site đã có dữ liệu, cần cập nhật `home`, `siteurl` và URL lưu trong cơ sở dữ liệu đúng cách. Không chỉnh mật khẩu DB trong `.env` khi volume DB đã được khởi tạo mà chưa đổi tương ứng trong MariaDB.

## Quản trị và triển khai

- [Hướng dẫn quản trị](docs/QUAN-TRI.md)
- [Cài lên hosting và gắn tên miền](docs/TRIEN-KHAI.md)
- [Phân tích ảnh và phân bổ nội dung](docs/NOI-DUNG-VA-HINH-ANH.md)
- [Ảnh gốc cần nhập](content/media/README.md)

## Kiểm thử

```bash
npm ci --ignore-scripts --cache /tmp/thanh-binh-npm-cache
CHROMIUM_PATH=/usr/bin/chromium npm test
```

Node.js 20+ và Chromium cần có trên máy. Có thể đặt `CHROMIUM_PATH` đến Chromium đã cài. Suite chạy trên dữ liệu phát triển (`WP_ENVIRONMENT_TYPE=local`), tạo rồi xóa yêu cầu kiểm thử, đặt lại bộ đếm giới hạn gửi của plugin; không chạy trên website đang nhận khách thật. Suite đọc mật khẩu quản trị từ tệp cục bộ, không in ra.

Các kiểm tra: trang chủ, bộ lọc, chi tiết sản phẩm, bài viết, 4 kích thước màn hình, menu bàn phím, gửi/lưu yêu cầu, dữ liệu sai, mã xác thực, chống spam, giới hạn gửi, quyền riêng tư và cập nhật trạng thái trong quản trị. Ảnh chụp kết quả nằm trong `.runtime/screenshots/`.

## Gói cài

```bash
bash scripts/export-content.sh
python3 scripts/package.py
```

Tạo gói tổng `dist/thanh-binh-wordpress-kit.zip` cùng `dist/thanh-binh-theme.zip` và `dist/thanh-binh-orders.zip` để tải lên WordPress. `dist/thanh-binh-content.xml` là nội dung đã xuất từ website phát triển, không chứa yêu cầu khách hàng, mật khẩu hoặc cấu hình DB. Gói cài có hướng dẫn trong `docs/`.

Các SVG đi kèm là hình minh họa tự tạo để website hoạt động khi chưa có ảnh; không là bản sao logo gửi trong hội thoại. Không có ảnh stock hoặc font bên ngoài được tải ngầm.
