<?php if (!defined('ABSPATH')) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<a class="skip-link" href="#main">Bỏ qua đến nội dung</a>
<div class="topbar"><div class="container"><span>Giải pháp đóng gói từ gỗ · Sản xuất theo yêu cầu</span><a href="<?php echo esc_url(home_url('/#bao-gia')); ?>">Cùng trao đổi nhu cầu của bạn <?php echo tb_icon('arrow'); ?></a></div></div>
<header class="site-header"><div class="container header-row">
<?php tb_brand(); ?>
<button class="menu-toggle" aria-expanded="false" aria-controls="site-navigation" aria-label="Mở menu"><?php echo tb_icon('menu'); ?></button>
<nav id="site-navigation" class="navigation" aria-label="Menu chính">
<?php if (has_nav_menu('primary')) { wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'nav-list']); } else { ?>
<ul class="nav-list"><li><a href="<?php echo esc_url(home_url('/')); ?>" <?php if (is_front_page()) echo 'aria-current="page"'; ?>>Trang chủ</a></li><li><a href="<?php echo esc_url(home_url('/#gioi-thieu')); ?>">Về chúng tôi</a></li><li><a href="<?php echo esc_url(home_url('/#san-pham')); ?>">Sản phẩm</a></li><li><a href="<?php echo esc_url(home_url('/#bai-viet')); ?>">Bài viết</a></li></ul>
<?php } ?>
<a class="button button-small" href="<?php echo esc_url(home_url('/#bao-gia')); ?>">Nhận báo giá <?php echo tb_icon('arrow'); ?></a>
</nav></div></header>
