# Cài WordPress và dùng tên miền riêng

Mã hiện chạy trong môi trường phát triển. Chưa có thao tác xuất bản website hoặc thay đổi tên miền. Liên kết mẫu không được chỉnh sửa.

## Cách dễ nhất: hosting WordPress có SSL

1. Chuẩn bị hosting hỗ trợ WordPress, PHP 8.3+, MariaDB/MySQL và HTTPS. Cài bản WordPress ổn định, đặt tài khoản quản trị cùng email thật của bạn. Không nhập mật khẩu hosting vào chat hoặc mã nguồn.
2. Tải lên `dist/thanh-binh-orders.zip` tại Plugin → Cài mới → Tải plugin; kích hoạt.
3. Tải `dist/thanh-binh-theme.zip` tại Giao diện → Giao diện → Thêm → Tải giao diện; kích hoạt Thanh Bình Wood.
4. Nhập `dist/thanh-binh-content.xml` ở Công cụ → Nhập → WordPress. Cài trình nhập chính thức nếu được hỏi; gán tác giả về quản trị viên thật. XML chỉ có sản phẩm, bài viết, trang bảo mật; không có ảnh gốc, thông tin DB hoặc đơn thử. Nếu có trang mẫu không dùng, tự kiểm tra trước khi xóa.
5. Trong Cài đặt → Hiển thị, chọn trang chủ hiển thị bài viết mới nhất. Theme `front-page.php` tạo trang chủ riêng, không cần tạo trang trống. Trong Cài đặt → Đường dẫn tĩnh, chọn Tên bài viết, bấm Lưu để nhận các đường dẫn sản phẩm.
6. Trong Cài đặt → Riêng tư, chọn trang Chính sách bảo mật vừa nhập. Hoàn thiện thông tin pháp nhân, đầu mối dữ liệu, thời hạn lưu và các dịch vụ thực tế trước khi công khai.
7. Nhập ảnh gốc, logo và thông tin công ty trong Tùy biến theo hướng dẫn quản trị. Cấu hình email nhận và SMTP, kiểm tra thư thật. Ảnh trong hội thoại chưa được đóng gói vì chưa có tệp gốc trong workspace.
8. Kiểm tra một yêu cầu báo giá trên điện thoại, xác nhận thấy dữ liệu trong quản trị và nhận được email. Xóa yêu cầu thử sau khi kiểm tra.

## Gắn tên miền

- Nếu chưa sở hữu tên miền, đăng ký qua nhà đăng ký bạn chọn.
- Trên hosting, thêm tên miền vào website WordPress (mục Domains/Add domain).
- Tại DNS, trỏ bản ghi A cho tên miền gốc và bản ghi cho `www` theo đúng địa chỉ/IP do hosting cung cấp. Không tự dùng IP của máy phát triển.
- Chờ DNS cập nhật, cấp chứng chỉ SSL bằng công cụ hosting, sau đó đặt Địa chỉ WordPress và Địa chỉ trang web về URL HTTPS mới. Sao lưu trước khi đổi; sai URL có thể khiến bạn mất truy cập tạm thời.
- Nếu chuyển website đã có dữ liệu giữa các URL, dùng công cụ migration hỗ trợ dữ liệu tuần tự hóa hoặc WP-CLI `wp search-replace OLD_URL NEW_URL --all-tables-with-prefix --skip-columns=guid --dry-run`, kiểm tra bản thử trước khi chạy thật. Cập nhật `home`/`siteurl` và kiểm tra ảnh, liên kết, form, trang quản trị. Không thay chuỗi trực tiếp trong SQL dump.
- Chọn một địa chỉ chính, chuyển hướng biến thể còn lại và ép HTTPS theo hướng dẫn hosting. Kiểm tra đường dẫn biểu mẫu gửi cùng tên miền, không gửi về máy phát triển.

Việc hỗ trợ tên miền không bao gồm phí đăng ký hoặc hosting. Cần quyền quản trị hosting/DNS để thực hiện xuất bản; chưa có các quyền hoặc tên miền trong phiên này.

## Nếu dùng VPS / Docker

`compose.yaml` là cấu hình phát triển, chỉ bind loopback. Không đưa thẳng cấu hình này lên Internet. Trước khi triển khai production, chuẩn bị reverse proxy HTTPS, domain thật, `WP_ENVIRONMENT_TYPE=production`, thông tin quản trị thật, backup DB/uploads và SMTP. Đặt tin cậy cho proxy đúng cách tại webserver; không tin mọi `X-Forwarded-For`. Bộ đếm chống spam dùng địa chỉ kết nối trực tiếp, nên cần chuẩn hóa IP thật tại proxy tin cậy hoặc bổ sung chống spam ở lớp hosting.

Bảo vệ `.env`, `.runtime/`, backup và database khỏi truy cập web. WordPress production chỉ cần mã theme/plugin, core, uploads và cấu hình riêng của hosting. Không chép cả repository làm thư mục web công khai.

Loại trừ `admin-post.php` khỏi cache/CDN. Không cache trang biểu mẫu quá hạn nonce WordPress (mặc định tối đa khoảng một ngày). Giữ cập nhật WordPress, plugin và các image Docker; mỗi lần nâng bản cần kiểm thử lại và cập nhật digest. Không tắt TLS hoặc xác minh gói tải xuống.
