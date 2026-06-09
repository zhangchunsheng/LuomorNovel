<?php
/**
 * 自定义区块注册（PHP 服务端渲染）
 *
 * @package LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'luomor_novel_register_custom_blocks' );
/**
 * 注册自定义区块
 */
function luomor_novel_register_custom_blocks() {
	// 章节目录区块
	register_block_type( 'luomor/novel-toc', array(
		'render_callback' => 'luomor_novel_render_novel_toc',
	) );

	// 章节导航区块
	register_block_type( 'luomor/chapter-nav', array(
		'render_callback' => 'luomor_novel_render_chapter_nav',
	) );

	// 阅读进度条区块
	register_block_type( 'luomor/progress-bar', array(
		'render_callback' => 'luomor_novel_render_progress_bar',
	) );
}

/**
 * 渲染小说目录区块
 */
function luomor_novel_render_novel_toc() {
	if ( ! is_singular( 'novel' ) ) {
		return '';
	}

	$novel_id = get_the_ID();
	$chapters = luomor_novel_get_chapters_for_novel( $novel_id );

	if ( empty( $chapters ) ) {
		return '<p>' . esc_html__( '暂无章节', 'luomor-novel' ) . '</p>';
	}

	$reading_progress = luomor_novel_get_reading_progress( $novel_id );

	$html = '<ul class="luomor-toc-list">';
	foreach ( $chapters as $chapter ) {
		$is_read = ( $reading_progress && (int) $reading_progress === (int) $chapter->ID );
		$class   = $is_read ? ' class="is-read"' : '';

		$html .= sprintf(
			'<li%s><a href="%s"><span class="chapter-title">%s</span><span class="chapter-number">%s</span></a></li>',
			$class,
			esc_url( get_permalink( $chapter->ID ) ),
			esc_html( $chapter->post_title ),
			esc_html( luomor_novel_format_word_count( (int) get_post_meta( $chapter->ID, '_luomor_word_count', true ) ) )
		);
	}
	$html .= '</ul>';

	return $html;
}

/**
 * 渲染章节导航区块
 */
function luomor_novel_render_chapter_nav() {
	if ( ! is_singular( 'chapter' ) ) {
		return '';
	}

	$chapter_id = get_the_ID();
	$prev       = luomor_novel_get_adjacent_chapter( $chapter_id, 'prev' );
	$next       = luomor_novel_get_adjacent_chapter( $chapter_id, 'next' );
	$novel_id   = luomor_novel_get_novel_id_from_chapter( $chapter_id );

	$html  = '<nav class="wp-block-group luomor-chapter-nav" aria-label="' . esc_attr__( '章节导航', 'luomor-novel' ) . '" style="padding-top:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md);">';
	$html .= '<div class="wp-block-group luomor-chapter-prev" style="display:flex;flex-direction:column;">';

	if ( $prev ) {
		$html .= sprintf(
			'<a href="%s" class="luomor-nav-link luomor-prev-link" aria-label="%s"><span class="luomor-nav-label">%s</span><span class="luomor-nav-title">%s</span></a>',
			esc_url( get_permalink( $prev->ID ) ),
			esc_attr__( '上一章', 'luomor-novel' ),
			esc_html__( '上一章', 'luomor-novel' ),
			esc_html( $prev->post_title )
		);
	}

	$html .= '</div>';
	$html .= '<div class="wp-block-group luomor-chapter-toc" style="display:flex;justify-content:center;">';

	if ( $novel_id ) {
		$novel = get_post( $novel_id );
		if ( $novel ) {
			$html .= sprintf(
				'<a href="%s" class="luomor-toc-link">%s</a>',
				esc_url( get_permalink( $novel_id ) ),
				esc_html__( '目录', 'luomor-novel' )
			);
		}
	}

	$html .= '</div>';
	$html .= '<div class="wp-block-group luomor-chapter-next" style="display:flex;flex-direction:column;justify-content:flex-end;">';

	if ( $next ) {
		$html .= sprintf(
			'<a href="%s" class="luomor-nav-link luomor-next-link" aria-label="%s"><span class="luomor-nav-label">%s</span><span class="luomor-nav-title">%s</span></a>',
			esc_url( get_permalink( $next->ID ) ),
			esc_attr__( '下一章', 'luomor-novel' ),
			esc_html__( '下一章', 'luomor-novel' ),
			esc_html( $next->post_title )
		);
	}

	$html .= '</div></nav>';

	return $html;
}

/**
 * 渲染阅读进度条区块
 */
function luomor_novel_render_progress_bar() {
	if ( ! is_singular( 'chapter' ) ) {
		return '';
	}

	return '<div class="luomor-progress-bar" style="position:fixed;top:0;left:0;height:3px;background:var(--wp--preset--color--primary,#8b5cf6);z-index:9999;transition:width 0.1s;width:0%;"></div>';
}
