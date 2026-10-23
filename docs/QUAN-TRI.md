# Sử dụng WordPress

Đăng nhập đường dẫn `/wp-admin/` của website bằng tài khoản được tạo trên hosting. Tài khoản phát triển và mật khẩu khởi tạo được mô tả trong README; không dùng lại cho bản công khai.

| Việc cần làm | Nơi thao tác |
| --- | --- |
| Đổi logo ngang | Giao diện → Tùy biến → Nhận diện website → Logo |
| Đổi favicon / biểu tượng vuông TB | Cài đặt → Tổng quan → Biểu tượng website (hoặc Nhận diện website, tùy phiên bản) |
| Điền công ty, điện thoại, Zalo, email, địa chỉ | Giao diện → Tùy biến → Công ty & nội dung trang chủ |
| Đổi tiêu đề, mô tả đầu trang, giới thiệu | Cùng mục Công ty & nội dung trang chủ |
| Đổi ảnh đầu trang, ảnh xưởng, màu nhấn | Cùng mục Công ty & nội dung trang chủ |
| Ẩn/hiện giới thiệu, quy trình, bài viết | Giao diện → Tùy biến → Bật / tắt các phần trang chủ |
| Chỉnh menu | Giao diện → Menu, gán vị trí Menu chính |
| Thêm/sửa/xóa sản phẩm | Sản phẩm pallet → Tất cả sản phẩm / Thêm sản phẩm |
| Chia nhóm sản phẩm | Sản phẩm pallet → Nhóm sản phẩm |
| Viết bài, sửa bài, thay ảnh, xóa bài | Bài viết → Tất cả bài viết / Viết bài mới |
| Thay ảnh, thêm ảnh mới | Thư viện → Thêm tệp media |
| Xem khách gửi yêu cầu | Yêu cầu báo giá |
| Đổi trạng thái xử lý | Mở yêu cầu → Trạng thái xử lý → Cập nhật |
| Chính sách bảo mật | Trang → Chính sách bảo mật; Cài đặt → Riêng tư |

## Nhập ảnh bạn đã gửi

Logo ngang nên dùng cho đầu trang; biểu tượng TB vuông dùng cho favicon. Ảnh chân cục gán cho sản phẩm chân cục và bài viết tương ứng; ảnh pallet trong kho dùng cho pallet, đầu trang và bài chọn pallet. Không dùng logo thay ảnh sản phẩm.

Tải ảnh gốc vào Thư viện, nhập văn bản thay thế mô tả ảnh, rồi chọn ảnh đại diện ở bài viết/sản phẩm. Đặt logo, ảnh đầu trang và ảnh xưởng trong Tùy biến. Không cần sửa mã. Nếu đang dùng Docker, có thể nhập hàng loạt theo `content/media/README.md`.

Điện thoại và Zalo vẫn trống để bạn tự cấu hình. Ảnh quảng cáo có in số 0972 037 939; kiểm tra số hiện hành trước khi điền hoặc dùng ảnh công khai.

Ảnh logo ngang có nền trắng; chân trang giữ nền sáng để logo đọc rõ. Ảnh quảng cáo có chữ ở mép nên kiểm tra vùng cắt trên thẻ bài viết/điện thoại. Nếu muốn giữ toàn bộ chữ, dùng ảnh đầy đủ trong nội dung bài thay vì chỉ ảnh đại diện.

## Sản phẩm

Mỗi sản phẩm có tên, nội dung chi tiết bằng trình soạn thảo khối, tóm tắt, ảnh đại diện, nhóm và thông số. Tại hộp Thông số sản phẩm, điền kích thước, vật liệu, tải trọng đã xác minh và nhãn nếu cần. Thứ tự nằm ở Thuộc tính → Thứ tự. Không cần điền giá cố định vì quy trình là báo giá riêng. Sản phẩm nháp hoặc đã xóa không thể được đặt qua biểu mẫu công khai.

Các sản phẩm ban đầu mô tả danh mục, chưa là thông số kỹ thuật được cam kết. Chỉ công khai tải trọng, xử lý ISPM 15 hoặc chứng nhận khi có căn cứ và khả năng cung ứng thực tế.

## Nhận và xử lý yêu cầu

Khách điền biểu mẫu và đồng ý liên hệ → website lưu yêu cầu riêng tư → quản trị viên trao đổi → báo giá → xác nhận đặt hàng riêng. Biểu mẫu không thu tiền và không xác nhận mua hàng tự động.

Các trạng thái: Mới tiếp nhận, Đã liên hệ, Đã báo giá, Đã xác nhận đặt hàng, Đã đóng. Đổi rồi bấm Cập nhật. Thông báo email có trạng thái riêng: chưa cấu hình, đã chuyển cho hệ thống gửi hoặc gửi thất bại. Hệ thống mail nhận yêu cầu không chứng minh thư đã vào hộp thư đích. Hãy kiểm tra trang quản trị thường xuyên.

Chỉ tài khoản quản trị được xem yêu cầu. Tài khoản biên tập nội dung không được cấp quyền đọc dữ liệu khách trong plugin. Khi thêm nhân viên, cân nhắc quyền được cấp và tạo tài khoản riêng.

Công cụ WordPress xuất/xóa dữ liệu cá nhân hỗ trợ yêu cầu có email. Với yêu cầu chỉ có điện thoại, quản trị viên cần xác minh rồi xử lý thủ công. Plugin không tự xóa yêu cầu theo thời gian; công ty phải quyết định thời hạn lưu, ghi trong chính sách và thực hiện dọn dữ liệu phù hợp.

## Email

Điền email nhận ở mục Công ty & nội dung trang chủ. Cấu hình SMTP qua hosting hoặc một plugin SMTP phù hợp; nhập mật khẩu/API key trong giao diện bảo mật của hosting hoặc plugin, không trong mã nguồn. Gửi thư kiểm tra rồi gửi một yêu cầu thật từ trang công khai, xác nhận cả dữ liệu lưu lẫn hộp thư nhận. Thiết lập SPF/DKIM theo nhà cung cấp mail.
