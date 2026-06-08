<?php
/**
 * 阅读进度追踪
 *
 * @package LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 保存阅读进度
 *
 * @param int $novel_id   小说 ID
 * @param int $chapter_id 章节 ID
 * @param int $user_id    用户 ID
 * @return bool
 */
function luomor_novel_save_reading_progress( $novel_id, $chapter_id, $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return false;
	}

	$novel_id   = (int) $novel_id;
	$chapter_id = (int) $chapter_id;

	// 验证章节属于该小说
	$actual_novel_id = luomor_novel_get_novel_id_from_chapter( $chapter_id );
	if ( $actual_novel_id && $actual_novel_id !== $novel_id ) {
		return false;
	}

	update_user_meta( $user_id, '_luomor_last_read_' . $novel_id, $chapter_id );

	return true;
}

/**
 * 获取阅读进度
 *
 * @param int $novel_id 小说 ID
 * @param int $user_id  用户 ID
 * @return int|false 上次阅读的章节 ID 或 false
 */
function luomor_novel_get_reading_progress( $novel_id, $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return false;
	}

	$chapter_id = get_user_meta( $user_id, '_luomor_last_read_' . (int) $novel_id, true );
	return $chapter_id ? (int) $chapter_id : false;
}

/**
 * 获取用户所有阅读进度
 *
 * @param int $user_id 用户 ID
 * @return array ['novel_id' => chapter_id, ...]
 */
function luomor_novel_get_all_reading_progress( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return array();
	}

	global $wpdb;
	$results = $wpdb->get_results( $wpdb->prepare(
		"SELECT meta_key, meta_value FROM {$wpdb->usermeta}
		 WHERE user_id = %d AND meta_key LIKE %s",
		$user_id,
		'_luomor_last_read_%'
	), ARRAY_A );

	$progress = array();
	foreach ( $results as $row ) {
		$novel_id = (int) str_replace( '_luomor_last_read_', '', $row['meta_key'] );
		$progress[ $novel_id ] = (int) $row['meta_value'];
	}

	return $progress;
}

/**
 * 清除阅读进度
 *
 * @param int $novel_id 小说 ID
 * @param int $user_id  用户 ID
 * @return bool
 */
function luomor_novel_clear_reading_progress( $novel_id, $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return false;
	}

	return delete_user_meta( $user_id, '_luomor_last_read_' . (int) $novel_id );
}
