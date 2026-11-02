<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if (!function_exists('tb_create_starter_content')) { WP_CLI::error('Hãy kích hoạt plugin Thanh Bình Website trước.'); }
$result = tb_create_starter_content();
if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
WP_CLI::success('Nội dung khởi tạo đã sẵn sàng; giữ nguyên các chỉnh sửa hiện có.');
