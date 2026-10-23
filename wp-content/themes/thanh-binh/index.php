<?php get_header(); ?>
<main id="main" class="section page-main"><div class="container narrow">
<?php if (have_posts()): while (have_posts()): the_post(); ?><article <?php post_class(); ?>><p class="eyebrow"><?php echo esc_html(tb_value('company')); ?></p><h1><?php the_title(); ?></h1><div class="entry-content"><?php the_content(); ?></div></article><?php endwhile; the_posts_pagination(); else: ?><h1>Chưa có nội dung</h1><p>Vui lòng quay lại sau hoặc gửi yêu cầu tư vấn.</p><?php endif; ?>
</div></main><?php get_footer(); ?>
