<?php
/**
 * 自定义文章字段注册
 *
 * @package LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'luomor_novel_register_meta' );
/**
 * 注册自定义 post meta 字段
 */
function luomor_novel_register_meta() {
	// 小说相关字段
	register_post_meta( 'novel', '_luomor_ai_provider', array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'string',
		'default'       => '',
		'auth_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
	) );

	register_post_meta( 'novel', '_luomor_ai_prompt', array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'string',
		'default'       => '',
		'auth_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
	) );

	register_post_meta( 'novel', '_luomor_cover_prompt', array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'string',
		'default'       => '',
		'auth_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
	) );

	register_post_meta( 'novel', '_luomor_word_count', array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'integer',
		'default'       => 0,
		'auth_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
	) );

	// 章节相关字段
	register_post_meta( 'chapter', '_luomor_novel_id', array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'integer',
		'default'       => 0,
		'auth_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
	) );

	register_post_meta( 'chapter', '_luomor_chapter_order', array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'integer',
		'default'       => 0,
		'auth_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
	) );

	register_post_meta( 'chapter', '_luomor_word_count', array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'integer',
		'default'       => 0,
		'auth_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
	) );

	register_post_meta( 'chapter', '_luomor_ai_provider', array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'string',
		'default'       => '',
		'auth_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
	) );

	register_post_meta( 'chapter', '_luomor_ai_prompt', array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'string',
		'default'       => '',
		'auth_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
	) );
}

// ============================================================
// 章节保存时更新字数统计和小说总字数
// ============================================================

add_action( 'save_post_chapter', 'luomor_novel_update_word_count', 10, 3 );
/**
 * 保存章节时更新字数
 */
function luomor_novel_update_word_count( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$content = $post->post_content;
	// 中文字符统计（去掉 HTML 标签后统计）
	$text      = wp_strip_all_tags( $content );
	$char_count = mb_strlen( preg_replace( '/\s+/', '', $text ), 'UTF-8' );

	update_post_meta( $post_id, '_luomor_word_count', $char_count );

	// 更新小说总字数
	$novel_id = get_post_meta( $post_id, '_luomor_novel_id', true );
	if ( $novel_id ) {
		luomor_novel_recalculate_novel_word_count( $novel_id );
	}
}

/**
 * 重新计算小说总字数
 */
function luomor_novel_recalculate_novel_word_count( $novel_id ) {
	$chapters = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_luomor_novel_id',
				'value' => $novel_id,
			),
		),
		'fields' => 'ids',
	) );

	$total = 0;
	foreach ( $chapters as $chapter_id ) {
		$total += (int) get_post_meta( $chapter_id, '_luomor_word_count', true );
	}

	update_post_meta( $novel_id, '_luomor_word_count', $total );
}

// ============================================================
// 章节关联验证
// ============================================================

add_action( 'rest_pre_insert_chapter', 'luomor_novel_validate_chapter_novel', 10, 2 );
/**
 * REST 创建章节时验证小说关联
 */
function luomor_novel_validate_chapter_novel( $prepared_post, $request ) {
	if ( ! empty( $request['_luomor_novel_id'] ) ) {
		$novel_id = absint( $request['_luomor_novel_id'] );
		if ( ! get_post( $novel_id ) || get_post_type( $novel_id ) !== 'novel' ) {
			return new WP_Error(
				'invalid_novel',
				__( '关联的小说不存在', 'luomor-novel' ),
				array( 'status' => 400 )
			);
		}
	}
	return $prepared_post;
}
