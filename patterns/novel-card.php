<?php
/**
 * 小说卡片区块模式
 *
 * 在 query loop 中使用的小说卡片显示
 *
 * @package LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="luomor-novel-card-pattern">
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="luomor-card-cover">
			<?php the_post_thumbnail( 'novel-cover-thumb', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="luomor-card-body">
		<h3 class="luomor-card-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php
		$genres = luomor_novel_get_genres( get_the_ID() );
		if ( ! empty( $genres ) ) :
		?>
			<div class="luomor-card-genres">
				<?php foreach ( $genres as $genre ) : ?>
					<a href="<?php echo esc_url( get_term_link( $genre ) ); ?>" class="luomor-genre-tag">
						<?php echo esc_html( $genre->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( has_excerpt() ) : ?>
			<p class="luomor-card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
		<?php endif; ?>

		<div class="luomor-card-meta">
			<span class="luomor-card-chapters">
				<?php echo esc_html( sprintf(
					/* translators: %d: number of chapters */
					_n( '%d 章', '%d 章', luomor_novel_get_total_chapters( get_the_ID() ), 'luomor-novel' ),
					luomor_novel_get_total_chapters( get_the_ID() )
				) ); ?>
			</span>
			<span class="luomor-card-words">
				<?php echo esc_html( luomor_novel_format_word_count( luomor_novel_get_word_count( get_the_ID() ) ) ); ?>
			</span>
		</div>
	</div>
</div>
