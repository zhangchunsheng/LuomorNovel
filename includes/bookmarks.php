<?php
/**
 * 收藏功能
 *
 * @package LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 切换小说收藏状态
 *
 * @param int $novel_id 小说 ID
 * @param int $user_id  用户 ID
 * @return array ['action' => 'added'|'removed', 'count' => int]
 */
function luomor_novel_toggle_bookmark( $novel_id, $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return array(
			'action' => 'error',
			'count'  => 0,
			'error'  => __( '请先登录', 'luomor-novel' ),
		);
	}

	$bookmarks = get_user_meta( $user_id, '_luomor_bookmarks', true );
	if ( ! is_array( $bookmarks ) ) {
		$bookmarks = array();
	}

	if ( in_array( $novel_id, $bookmarks, true ) ) {
		$bookmarks = array_values( array_diff( $bookmarks, array( $novel_id ) ) );
		$action    = 'removed';
	} else {
		$bookmarks[] = $novel_id;
		$action      = 'added';
	}

	update_user_meta( $user_id, '_luomor_bookmarks', $bookmarks );

	// 清除收藏数缓存
	delete_transient( 'luomor_bookmark_count_' . $novel_id );

	return array(
		'action' => $action,
		'count'  => count( $bookmarks ),
	);
}

/**
 * 检查用户是否已收藏某小说
 *
 * @param int $novel_id 小说 ID
 * @param int $user_id  用户 ID
 * @return bool
 */
function luomor_novel_is_bookmarked( $novel_id, $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return false;
	}

	$bookmarks = get_user_meta( $user_id, '_luomor_bookmarks', true );
	return is_array( $bookmarks ) && in_array( $novel_id, $bookmarks, true );
}

/**
 * 获取用户的收藏列表
 *
 * @param int   $user_id  用户 ID
 * @param int   $page     页码
 * @param int   $per_page 每页数量
 * @return WP_Post[]
 */
function luomor_novel_get_bookmarks( $user_id = 0, $page = 1, $per_page = 20 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return array();
	}

	$bookmarks = get_user_meta( $user_id, '_luomor_bookmarks', true );
	if ( ! is_array( $bookmarks ) || empty( $bookmarks ) ) {
		return array();
	}

	$novels = get_posts( array(
		'post_type'      => 'novel',
		'post__in'       => array_map( 'intval', $bookmarks ),
		'orderby'        => 'post__in',
		'posts_per_page' => $per_page,
		'paged'          => $page,
	) );

	return $novels;
}

/**
 * 获取用户收藏总数
 *
 * @param int $user_id 用户 ID
 * @return int
 */
function luomor_novel_get_bookmark_total( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return 0;
	}

	$bookmarks = get_user_meta( $user_id, '_luomor_bookmarks', true );
	return is_array( $bookmarks ) ? count( $bookmarks ) : 0;
}
