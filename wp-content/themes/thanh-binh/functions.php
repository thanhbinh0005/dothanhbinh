<?php
if (!defined('ABSPATH')) { exit; }
function tb_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['height' => 100, 'width' => 300, 'flex-width' => true, 'flex-height' => true]);
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('editor-styles');
    add_editor_style('assets/site.css');
    register_nav_menus(['primary' => 'Menu chính']);
}
add_action('after_setup_theme', 'tb_setup');
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('tb-site', get_theme_file_uri('assets/site.css'), [], filemtime(get_theme_file_path('assets/site.css')));
    wp_enqueue_script('tb-site', get_theme_file_uri('assets/site.js'), [], filemtime(get_theme_file_path('assets/site.js')), true);
});
function tb_defaults() {
    return [
        'company' => ['Tên công ty / thương hiệu', 'Thanh Bình', 'text'],
        'tagline' => ['Dòng mô tả thương hiệu', 'WOOD & PALLET', 'text'],
        'phone' => ['Số điện thoại (để trống để ẩn)', '', 'text'],
        'zalo' => ['Số Zalo (chỉ nhập số, để trống để ẩn)', '', 'text'],
        'email' => ['Email liên hệ và nhận yêu cầu báo giá', '', 'email'],
        'address' => ['Địa chỉ xưởng (để trống để ẩn)', '', 'text'],
        'hero_label' => ['Nhãn đầu trang', 'GIẢI PHÁP PALLET CHO DOANH NGHIỆP', 'text'],
        'hero_title' => ['Tiêu đề đầu trang', "Vững từng kiện hàng.\nBền từng hành trình.", 'textarea'],
        'hero_description' => ['Mô tả đầu trang', 'Pallet gỗ và giải pháp đóng gói theo nhu cầu. Đồng hành cùng doanh nghiệp từ khâu sản xuất đến lưu kho và vận chuyển.', 'textarea'],
        'about_title' => ['Tiêu đề giới thiệu', 'Từ thớ gỗ mộc, đến nền tảng vững chắc.', 'text'],
        'about_text' => ['Nội dung giới thiệu', 'Chúng tôi tin rằng một hành trình hàng hóa an toàn bắt đầu từ những điều cơ bản: vật liệu phù hợp, kết cấu chắc chắn và sự chỉn chu trong từng chi tiết. Thanh Bình hướng đến các giải pháp pallet gỗ thiết thực cho nhu cầu của doanh nghiệp.', 'textarea'],
        'footer_text' => ['Mô tả chân trang', 'Giải pháp pallet gỗ và đóng gói cho hành trình hàng hóa của bạn.', 'textarea'],
    ];
}
function tb_value($key) {
    $defaults = tb_defaults();
    return get_theme_mod('tb_' . $key, $defaults[$key][1] ?? '');
}
function tb_customize($c) {
    $c->add_section('tb_business', ['title' => 'Công ty & nội dung trang chủ', 'priority' => 25, 'description' => 'Sửa thông tin thật trước khi đưa website lên tên miền. Email ở đây cũng nhận thông báo yêu cầu báo giá.']);
    foreach (tb_defaults() as $key => $setting) {
        $sanitize = $setting[2] === 'email' ? 'sanitize_email' : ($setting[2] === 'textarea' ? 'sanitize_textarea_field' : 'sanitize_text_field');
        $c->add_setting('tb_' . $key, ['default' => $setting[1], 'sanitize_callback' => $sanitize]);
        $c->add_control('tb_' . $key, ['section' => 'tb_business', 'label' => $setting[0], 'type' => $setting[2]]);
    }
    foreach (['hero' => 'Ảnh đầu trang', 'about' => 'Ảnh giới thiệu xưởng'] as $key => $label) {
        $c->add_setting('tb_' . $key . '_image', ['sanitize_callback' => 'absint']);
        $c->add_control(new WP_Customize_Media_Control($c, 'tb_' . $key . '_image', ['label' => $label, 'section' => 'tb_business', 'mime_type' => 'image']));
    }
    $c->add_setting('tb_accent', ['default' => '#eeb869', 'sanitize_callback' => 'sanitize_hex_color']);
    $c->add_control(new WP_Customize_Color_Control($c, 'tb_accent', ['label' => 'Màu nhấn', 'section' => 'tb_business']));
}
add_action('customize_register', 'tb_customize');
add_action('wp_head', function () {
    $accent = sanitize_hex_color(get_theme_mod('tb_accent', '#eeb869')) ?: '#eeb869';
    echo '<style>:root{--accent:' . esc_html($accent) . '}</style>';
});
function tb_icon($name, $class = '') {
    $paths = [
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'box' => '<path d="m12 3 9 5-9 5-9-5 9-5ZM3 8v9l9 5 9-5V8M12 13v9M7 5.8l9 5"/>',
        'truck' => '<path d="M3 5h11v12H3zM14 9h4l3 4v4h-7"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
        'leaf' => '<path d="M20 3C9 2 2 8 6 15s16 5 14-12ZM4 21 16 9"/>',
        'ruler' => '<path d="m3 15 12-12 6 6L9 21l-6-6ZM8 10l3 3m1-7 3 3m-11 5 3 3"/>',
        'phone' => '<path d="m7 3 3 5-3 2c1 3 4 6 7 7l2-3 5 3c0 3-2 5-5 4C8 19 3 14 2 7c0-3 2-4 5-4Z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/>',
        'pin' => '<path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'wood' => '<path d="M3 6h18v4H3zM3 14h18v4H3zM6 10v4m12-4v4M6 18v3m12-3v3M7 6V3m10 3V3"/>',
    ];
    return '<svg class="icon ' . esc_attr($class) . '" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['arrow']) . '</svg>';
}
function tb_brand() {
    if (has_custom_logo()) { the_custom_logo(); return; }
    echo '<a class="brand" href="' . esc_url(home_url('/')) . '" aria-label="' . esc_attr(tb_value('company')) . ' — Trang chủ"><span class="brand-mark">' . tb_icon('wood') . '</span><span><strong>' . esc_html(tb_value('company')) . '</strong><small>' . esc_html(tb_value('tagline')) . '</small></span></a>';
}
function tb_product_meta($id, $key) { return get_post_meta($id, '_tb_' . $key, true); }
function tb_product_picture($id, $size = 'large') {
    if (has_post_thumbnail($id)) { echo get_the_post_thumbnail($id, $size, ['loading' => 'lazy']); }
    else { echo '<img loading="lazy" src="' . esc_url(get_theme_file_uri(get_post_field('post_name', $id) === 'chan-cuc-pallet' ? 'assets/blocks.svg' : 'assets/pallet.svg')) . '" alt="Minh họa sản phẩm; liên hệ để nhận ảnh thực tế">'; }
}
add_action('customize_register', function ($c) {
    $c->add_section('tb_sections', ['title' => 'Bật / tắt các phần trang chủ', 'priority' => 27]);
    foreach (['about' => 'Giới thiệu công ty', 'process' => 'Quy trình làm việc', 'blog' => 'Bài viết mới nhất'] as $key => $label) {
        $c->add_setting('tb_show_' . $key, ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
        $c->add_control('tb_show_' . $key, ['section' => 'tb_sections', 'label' => $label, 'type' => 'checkbox']);
    }
});
