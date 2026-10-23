<?php
/**
 * Plugin Name: Thanh Bình — Sản phẩm & Yêu cầu báo giá
 * Description: Quản lý pallet và tiếp nhận yêu cầu báo giá riêng tư trong WordPress.
 * Version: 1.0.0
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) { exit; }
add_action('init', function () {
    register_post_type('tb_product', [
        'labels' => ['name' => 'Sản phẩm pallet', 'singular_name' => 'Sản phẩm', 'add_new_item' => 'Thêm sản phẩm', 'edit_item' => 'Sửa sản phẩm'],
        'public' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-products',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'rewrite' => ['slug' => 'san-pham'], 'has_archive' => false,
    ]);
    register_taxonomy('tb_product_type', 'tb_product', [
        'labels' => ['name' => 'Nhóm sản phẩm', 'singular_name' => 'Nhóm sản phẩm'],
        'hierarchical' => true, 'show_in_rest' => true, 'show_admin_column' => true,
        'rewrite' => ['slug' => 'nhom-san-pham'],
    ]);
    register_post_type('tb_quote', [
        'labels' => ['name' => 'Yêu cầu báo giá', 'singular_name' => 'Yêu cầu', 'edit_item' => 'Xử lý yêu cầu'],
        'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_rest' => false,
        'exclude_from_search' => true, 'menu_icon' => 'dashicons-email-alt', 'supports' => ['title'],
        'capabilities' => ['edit_post' => 'manage_options', 'read_post' => 'manage_options', 'delete_post' => 'manage_options', 'edit_posts' => 'manage_options', 'edit_others_posts' => 'manage_options', 'publish_posts' => 'manage_options', 'read_private_posts' => 'manage_options', 'delete_posts' => 'manage_options', 'delete_private_posts' => 'manage_options', 'delete_published_posts' => 'manage_options', 'delete_others_posts' => 'manage_options', 'edit_private_posts' => 'manage_options', 'edit_published_posts' => 'manage_options', 'create_posts' => 'do_not_allow'],
        'map_meta_cap' => false,
    ]);
});
register_activation_hook(__FILE__, function () { do_action('init'); flush_rewrite_rules(); });
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
add_action('add_meta_boxes', function () {
    add_meta_box('tb_product_specs', 'Thông số sản phẩm', function ($post) {
        wp_nonce_field('tb_product_save', 'tb_product_nonce');
        foreach (['size' => 'Kích thước (ví dụ: 1.100 × 1.100 mm)', 'material' => 'Vật liệu', 'load' => 'Tải trọng tham khảo (chỉ điền khi đã xác minh)', 'badge' => 'Nhãn nổi bật (không bắt buộc)'] as $key => $label) {
            echo '<p><label for="tb_' . esc_attr($key) . '">' . esc_html($label) . '</label><br><input class="widefat" id="tb_' . esc_attr($key) . '" name="tb_' . esc_attr($key) . '" value="' . esc_attr(get_post_meta($post->ID, '_tb_' . $key, true)) . '"></p>';
        }
        echo '<p>Ảnh sản phẩm: đặt ở mục Ảnh đại diện. Mô tả ngắn: mục Tóm tắt. Thứ tự hiển thị: Thuộc tính → Thứ tự.</p>';
    }, 'tb_product');
    add_meta_box('tb_quote_details', 'Thông tin khách hàng & yêu cầu', function ($post) {
        echo '<table class="widefat striped"><tbody>';
        foreach (['name' => 'Họ tên', 'company' => 'Công ty', 'phone' => 'Điện thoại', 'email' => 'Email', 'product' => 'Sản phẩm (tại thời điểm gửi)', 'quantity' => 'Số lượng', 'message' => 'Quy cách / ghi chú', 'submitted' => 'Thời điểm gửi', 'consent' => 'Đồng ý liên hệ', 'notification' => 'Thông báo email'] as $key => $label) {
            echo '<tr><th style="width:220px">' . esc_html($label) . '</th><td style="white-space:pre-wrap">' . esc_html(get_post_meta($post->ID, '_tb_' . $key, true)) . '</td></tr>';
        }
        echo '</tbody></table><p>Đây là yêu cầu tư vấn/báo giá, chưa phải đơn hàng đã xác nhận. Chỉ quản trị viên được xem thông tin này. Xóa yêu cầu khi hết thời hạn lưu theo chính sách của công ty.</p>';
    }, 'tb_quote', 'normal', 'high');
    add_meta_box('tb_quote_status', 'Trạng thái xử lý', function ($post) {
        wp_nonce_field('tb_quote_status_save', 'tb_quote_status_nonce');
        $status = get_post_meta($post->ID, '_tb_status', true) ?: 'new';
        echo '<select name="tb_status" class="widefat">';
        foreach (tb_quote_statuses() as $key => $label) { echo '<option value="' . esc_attr($key) . '" ' . selected($status, $key, false) . '>' . esc_html($label) . '</option>'; }
        echo '</select><p>Bấm Cập nhật để lưu trạng thái.</p>';
    }, 'tb_quote', 'side');
});
function tb_quote_statuses() { return ['new' => 'Mới tiếp nhận', 'contacted' => 'Đã liên hệ', 'quoted' => 'Đã báo giá', 'confirmed' => 'Đã xác nhận đặt hàng', 'closed' => 'Đã đóng']; }
add_action('save_post_tb_product', function ($id) {
    if (!isset($_POST['tb_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tb_product_nonce'])), 'tb_product_save') || !current_user_can('edit_post', $id) || wp_is_post_autosave($id) || wp_is_post_revision($id)) { return; }
    foreach (['size', 'material', 'load', 'badge'] as $key) {
        if (isset($_POST['tb_' . $key]) && is_scalar($_POST['tb_' . $key])) { update_post_meta($id, '_tb_' . $key, sanitize_text_field(wp_unslash($_POST['tb_' . $key]))); }
    }
});
add_action('save_post_tb_quote', function ($id) {
    if (!current_user_can('manage_options') || empty($_POST['tb_quote_status_nonce']) || !is_scalar($_POST['tb_quote_status_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tb_quote_status_nonce'])), 'tb_quote_status_save')) { return; }
    $status = isset($_POST['tb_status']) && is_scalar($_POST['tb_status']) ? sanitize_key($_POST['tb_status']) : '';
    if (array_key_exists($status, tb_quote_statuses())) { update_post_meta($id, '_tb_status', $status); }
});
add_filter('manage_tb_quote_posts_columns', function ($columns) {
    return ['cb' => $columns['cb'], 'title' => 'Yêu cầu', 'tb_phone' => 'Điện thoại', 'tb_product' => 'Sản phẩm', 'tb_quantity' => 'Số lượng', 'tb_status' => 'Trạng thái', 'date' => 'Ngày gửi'];
});
add_action('manage_tb_quote_posts_custom_column', function ($column, $id) {
    $key = str_replace('tb_', '', $column);
    $value = get_post_meta($id, '_tb_' . $key, true);
    echo esc_html($key === 'status' ? (tb_quote_statuses()[$value] ?? 'Mới tiếp nhận') : $value);
}, 10, 2);
function tb_render_quote_form() {
    $selected = isset($_GET['product']) && is_scalar($_GET['product']) ? absint($_GET['product']) : 0;
    $products = get_posts(['post_type' => 'tb_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
    $result = isset($_GET['quote']) && is_scalar($_GET['quote']) ? sanitize_key($_GET['quote']) : '';
    ?>
    <div id="quote-feedback" class="form-feedback <?php echo $result === 'success' ? 'success' : ''; ?>" role="status" aria-live="polite" <?php if (!$result) echo 'hidden'; ?>><?php if ($result === 'success') { echo 'Đã tiếp nhận yêu cầu. Chúng tôi sẽ liên hệ để trao đổi và xác nhận thông tin với bạn.'; } elseif ($result) { echo 'Chưa gửi được yêu cầu. Vui lòng kiểm tra thông tin hoặc tải lại trang và thử lại.'; } ?></div>
    <form class="quote-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="tb_submit_quote"><?php wp_nonce_field('tb_submit_quote', 'tb_quote_nonce', false); ?>
        <div class="form-row"><div class="field"><label for="quote-name">Họ và tên <span>*</span></label><input id="quote-name" name="customer_name" autocomplete="name" maxlength="120" placeholder="Nguyễn Văn An" required></div><div class="field"><label for="quote-phone">Số điện thoại <span>*</span></label><input id="quote-phone" name="phone" type="tel" autocomplete="tel" maxlength="25" placeholder="Số điện thoại liên hệ" pattern="\+?[0-9 .()\-]{8,25}" required></div></div>
        <div class="form-row"><div class="field"><label for="quote-company">Tên công ty</label><input id="quote-company" name="company" autocomplete="organization" maxlength="180" placeholder="Công ty của bạn (nếu có)"></div><div class="field"><label for="quote-email">Email</label><input id="quote-email" name="email" type="email" autocomplete="email" maxlength="254" placeholder="email@congty.vn"></div></div>
        <div class="form-row"><div class="field"><label for="quote-product">Sản phẩm quan tâm <span>*</span></label><select id="quote-product" name="product" required><option value="">Chọn loại sản phẩm</option><?php foreach ($products as $product): ?><option value="<?php echo esc_attr($product->ID); ?>" <?php selected($selected, $product->ID); ?>><?php echo esc_html($product->post_title); ?></option><?php endforeach; ?><option value="custom">Quy cách riêng / Cần tư vấn</option></select></div><div class="field"><label for="quote-quantity">Số lượng dự kiến <span>*</span></label><input id="quote-quantity" type="number" name="quantity" min="1" max="100000" step="1" placeholder="Ví dụ: 100" required></div></div>
        <div class="field"><label for="quote-message">Bạn cần thêm điều gì?</label><textarea id="quote-message" name="message" rows="3" maxlength="3000" placeholder="Kích thước, tải trọng, địa điểm giao hàng, thời gian cần nhận…"></textarea></div>
        <div class="honeypot" aria-hidden="true"><label>Để trống ô này<input name="website" tabindex="-1" autocomplete="off"></label></div>
        <label class="consent"><input type="checkbox" name="consent" value="1" required><span>Tôi đồng ý để công ty sử dụng thông tin trên nhằm liên hệ và xử lý yêu cầu. <?php if (get_privacy_policy_url()): ?><a href="<?php echo esc_url(get_privacy_policy_url()); ?>" target="_blank" rel="noopener">Chính sách bảo mật</a>.<?php endif; ?></span></label>
        <button class="button submit-quote" type="submit"><span>Gửi yêu cầu báo giá</span><?php echo function_exists('tb_icon') ? tb_icon('arrow') : '→'; ?></button><p class="form-footnote">Chưa phát sinh thanh toán hoặc xác nhận mua hàng.</p>
    </form>
    <?php
}
add_shortcode('thanh_binh_quote', 'tb_quote_shortcode');
function tb_quote_shortcode() { ob_start(); tb_render_quote_form(); return ob_get_clean(); }
function tb_quote_reply($success, $message, $code = 200) {
    $json = isset($_POST['tb_json']) && is_scalar($_POST['tb_json']) && $_POST['tb_json'] === '1';
    if ($json) { wp_send_json(['success' => $success, 'message' => $message], $code); }
    wp_safe_redirect(add_query_arg('quote', $success ? 'success' : 'error', home_url('/')) . '#bao-gia', 303); exit;
}
function tb_handle_quote() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { tb_quote_reply(false, 'Phương thức không được hỗ trợ.', 405); }
    foreach (['tb_quote_nonce', 'website', 'customer_name', 'phone', 'company', 'email', 'product', 'quantity', 'message', 'consent'] as $field) {
        if (isset($_POST[$field]) && !is_scalar($_POST[$field])) { tb_quote_reply(false, 'Thông tin không hợp lệ.', 422); }
    }
    if (empty($_POST['tb_quote_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tb_quote_nonce'])), 'tb_submit_quote')) { tb_quote_reply(false, 'Phiên gửi đã hết hạn. Vui lòng tải lại trang và thử lại.', 403); }
    if (!empty($_POST['website'])) { tb_quote_reply(false, 'Không thể xử lý yêu cầu này.', 422); }
    $input = function ($key, $multiline = false) { return $multiline ? sanitize_textarea_field(wp_unslash($_POST[$key] ?? '')) : sanitize_text_field(wp_unslash($_POST[$key] ?? '')); };
    $name = $input('customer_name'); $phone = $input('phone'); $company = $input('company');
    $email_raw = trim(wp_unslash($_POST['email'] ?? '')); $email = sanitize_email($email_raw);
    $product = $input('product'); $quantity_raw = $input('quantity'); $message = $input('message', true);
    if (strlen($name) < 2 || strlen($name) > 480 || !preg_match('/^\+?[0-9 .()\-]{8,25}$/', $phone) || strlen(preg_replace('/\D/', '', $phone)) < 8 || strlen(preg_replace('/\D/', '', $phone)) > 15 || strlen($company) > 720 || strlen($message) > 12000 || !ctype_digit($quantity_raw) || (int) $quantity_raw < 1 || (int) $quantity_raw > 100000 || ($email_raw !== '' && (!is_email($email_raw) || strlen($email_raw) > 254)) || ($_POST['consent'] ?? '') !== '1') {
        tb_quote_reply(false, 'Vui lòng nhập họ tên, điện thoại hợp lệ, số lượng từ 1–100.000 và đồng ý liên hệ. Kiểm tra email nếu có.', 422);
    }
    if ($product !== 'custom' && (!ctype_digit($product) || get_post_type((int) $product) !== 'tb_product' || get_post_status((int) $product) !== 'publish')) { tb_quote_reply(false, 'Vui lòng chọn sản phẩm đang được cung cấp hoặc chọn quy cách riêng.', 422); }
    // Use the direct client address, never a spoofable forwarded header. No raw IP is stored.
    $rate_key = 'tb_rate_' . hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown', wp_salt('nonce'));
    $attempts = (int) get_transient($rate_key);
    if ($attempts >= 5) { tb_quote_reply(false, 'Bạn đã gửi nhiều yêu cầu. Vui lòng chờ khoảng 15 phút rồi thử lại.', 429); }
    $product_name = $product === 'custom' ? 'Quy cách riêng / Cần tư vấn' : get_the_title((int) $product);
    $quantity = (int) $quantity_raw;
    $id = wp_insert_post(['post_type' => 'tb_quote', 'post_status' => 'private', 'post_title' => $name . ' — ' . $quantity . ' pallet — ' . current_time('d/m/Y H:i'), 'meta_input' => ['_tb_name' => $name, '_tb_phone' => $phone, '_tb_company' => $company, '_tb_email' => $email, '_tb_product' => $product_name, '_tb_product_id' => $product === 'custom' ? 0 : (int) $product, '_tb_quantity' => $quantity, '_tb_message' => $message, '_tb_submitted' => current_time('mysql'), '_tb_status' => 'new', '_tb_consent' => 'Đồng ý liên hệ và xử lý yêu cầu (biểu mẫu v1)']], true);
    if (is_wp_error($id) || !$id) { tb_quote_reply(false, 'Chưa lưu được yêu cầu. Vui lòng thử lại sau.', 500); }
    set_transient($rate_key, $attempts + 1, 15 * MINUTE_IN_SECONDS);
    $to = sanitize_email(get_theme_mod('tb_email', ''));
    $notified = false;
    if ($to) {
        $body = "Yêu cầu mới #{$id}\nKhách: {$name}\nCông ty: {$company}\nĐiện thoại: {$phone}\nEmail: {$email}\nSản phẩm: {$product_name}\nSố lượng: {$quantity}\nGhi chú: {$message}\n\nXử lý trong WordPress: " . admin_url('post.php?post=' . $id . '&action=edit');
        $notified = wp_mail($to, 'Yêu cầu báo giá mới #' . $id, $body);
    }
    update_post_meta($id, '_tb_notification', !$to ? 'Chưa cấu hình email nhận. Yêu cầu đã lưu trong quản trị.' : ($notified ? 'Đã chuyển cho hệ thống gửi email. Chưa xác nhận giao thư.' : 'Gửi email thất bại. Yêu cầu đã lưu trong quản trị.'));
    tb_quote_reply(true, 'Đã tiếp nhận yêu cầu. Chúng tôi sẽ liên hệ để trao đổi và xác nhận thông tin với bạn.');
}
add_action('admin_post_nopriv_tb_submit_quote', 'tb_handle_quote');
add_action('admin_post_tb_submit_quote', 'tb_handle_quote');
add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) { return; }
    if (!get_theme_mod('tb_email')) { echo '<div class="notice notice-warning"><p><strong>Nhận yêu cầu báo giá:</strong> yêu cầu được lưu tại menu Yêu cầu báo giá. Để nhận thông báo email, vào <a href="' . esc_url(admin_url('customize.php')) . '">Giao diện → Tùy biến → Công ty & nội dung trang chủ</a>, điền email nhận và cấu hình SMTP trên hosting.</p></div>'; }
});
// WordPress privacy tools also cover the private quote records.
add_filter('wp_privacy_personal_data_exporters', function ($exporters) {
    $exporters['tb-quotes'] = ['exporter_friendly_name' => 'Yêu cầu báo giá', 'callback' => function ($email, $page = 1) {
        $query = new WP_Query(['post_type' => 'tb_quote', 'post_status' => 'private', 'posts_per_page' => 50, 'paged' => $page, 'meta_key' => '_tb_email', 'meta_value' => sanitize_email($email), 'orderby' => 'ID', 'order' => 'ASC']);
        $data = [];
        foreach ($query->posts as $post) {
            $fields = [];
            foreach (['name' => 'Họ tên', 'phone' => 'Điện thoại', 'company' => 'Công ty', 'email' => 'Email', 'product' => 'Sản phẩm', 'quantity' => 'Số lượng', 'message' => 'Ghi chú', 'submitted' => 'Ngày gửi', 'consent' => 'Đồng ý liên hệ'] as $key => $label) { $fields[] = ['name' => $label, 'value' => get_post_meta($post->ID, '_tb_' . $key, true)]; }
            $data[] = ['group_id' => 'tb-quotes', 'group_label' => 'Yêu cầu báo giá', 'item_id' => 'quote-' . $post->ID, 'data' => $fields];
        }
        return ['data' => $data, 'done' => $page >= $query->max_num_pages];
    }]; return $exporters;
});
add_filter('wp_privacy_personal_data_erasers', function ($erasers) {
    $erasers['tb-quotes'] = ['eraser_friendly_name' => 'Yêu cầu báo giá', 'callback' => function ($email, $page = 1) {
        $ids = get_posts(['post_type' => 'tb_quote', 'post_status' => 'private', 'numberposts' => 50, 'meta_key' => '_tb_email', 'meta_value' => sanitize_email($email), 'fields' => 'ids']);
        $removed = false; $retained = false;
        foreach ($ids as $id) { if (wp_delete_post($id, true)) { $removed = true; } else { $retained = true; } }
        return ['items_removed' => $removed, 'items_retained' => $retained, 'messages' => $retained ? ['Có yêu cầu chưa xóa được, vui lòng kiểm tra thủ công.'] : [], 'done' => count($ids) < 50 || $retained];
    }]; return $erasers;
});
