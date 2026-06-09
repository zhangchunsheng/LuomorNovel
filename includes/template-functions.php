<?php
/**
 * 模板辅助函数
 *
 * @package LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 从章节 ID 获取小说 ID
 *
 * @param int $chapter_id 章节 ID
 * @return int|false 小说 ID 或 false
 */
function luomor_novel_get_novel_id_from_chapter( $chapter_id ) {
	$novel_id = get_post_meta( $chapter_id, '_luomor_novel_id', true );
	return $novel_id ? (int) $novel_id : false;
}

/**
 * 获取小说的章节列表（带缓存）
 *
 * @param int $novel_id 小说 ID
 * @param string $order 排序方向 asc|desc
 * @return array 章节数组
 */
function luomor_novel_get_chapters_for_novel( $novel_id, $order = 'asc' ) {
	$cache_key = 'luomor_chapters_' . $novel_id . '_' . $order;
	$chapters  = get_transient( $cache_key );

	if ( false !== $chapters ) {
		return $chapters;
	}

	$query = new WP_Query( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_luomor_novel_id',
				'value' => (int) $novel_id,
			),
		),
		'meta_key' => '_luomor_chapter_order',
		'orderby'  => 'meta_value_num',
		'order'    => strtoupper( $order ) === 'DESC' ? 'DESC' : 'ASC',
	) );

	$chapters = $query->posts;
	set_transient( $cache_key, $chapters, HOUR_IN_SECONDS );

	return $chapters;
}

/**
 * 获取章节排序号
 *
 * @param int $chapter_id 章节 ID
 * @return int 排序号
 */
function luomor_novel_get_chapter_order( $chapter_id ) {
	return (int) get_post_meta( $chapter_id, '_luomor_chapter_order', true );
}

/**
 * 获取相邻章节
 *
 * @param int    $current_chapter_id 当前章节 ID
 * @param string $direction          'prev' 或 'next'
 * @return WP_Post|false 相邻章节或 false
 */
function luomor_novel_get_adjacent_chapter( $current_chapter_id, $direction = 'next' ) {
	$novel_id = luomor_novel_get_novel_id_from_chapter( $current_chapter_id );
	if ( ! $novel_id ) {
		return false;
	}

	$chapters = luomor_novel_get_chapters_for_novel( $novel_id );
	if ( empty( $chapters ) ) {
		return false;
	}

	$found = false;
	foreach ( $chapters as $i => $chapter ) {
		if ( $chapter->ID === $current_chapter_id ) {
			$found = $i;
			break;
		}
	}

	if ( $found === false ) {
		return false;
	}

	if ( 'next' === $direction ) {
		return isset( $chapters[ $found + 1 ] ) ? $chapters[ $found + 1 ] : false;
	}

	return $found > 0 ? $chapters[ $found - 1 ] : false;
}

/**
 * 获取小说章节总数
 *
 * @param int $novel_id 小说 ID
 * @return int 章节数
 */
function luomor_novel_get_total_chapters( $novel_id ) {
	$cache_key = 'luomor_chapter_count_' . $novel_id;
	$count = get_transient( $cache_key );

	if ( false === $count ) {
		global $wpdb;
		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_luomor_novel_id' AND meta_value = %d",
			$novel_id
		) );
		set_transient( $cache_key, $count, HOUR_IN_SECONDS );
	}

	return $count;
}

/**
 * 获取小说的分类列表
 *
 * @param int $novel_id 小说 ID
 * @return array 分类术语数组
 */
function luomor_novel_get_genres( $novel_id ) {
	$terms = wp_get_object_terms( $novel_id, 'novel_genre', array( 'fields' => 'all' ) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * 获取小说的标签列表
 *
 * @param int $novel_id 小说 ID
 * @return array 标签术语数组
 */
function luomor_novel_get_tags( $novel_id ) {
	$terms = wp_get_object_terms( $novel_id, 'novel_tag', array( 'fields' => 'all' ) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * 获取小说状态
 *
 * @param int $novel_id 小说 ID
 * @return string 状态 slug
 */
function luomor_novel_get_status( $novel_id ) {
	$terms = wp_get_object_terms( $novel_id, 'novel_status', array( 'fields' => 'slugs' ) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return 'ongoing';
	}
	return $terms[0];
}

/**
 * 获取小说字数
 *
 * @param int $novel_id 小说 ID
 * @return int 总字数
 */
function luomor_novel_get_word_count( $novel_id ) {
	return (int) get_post_meta( $novel_id, '_luomor_word_count', true );
}

/**
 * 格式化字数显示
 *
 * @param int $count 字数
 * @return string 格式化后的字符串
 */
function luomor_novel_format_word_count( $count ) {
	if ( $count >= 10000 ) {
		return number_format_i18n( $count / 10000, 1 ) . __( '万字', 'luomor-novel' );
	}
	if ( $count >= 1000 ) {
		return number_format_i18n( $count / 1000, 1 ) . __( '千字', 'luomor-novel' );
	}
	return number_format_i18n( $count ) . __( '字', 'luomor-novel' );
}

/**
 * 获取小说收藏数
 *
 * @param int $novel_id 小说 ID
 * @return int 收藏数
 */
function luomor_novel_get_bookmark_count( $novel_id ) {
	$cache_key = 'luomor_bookmark_count_' . $novel_id;
	$count = get_transient( $cache_key );

	if ( false === $count ) {
		global $wpdb;
		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = '_luomor_bookmarks' AND FIND_IN_SET(%d, REPLACE(REPLACE(meta_value, '[\"', ''), '\"]', ''))",
			$novel_id
		) );
		// Fallback: count via serialized meta
		$count = 0;
		$users = get_users( array(
			'meta_query' => array(
				array(
					'key'     => '_luomor_bookmarks',
					'value'   => '"' . $novel_id . '"',
					'compare' => 'LIKE',
				),
			),
			'fields' => 'ID',
		) );
		$count = count( $users );

		set_transient( $cache_key, $count, 5 * MINUTE_IN_SECONDS );
	}

	return $count;
}

// ============================================================
// 缓存失效钩子
// ============================================================

add_action( 'save_post_chapter', 'luomor_novel_invalidate_chapter_cache', 10, 2 );
/**
 * 章节保存时清除缓存
 */
function luomor_novel_invalidate_chapter_cache( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$novel_id = get_post_meta( $post_id, '_luomor_novel_id', true );
	if ( $novel_id ) {
		delete_transient( 'luomor_chapters_' . $novel_id . '_asc' );
		delete_transient( 'luomor_chapters_' . $novel_id . '_desc' );
		delete_transient( 'luomor_chapter_count_' . $novel_id );
	}
}

add_action( 'delete_post_chapter', 'luomor_novel_invalidate_on_delete' );
/**
 * 章节删除时清除缓存
 */
function luomor_novel_invalidate_on_delete( $post_id ) {
	$novel_id = get_post_meta( $post_id, '_luomor_novel_id', true );
	if ( $novel_id ) {
		delete_transient( 'luomor_chapters_' . $novel_id . '_asc' );
		delete_transient( 'luomor_chapters_' . $novel_id . '_desc' );
		delete_transient( 'luomor_chapter_count_' . $novel_id );
		delete_transient( 'luomor_bookmark_count_' . $novel_id );
	}
}
