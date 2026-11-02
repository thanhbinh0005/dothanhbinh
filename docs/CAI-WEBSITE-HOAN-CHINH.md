# Cài website Thanh Bình hoàn chỉnh — PHP 7.4.30

Chỉ cần tải **thanh-binh-website-hoan-chinh-php74.zip**, không giải nén trước khi tải vào WordPress. Đây là plugin cài website, đã chứa cả theme và nội dung khởi tạo; khác với gói kit chứa nhiều ZIP bên trong.

1. Mở quản trị WordPress. Với XAMPP đã cấu hình, địa chỉ là `http://localhost:8899/thanhbinh/wp-admin/`.
2. Vào **Plugin → Cài mới → Tải plugin lên**, chọn `thanh-binh-website-hoan-chinh-php74.zip`, bấm Cài đặt rồi Kích hoạt. Nếu đã có plugin Thanh Bình cũ, chọn thay thế bản hiện tại. Bản này giữ nguyên thư mục `thanh-binh-orders` để cập nhật đúng plugin.
3. Vào menu **Thanh Bình** bên trái → bấm **Thiết lập website**.
4. Chờ trang báo **Trang chủ đã sẵn sàng**, rồi bấm **Xem website**.

Nút thiết lập cài hoặc cập nhật và kích hoạt theme Thanh Bình, tạo trang chủ, giới thiệu, liên hệ, trang bài viết, menu chính, 3 sản phẩm, 3 bài viết và ảnh minh họa pallet. Sản phẩm mẫu đầu tiên là **Pallet gỗ kê hàng**, có nội dung, mô tả ngắn và ảnh đại diện. Không cần nhập XML hoặc chạy lệnh kỹ thuật để có nội dung mẫu.

Nếu đã có bài viết, sản phẩm, menu, trang chủ hoặc thông tin liên hệ, thiết lập giữ nội dung hiện có và chỉ bổ sung phần thiếu theo đường dẫn khởi tạo. Các nội dung mẫu đã chỉnh không bị ghi đè khi bấm lại. Yêu cầu báo giá không bị xóa. Nếu từng sửa trực tiếp mã của theme Thanh Bình, hãy sao lưu theme trước khi dùng nút cập nhật giao diện.

## Chỉnh sửa sau khi cài

- **Thanh Bình → Đổi logo & thông tin công ty:** tên, điện thoại, Zalo, email, địa chỉ, ảnh đầu trang, màu nhấn và nội dung giới thiệu.
- **Sản phẩm pallet:** thêm, sửa, xóa sản phẩm; đặt ảnh đại diện; chỉnh thông số và nhóm.
- **Bài viết:** chỉnh 3 bài có sẵn hoặc viết thêm. Trang Bài viết hiển thị danh sách có phân trang.
- **Trang:** chỉnh trang Giới thiệu và Liên hệ bằng trình soạn thảo khối.
- **Yêu cầu báo giá:** xem thông tin khách và thay đổi trạng thái xử lý.

Ảnh `pallet-go-ke-hang.jpg` là ảnh minh họa tạo bằng AI theo yêu cầu, không phải ảnh xác thực xưởng của công ty. Ảnh được nhập vào Thư viện và gắn cho sản phẩm mẫu khi thiết lập. Bạn có thể tải riêng ảnh này và dùng Đặt ảnh đại diện trên sản phẩm tự tạo. Logo và các ảnh nguyên bản gửi trong chat vẫn cần được tải vào Thư viện khi có tệp gốc.

Điện thoại/Zalo/email/địa chỉ chưa điền tự động. Yêu cầu được lưu trong quản trị; cần cấu hình SMTP để gửi email thông báo. Hoàn thiện thông tin doanh nghiệp và chính sách bảo mật trước khi công khai website.

Yêu cầu: WordPress 6.6+, PHP 7.4 trở lên (đã có môi trường kiểm thử PHP 7.4.30), tài khoản quản trị có quyền cài và kích hoạt theme. Website chạy trong WordPress hiện có; gói này không bao gồm bộ cài WordPress core hoặc XAMPP.

## Kiểm thử bản 1.1.0

Đã cài từ một ZIP qua trang quản trị WordPress trống dùng PHP 7.4.30, bấm nút thiết lập để cài theme đi kèm và tạo nội dung. Đã kiểm tra trang chủ, trang bài viết, giới thiệu, liên hệ, ảnh đại diện và việc chạy lại không nhân đôi nội dung. Cũng đã kiểm tra cập nhật từ bộ 1.0.1, giữ các chỉnh sửa hiện có. Các luồng gửi báo giá, xác thực, giới hạn gửi, quyền riêng tư và cập nhật trạng thái qua trên PHP 7.4.30 và PHP 8.3. Đây là kiểm thử trong môi trường phát triển, không phải thao tác trực tiếp lên XAMPP của người dùng.
