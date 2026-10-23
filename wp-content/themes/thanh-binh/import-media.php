<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$key=getenv('TB_MEDIA_KEY'); $file=getenv('TB_MEDIA_FILE');
if (!in_array($key,['logo','icon','blocks','pallet'],true) || !$file || !is_file($file)) WP_CLI::error('Thiếu tệp ảnh hợp lệ.');
require_once ABSPATH.'wp-admin/includes/file.php'; require_once ABSPATH.'wp-admin/includes/media.php'; require_once ABSPATH.'wp-admin/includes/image.php';
$hash=hash_file('sha256',$file); $old=get_option('tb_imported_'.$key);
if ($old && ($old['hash']??'')===$hash && get_post($old['id'])) { WP_CLI::success('Giữ nguyên ảnh đã nhập.'); return; }
$labels=['logo'=>'Logo Thanh Bình Hưng Yên','icon'=>'Biểu tượng TB trên pallet','blocks'=>'Chân cục pallet — ảnh do công ty cung cấp','pallet'=>'Pallet gỗ — ảnh do công ty cung cấp'];
$temp=wp_tempnam($file); copy($file,$temp);
$id=media_handle_sideload(['name'=>basename($file),'tmp_name'=>$temp],0,$labels[$key]);
if (is_wp_error($id)) { @unlink($temp); WP_CLI::error($id->get_error_message()); }
update_post_meta($id,'_wp_attachment_image_alt',$labels[$key]);
if ($key==='logo') set_theme_mod('custom_logo',$id);
if ($key==='icon') update_option('site_icon',$id);
if ($key==='pallet') { set_theme_mod('tb_hero_image',$id); set_theme_mod('tb_about_image',$id); }
if (in_array($key,['blocks','pallet'],true)) {
 foreach (get_posts(['post_type'=>'tb_product','numberposts'=>-1]) as $p) { if (($key==='blocks')===($p->post_name==='chan-cuc-pallet')) set_post_thumbnail($p->ID,$id); }
 foreach (get_posts(['post_type'=>'post','numberposts'=>-1,'meta_key'=>'_tb_media_group','meta_value'=>$key]) as $p) set_post_thumbnail($p->ID,$id);
}
update_option('tb_imported_'.$key,['hash'=>$hash,'id'=>$id]); WP_CLI::success('Đã nhập '.$labels[$key]);
