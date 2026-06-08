<?php
/**
 * 区块模板回退文件
 *
 * 当 FSE 模板不可用时使用此文件
 *
 * @package LuomorNovel
 */

get_header();
?>

<main id="primary" class="site-main">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header>
				<div class="entry-content">
					<?php
					the_content();
					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( '页面：', 'luomor-novel' ),
						'after'  => '</div>',
					) );
					?>
				</div>
			</article>
			<?php
			// 允许评论
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		<?php endwhile; ?>
		<?php the_posts_navigation(); ?>
	<?php else : ?>
		<p><?php esc_html_e( '暂无内容', 'luomor-novel' ); ?></p>
	<?php endif; ?>
</main>

<?php
get_footer();
