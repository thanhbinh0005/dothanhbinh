<?php
if (!defined('ABSPATH')) { exit; }
add_action('admin_menu', function () {
    add_menu_page('Website Thanh Bình', 'Thanh Bình', 'manage_options', 'tb-website', 'tb_website_setup_screen', 'dashicons-store', 3);
});
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_tb-website') {
        wp_enqueue_style('tb-website-admin', plugins_url('../assets/website-admin.css', __FILE__), [], TB_WEBSITE_VERSION);
    }
});
add_filter('plugin_action_links_thanh-binh-orders/thanh-binh-orders.php', function ($links) {
    array_unshift($links, '<a href="' . esc_url(admin_url('admin.php?page=tb-website')) . '">Thiết lập website</a>');
    return $links;
});
add_action('admin_notices', function () {
    if (current_user_can('manage_options') && !get_option('tb_website_setup_version')) {
        echo '<div class="notice notice-info"><p><strong>Thanh Bình Website:</strong> cài giao diện, trang chủ, menu, sản phẩm và bài viết bằng nút Thiết lập website. <a href="' . esc_url(admin_url('admin.php?page=tb-website')) . '">Mở thiết lập website</a></p></div>';
    }
});
function tb_website_setup_screen() {
    if (!current_user_can('manage_options')) { return; }
    $ready = get_stylesheet() === 'thanh-binh' && get_option('tb_website_setup_version');
    $error = get_transient('tb_setup_error_' . get_current_user_id());
    if ($error) { delete_transient('tb_setup_error_' . get_current_user_id()); }
    ?>
    <div class="wrap tb-website-wrap">
    <div class="tb-website-heading"><span>THANH BÌNH · WEBSITE PALLET</span><h1>Website hoàn chỉnh, sẵn sàng để bạn chỉnh sửa.</h1><p>Giao diện, sản phẩm, bài viết và nhận yêu cầu báo giá trong một bộ cài.</p></div>
    <?php if ($error): ?><div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div><?php endif; ?>
    <?php if ($ready): ?><div class="notice notice-success"><p><strong>Trang chủ đã sẵn sàng.</strong> Bạn có thể xem website và chỉnh nội dung bên dưới.</p></div><?php endif; ?>
    <div class="tb-website-layout"><div class="tb-website-preview"><img src="<?php echo esc_url(plugins_url('../assets/website-preview.jpg', __FILE__)); ?>" alt="Xem trước giao diện website Thanh Bình"><p>Ảnh xem trước của giao diện. Ảnh sản phẩm hiện là minh họa; bạn có thể thay bằng ảnh thật trong Thư viện.</p></div>
    <div class="tb-website-panel"><h2><?php echo $ready ? 'Quản lý website của bạn' : 'Bắt đầu với website đầy đủ'; ?></h2>
    <ul><li>Giao diện tiếng Việt cho máy tính và điện thoại</li><li>Trang chủ, giới thiệu, liên hệ và trang bài viết</li><li>3 sản phẩm mẫu, gồm Pallet gỗ kê hàng</li><li>3 bài viết có nội dung sẵn để chỉnh sửa</li><li>Biểu mẫu báo giá và quản lý yêu cầu riêng tư</li></ul>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
    <input type="hidden" name="action" value="tb_setup_website"><?php wp_nonce_field('tb_setup_website', 'tb_setup_nonce'); ?>
    <p>Nút dưới sẽ cài hoặc cập nhật giao diện Thanh Bình, kích hoạt giao diện và tạo nội dung mẫu ở lần thiết lập đầu và bổ sung các trang còn thiếu. Nội dung đã sửa, thông tin liên hệ và yêu cầu báo giá được giữ lại. Nếu đã chỉnh trực tiếp mã của theme Thanh Bình, hãy sao lưu trước khi cập nhật giao diện.</p>
    <button type="submit" id="tb-setup-submit" class="button button-primary button-hero"><?php echo $ready ? 'Kiểm tra và hoàn thiện website' : 'Thiết lập website'; ?></button></form>
    <?php if ($ready): ?><p class="tb-website-actions"><a class="button button-secondary" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">Xem website</a><a class="button button-secondary" href="<?php echo esc_url(admin_url('customize.php')); ?>">Đổi logo & thông tin công ty</a></p><?php endif; ?>
    <div class="tb-website-links"><a href="<?php echo esc_url(admin_url('edit.php?post_type=tb_product')); ?>">Quản lý sản phẩm</a><a href="<?php echo esc_url(admin_url('edit.php')); ?>">Quản lý bài viết</a><a href="<?php echo esc_url(admin_url('edit.php?post_type=tb_quote')); ?>">Yêu cầu báo giá</a><a href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>">Chỉnh các trang</a></div>
    <p>Email, điện thoại, Zalo và ảnh thật được điền trong Tùy biến. Cần cấu hình SMTP nếu muốn nhận thông báo email.</p>
    </div></div></div>
    <?php
}
function tb_install_website_theme() {
    $theme = wp_get_theme('thanh-binh');
    if (!$theme->exists() || version_compare($theme->get('Version'), TB_WEBSITE_VERSION, '<')) {
        if (!current_user_can('install_themes')) { return new WP_Error('tb_theme_permission', 'Tài khoản cần quyền cài giao diện để hoàn tất website.'); }
        $package = dirname(__DIR__) . '/packages/thanh-binh-theme.zip';
        if (!is_file($package)) { return new WP_Error('tb_missing_theme', 'Gói giao diện chưa có trong bản cài này. Hãy tải gói Website hoàn chỉnh mới nhất, hoặc cài theme Thanh Bình bản ' . TB_WEBSITE_VERSION . ' trong mục Giao diện.'); }
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        $skin = new Automatic_Upgrader_Skin();
        $upgrader = new Theme_Upgrader($skin);
        $result = $upgrader->install($package, ['overwrite_package' => true]);
        if (is_wp_error($result)) { return $result; }
        if (!$result) { return new WP_Error('tb_theme_install', 'Chưa cài được giao diện. Kiểm tra quyền ghi thư mục wp-content/themes rồi thử lại.'); }
        wp_clean_themes_cache();
        $theme = wp_get_theme('thanh-binh');
    }
    if ($theme->errors()) { return $theme->errors(); }
    if ($theme->get('RequiresPHP') && version_compare(PHP_VERSION, $theme->get('RequiresPHP'), '<')) { return new WP_Error('tb_theme_php', 'Giao diện cần PHP ' . $theme->get('RequiresPHP') . ' trở lên.'); }
    return true;
}
function tb_ensure_site_page($slug, $title, $content) {
    $page = get_page_by_path($slug, OBJECT, 'page');
    if ($page) {
        if ($page->post_status !== 'publish') { return new WP_Error('tb_page_draft', 'Trang có đường dẫn ' . $slug . ' đang ở trạng thái chưa công khai. Hãy kiểm tra trang đó trước khi thiết lập lại.'); }
        return $page->ID;
    }
    return wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content], true);
}
function tb_configure_website() {
    $result = tb_install_website_theme();
    if (is_wp_error($result)) { return $result; }
    if (!current_user_can('switch_themes')) { return new WP_Error('tb_switch_theme', 'Tài khoản cần quyền kích hoạt giao diện.'); }
    if (!get_option('tb_website_previous_settings')) {
        add_option('tb_website_previous_settings', ['stylesheet' => get_stylesheet(), 'show_on_front' => get_option('show_on_front'), 'page_on_front' => get_option('page_on_front'), 'page_for_posts' => get_option('page_for_posts')], '', false);
    }
    if (get_stylesheet() !== 'thanh-binh') { switch_theme('thanh-binh'); }
    tb_register_content_types();
    $result = tb_create_starter_content();
    if (is_wp_error($result)) { return $result; }
    $result = tb_import_starter_media();
    if (is_wp_error($result)) { return $result; }
    $pages = [
        'trang-chu' => ['Trang chủ', ''],
        'tin-tuc' => ['Kiến thức & sản phẩm', ''],
        'gioi-thieu' => ['Giới thiệu Thanh Bình', '<!-- wp:paragraph --><p>Thanh Bình cung cấp giải pháp pallet gỗ và chân cục pallet theo nhu cầu đóng gói, lưu kho và vận chuyển của doanh nghiệp.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Trao đổi để chọn quy cách phù hợp</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Chúng tôi tiếp nhận thông tin về hàng hóa, số lượng, điều kiện sử dụng và giao nhận để tư vấn phương án. Vật liệu, quy cách, chi phí và lịch dự kiến được thống nhất trong báo giá riêng.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Để nhận tư vấn, hãy chia sẻ loại sản phẩm, quy cách, số lượng và thời gian cần nhận. Chúng tôi sẽ liên hệ để làm rõ nhu cầu và xác nhận phương án phù hợp.</p><!-- /wp:paragraph -->'],
        'lien-he' => ['Liên hệ & yêu cầu báo giá', '<!-- wp:paragraph --><p>Chia sẻ loại sản phẩm, số lượng và quy cách mong muốn. Chúng tôi sẽ liên hệ để trao đổi và xác nhận thông tin trước khi báo giá.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[thanh_binh_quote]<!-- /wp:shortcode -->'],
    ];
    $ids = [];
    foreach ($pages as $slug => $data) {
        $id = tb_ensure_site_page($slug, $data[0], $data[1]);
        if (is_wp_error($id)) { return $id; }
        $ids[$slug] = $id;
    }
    if (get_option('show_on_front') !== 'page' || get_post_status((int) get_option('page_on_front')) !== 'publish') {
        update_option('page_on_front', $ids['trang-chu']);
        update_option('show_on_front', 'page');
    }
    if (get_post_status((int) get_option('page_for_posts')) !== 'publish') { update_option('page_for_posts', $ids['tin-tuc']); }
    $locations = get_theme_mod('nav_menu_locations', []);
    if (empty($locations['primary']) || !wp_get_nav_menu_object($locations['primary'])) {
        $menu = wp_get_nav_menu_object('Thanh Bình — Menu chính');
        $menu_id = $menu ? $menu->term_id : wp_create_nav_menu('Thanh Bình — Menu chính');
        if (is_wp_error($menu_id)) { return $menu_id; }
        if (!wp_get_nav_menu_items($menu_id)) {
            $links = [['Trang chủ', home_url('/')], ['Giới thiệu', get_permalink($ids['gioi-thieu'])], ['Sản phẩm', home_url('/#san-pham')], ['Bài viết', get_permalink((int) get_option('page_for_posts'))], ['Liên hệ', get_permalink($ids['lien-he'])]];
            foreach ($links as $index => $link) {
                $item = wp_update_nav_menu_item($menu_id, 0, ['menu-item-title' => $link[0], 'menu-item-url' => $link[1], 'menu-item-status' => 'publish', 'menu-item-type' => 'custom', 'menu-item-position' => $index + 1]);
                if (is_wp_error($item)) { return $item; }
            }
        }
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
    global $wp_rewrite;
    if (!get_option('permalink_structure')) { $wp_rewrite->set_permalink_structure('/%postname%/'); }
    flush_rewrite_rules();
    update_option('tb_website_setup_version', TB_WEBSITE_VERSION);
    return true;
}
add_action('admin_post_tb_setup_website', function () {
    if (!current_user_can('manage_options')) { wp_die('Bạn không có quyền thiết lập website.', '', ['response' => 403]); }
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Hãy dùng nút Thiết lập website trong quản trị.', '', ['response' => 405]); }
    check_admin_referer('tb_setup_website', 'tb_setup_nonce');
    $result = tb_configure_website();
    if (is_wp_error($result)) { set_transient('tb_setup_error_' . get_current_user_id(), $result->get_error_message(), MINUTE_IN_SECONDS); }
    wp_safe_redirect(admin_url('admin.php?page=tb-website'), 303);
    exit;
});
function tb_import_starter_media() {
    $file = dirname(__DIR__) . '/assets/pallet-go-ke-hang.jpg';
    if (!is_file($file)) { return new WP_Error('tb_image_missing', 'Gói cài chưa có ảnh minh họa pallet. Hãy tải gói Website hoàn chỉnh mới nhất.'); }
    $id = (int) get_option('tb_starter_pallet_attachment');
    if (!$id || get_post_type($id) !== 'attachment' || get_post_status($id) === 'trash') {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $temp = wp_tempnam($file);
        if (!$temp || !copy($file, $temp)) { return new WP_Error('tb_image_copy', 'Chưa tạo được tệp ảnh tạm. Kiểm tra quyền ghi của WordPress.'); }
        $id = media_handle_sideload(['name' => 'pallet-go-ke-hang.jpg', 'tmp_name' => $temp], 0, 'Pallet gỗ kê hàng — ảnh minh họa');
        if (is_wp_error($id)) { @unlink($temp); return $id; }
        update_post_meta($id, '_wp_attachment_image_alt', 'Ảnh minh họa pallet gỗ kê hàng trong kho');
        update_option('tb_starter_pallet_attachment', $id);
    }
    foreach ([['tb_product', 'pallet-go-theo-quy-cach'], ['tb_product', 'giai-phap-pallet-rieng'], ['post', 'chon-pallet-go-phu-hop'], ['post', 'huong-dan-gui-yeu-cau-bao-gia']] as $item) {
        $post = get_page_by_path($item[1], OBJECT, $item[0]);
        if ($post && !get_post_thumbnail_id($post->ID)) { set_post_thumbnail($post->ID, $id); }
    }
    if (!get_theme_mod('tb_hero_image')) { set_theme_mod('tb_hero_image', $id); }
    return true;
}
