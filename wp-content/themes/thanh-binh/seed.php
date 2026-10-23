<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if (get_option('tb_content_seed_v1')) { WP_CLI::success('Giữ nguyên nội dung đã có.'); return; }
$types = ['pallet-go' => 'Pallet gỗ', 'linh-kien' => 'Linh kiện pallet', 'theo-yeu-cau' => 'Theo yêu cầu'];
foreach ($types as $slug => $name) { if (!term_exists($slug, 'tb_product_type')) { wp_insert_term($name, 'tb_product_type', ['slug' => $slug]); } }
$products = [
 ['pallet-go-theo-quy-cach','Pallet gỗ theo quy cách','Giải pháp kê hàng và đóng gói; chọn kích thước, kết cấu theo nhu cầu sử dụng.','pallet-go','Theo kích thước kiện hàng','Gỗ — xác nhận loại khi tư vấn','Tư vấn theo nhu cầu'],
 ['chan-cuc-pallet','Chân cục pallet','Chân cục dùng trong lắp ráp pallet. Trao đổi quy cách để chọn phương án phù hợp.','linh-kien','Theo bản vẽ / mẫu xác nhận','Vật liệu xác nhận khi báo giá','Linh kiện pallet'],
 ['giai-phap-pallet-rieng','Giải pháp pallet riêng','Trao đổi đặc điểm hàng hóa để đề xuất quy cách phù hợp cho doanh nghiệp.','theo-yeu-cau','Thiết kế theo nhu cầu','Xác nhận theo phương án','Quy cách linh hoạt'],
];
foreach ($products as $i => $p) {
 if (get_page_by_path($p[0], OBJECT, 'tb_product')) continue;
 $content = '<!-- wp:paragraph --><p>'.esc_html($p[2]).'</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Thông tin cần trao đổi khi đặt hàng</h2><!-- /wp:heading --><!-- wp:list --><ul class="wp-block-list"><li>Kích thước dài × rộng × cao hoặc bản vẽ của bạn.</li><li>Khối lượng hàng, cách kê đỡ và điều kiện kho.</li><li>Số lượng dự kiến, thời gian và địa điểm giao nhận.</li><li>Yêu cầu vật liệu, xử lý và hồ sơ nếu có.</li></ul><!-- /wp:list --><!-- wp:paragraph --><p>Quy cách, khả năng chịu tải và các yêu cầu xử lý vật liệu được xác nhận trong báo giá riêng. Với hàng xuất khẩu, vui lòng nêu yêu cầu thị trường và đối tác để kiểm tra khả năng đáp ứng; không mặc định sản phẩm có chứng nhận xử lý.</p><!-- /wp:paragraph -->';
 $id = wp_insert_post(['post_type'=>'tb_product','post_status'=>'publish','post_name'=>$p[0],'post_title'=>$p[1],'post_excerpt'=>$p[2],'post_content'=>$content,'menu_order'=>$i],true);
 if (is_wp_error($id)) WP_CLI::error($id->get_error_message());
 wp_set_object_terms($id,$p[3],'tb_product_type');
 foreach (['size'=>$p[4],'material'=>$p[5],'badge'=>$p[6]] as $key=>$value) update_post_meta($id,'_tb_'.$key,$value);
}
$category = get_term_by('slug','kien-thuc-pallet','category');
if (!$category) { $term=wp_insert_term('Kiến thức & sản phẩm','category',['slug'=>'kien-thuc-pallet']); $cat_id=is_wp_error($term)?1:$term['term_id']; } else $cat_id=$category->term_id;
$posts = [
 ['chon-pallet-go-phu-hop','Chọn pallet gỗ phù hợp: bắt đầu từ kiện hàng của bạn','Kích thước, cách nâng và điều kiện sử dụng là những thông tin cần làm rõ trước khi chọn pallet.','pallet',[
 ['p','Một pallet phù hợp cần đáp ứng cách hàng hóa được đóng gói, lưu trữ và vận chuyển. Thay vì chọn chỉ theo hình thức, hãy bắt đầu bằng đặc điểm kiện hàng và điều kiện sử dụng thực tế.'],
 ['h','1. Xác định kích thước và cách xếp'],['p','Ghi lại chiều dài, chiều rộng kiện hàng, số lớp xếp và vị trí tiếp xúc trên mặt pallet. Tránh để hàng nhô ra ngoài khi chưa có phương án bảo vệ. Nếu sử dụng kệ kho hoặc container, cần đối chiếu thêm không gian làm việc.'],
 ['h','2. Phân biệt điều kiện chịu tải'],['p','Tải khi đặt trên nền, khi di chuyển bằng xe nâng và khi kê trên kệ là các điều kiện khác nhau. Không dùng một con số tải trọng để thay thế cho mọi tình huống. Cần thống nhất cách xếp hàng và điểm đỡ khi trao đổi thông số.'],
 ['h','3. Trao đổi môi trường sử dụng'],['p','Độ ẩm, thời gian lưu kho và tần suất tái sử dụng ảnh hưởng đến việc chọn vật liệu. Với hàng xuất khẩu, hãy nêu yêu cầu của đối tác và nước nhập khẩu để kiểm tra yêu cầu xử lý, hồ sơ và khả năng cung ứng trước khi đặt.'],
 ['h','Cần chuẩn bị gì để nhận tư vấn?'],['p','Gửi kích thước kiện hàng, khối lượng, cách nâng, số lượng và nơi nhận. Nếu có bản vẽ hoặc mẫu đang dùng, hãy nhắc tới trong ghi chú; công ty sẽ trao đổi cách nhận tài liệu khi liên hệ.']]],
 ['chan-cuc-pallet-thong-tin-can-biet','Chân cục pallet: những thông tin cần biết trước khi đặt','Từ kích thước đến cách liên kết, cùng chuẩn bị thông số cho phương án chân cục phù hợp.','blocks',[
 ['p','Chân cục tạo khoảng hở bên dưới mặt pallet và là một phần của kết cấu truyền tải. Trong ảnh công ty cung cấp, có thể thấy dạng cục với các lớp vật liệu gỗ xếp chồng. Hình ảnh giúp nhận biết dạng sản phẩm; thông số kỹ thuật phải được xác nhận riêng.'],
 ['h','Kích thước cần thống nhất'],['p','Khi hỏi mua, ghi rõ chiều dài × chiều rộng × chiều cao và dung sai nếu có. Nêu vị trí sử dụng, cách liên kết và nhu cầu khoan hoặc gia công bổ sung. Công ty sẽ xác nhận phần có thể đáp ứng trong phương án báo giá.'],
 ['h','Không suy ra tải trọng chỉ từ ảnh'],['p','Khả năng chịu lực phụ thuộc vật liệu, chất lượng liên kết, phương án lắp ráp và môi trường sử dụng. Cần trao đổi yêu cầu thực tế và cách kiểm tra; ảnh không thay thế báo cáo thử nghiệm hoặc chứng nhận.'],
 ['h','Gửi yêu cầu chân cục pallet'],['p','Chọn “Chân cục pallet” trong biểu mẫu, điền số lượng và quy cách mong muốn. Nếu chưa có bản vẽ, mô tả pallet đang dùng để công ty trao đổi thêm trước khi báo giá.']]],
 ['huong-dan-gui-yeu-cau-bao-gia','5 thông tin giúp nhận báo giá pallet rõ ràng hơn','Chuẩn bị đúng thông tin để việc trao đổi phương án, chi phí và kế hoạch giao nhận thuận tiện hơn.','pallet',[
 ['p','Báo giá pallet cần dựa trên nhu cầu cụ thể. Bạn chưa cần bản vẽ hoàn chỉnh ngay từ đầu; những thông tin sau giúp công ty có cơ sở trao đổi và làm rõ phương án.'],
 ['h','1. Loại sản phẩm'],['p','Cho biết bạn cần pallet gỗ, chân cục hay một giải pháp riêng. Nếu chưa biết loại phù hợp, hãy chọn mục cần tư vấn.'],
 ['h','2. Quy cách'],['p','Ghi dài × rộng × cao, hoặc kích thước hàng cần kê. Nêu đơn vị đo để tránh nhầm lẫn.'],
 ['h','3. Công năng'],['p','Cung cấp khối lượng hàng, cách nâng, vị trí kê trên nền hay kệ và điều kiện kho. Đây là cơ sở để trao đổi kết cấu.'],
 ['h','4. Số lượng'],['p','Nêu số lượng dự kiến cho lần đầu và kế hoạch các đợt sau nếu đã có. Số lượng là một yếu tố để thống nhất phương án cung ứng.'],
 ['h','5. Giao nhận'],['p','Cung cấp địa điểm, thời gian mong muốn và yêu cầu vận chuyển đặc biệt. Lịch cụ thể cần được hai bên xác nhận.'],
 ['h','Sau khi gửi biểu mẫu'],['p','Website lưu yêu cầu để công ty liên hệ lại. Hai bên trao đổi thông số, khả năng cung ứng, chi phí và lịch dự kiến. Đơn hàng chỉ được xác nhận sau khi thống nhất báo giá và các điều kiện liên quan. Không gửi thông tin thanh toán hoặc dữ liệu nhạy cảm qua biểu mẫu tư vấn.']]],
];
foreach ($posts as $p) {
 if (get_page_by_path($p[0],OBJECT,'post')) continue;
 $content='';
 foreach ($p[4] as [$kind,$text]) $content .= $kind==='h'?'<!-- wp:heading --><h2 class="wp-block-heading">'.esc_html($text).'</h2><!-- /wp:heading -->':'<!-- wp:paragraph --><p>'.esc_html($text).'</p><!-- /wp:paragraph -->';
 $id=wp_insert_post(['post_type'=>'post','post_status'=>'publish','post_name'=>$p[0],'post_title'=>$p[1],'post_excerpt'=>$p[2],'post_content'=>$content,'post_category'=>[$cat_id],'meta_input'=>['_tb_media_group'=>$p[3]]],true);
 if (is_wp_error($id)) WP_CLI::error($id->get_error_message());
}
if (!get_page_by_path('chinh-sach-bao-mat')) {
 $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'Chính sách bảo mật','post_name'=>'chinh-sach-bao-mat','post_content'=>'<!-- wp:paragraph --><p>Biểu mẫu báo giá thu thập họ tên, điện thoại, sản phẩm, số lượng và thông tin bạn tự nguyện cung cấp như email, công ty và quy cách. Công ty dùng dữ liệu để liên hệ, tư vấn và xử lý yêu cầu; gửi biểu mẫu chưa tạo đơn hàng được xác nhận.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Lưu trữ và truy cập</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Thông tin được lưu trong khu vực quản trị WordPress, chỉ quản trị viên được phép truy cập. Khi cấu hình email, bản thông báo được chuyển qua nhà cung cấp email. Biểu mẫu dùng mã xác thực và bộ đếm tạm thời trong khoảng 15 phút để hạn chế gửi lặp. Địa chỉ kết nối được chuyển thành mã băm cho bộ đếm; plugin không lưu IP thô cùng yêu cầu. Nhật ký hosting và dịch vụ email phụ thuộc cấu hình nhà cung cấp.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Yêu cầu chỉnh sửa hoặc xóa</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Bạn có thể liên hệ công ty qua thông tin trên website để yêu cầu kiểm tra, sửa hoặc xóa dữ liệu sau khi xác minh danh tính. Không gửi dữ liệu nhạy cảm vào ghi chú.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><strong>Cần hoàn thiện trước khi công khai:</strong> chủ website bổ sung danh tính đơn vị phụ trách, địa chỉ liên hệ về dữ liệu, thời hạn lưu, nhà cung cấp thực tế và các nội dung áp dụng cho doanh nghiệp.</p><!-- /wp:paragraph -->'],true);
 if (is_wp_error($id)) WP_CLI::error($id->get_error_message());
 update_option('wp_page_for_privacy_policy',$id);
}
// Delete only known, untouched defaults on this newly initialized site.
$hello=get_post(1);
if (get_option('tb_fresh_install') && $hello && $hello->post_name==='hello-world' && $hello->post_title==='Hello world!' && $hello->post_type==='post') wp_delete_post(1,true);
update_option('tb_content_seed_v1',1);
WP_CLI::success('Đã tạo 3 sản phẩm, 3 bài viết và chính sách bảo mật mẫu.');
