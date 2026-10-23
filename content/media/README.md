Ảnh gốc từ chủ website chưa có trong filesystem. Không dùng bản vẽ thay thế để giả làm logo gốc.

Đặt các tệp do chủ website gửi vào đây:

- logo-ngang.jpg / .png: logo có chữ THANH BÌNH HƯNG YÊN (ảnh thứ nhất).
- bieu-tuong-tb.jpg / .png: biểu tượng vuông TB trên pallet (chọn một trong hai bản trùng).
- chan-cuc-pallet.jpg / .png: ảnh chân cục (nên dùng bản rõ, ảnh thứ năm).
- pallet-go.jpg / .png: ảnh pallet trong kho (ảnh cuối).

Sau đó chạy `bash scripts/import-media.sh`. Lệnh nhập ảnh vào Media, đặt logo và biểu tượng trang, gán ảnh sản phẩm/bài viết, ảnh đầu trang và giới thiệu. Giữ ảnh gốc nguyên vẹn. Có thể thay hoặc gỡ tất cả trong WordPress.

Ảnh có chữ/số điện thoại nên dùng `object-fit: contain` nếu cần thấy đầy đủ; ảnh đầu trang dùng cover nên có thể bị cắt mép. Kiểm tra lại trên điện thoại trước khi công khai. Số điện thoại/Zalo được giữ trống theo yêu cầu; tự cấu hình trong WordPress dù ảnh có in số liên hệ.
