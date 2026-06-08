<?php
/**
 * REST API 端点注册
 *
 * @package LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', 'luomor_novel_register_rest_routes' );
/**
 * 注册所有自定义 REST 端点
 */
function luomor_novel_register_rest_routes() {
	// ============================================================
	// 小说 CRUD
	// ============================================================

	// 删除小说（含所有章节）
	register_rest_route( 'luomor/v1', '/novels/(?P<id>\d+)', array(
		'methods'             => 'DELETE',
		'callback'            => 'luomor_rest_delete_novel',
		'permission_callback' => function() {
			return current_user_can( 'delete_posts' );
		},
		'args'                => array(
			'id' => array(
				'required'          => true,
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param > 0;
				},
			),
		),
	) );

	// 获取小说详情（含章节列表）
	register_rest_route( 'luomor/v1', '/novels/(?P<id>\d+)', array(
		'methods'             => 'GET',
		'callback'            => 'luomor_rest_get_novel_detail',
		'permission_callback' => '__return_true',
		'args'                => array(
			'id' => array(
				'required'          => true,
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param > 0;
				},
			),
		),
	) );

	// 获取小说列表
	register_rest_route( 'luomor/v1', '/novels', array(
		'methods'             => 'GET',
		'callback'            => 'luomor_rest_list_novels',
		'permission_callback' => '__return_true',
		'args'                => array(
			'genre'    => array( 'type' => 'string' ),
			'tag'      => array( 'type' => 'string' ),
			'status'   => array( 'type' => 'string' ),
			'author'   => array( 'type' => 'integer' ),
			'search'   => array( 'type' => 'string' ),
			'order'    => array( 'default' => 'desc' ),
			'orderby'  => array( 'default' => 'date' ),
			'page'     => array( 'default' => 1, 'type' => 'integer' ),
			'per_page' => array( 'default' => 20, 'type' => 'integer' ),
		),
	) );

	// 创建小说
	register_rest_route( 'luomor/v1', '/novels', array(
		'methods'             => 'POST',
		'callback'            => 'luomor_rest_create_novel',
		'permission_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
		'args'                => array(
			'title'    => array( 'required' => true, 'type' => 'string' ),
			'content'  => array( 'default' => '', 'type' => 'string' ),
			'excerpt'  => array( 'default' => '', 'type' => 'string' ),
			'genres'   => array( 'default' => array(), 'type' => 'array' ),
			'tags'     => array( 'default' => array(), 'type' => 'array' ),
			'status'   => array( 'default' => 'ongoing', 'type' => 'string' ),
		),
	) );

	// 更新小说
	register_rest_route( 'luomor/v1', '/novels/(?P<id>\d+)', array(
		'methods'             => 'PUT',
		'callback'            => 'luomor_rest_update_novel',
		'permission_callback' => function( $request ) {
			$novel_id = $request['id'];
			return current_user_can( 'edit_post', $novel_id );
		},
		'args'                => array(
			'id'       => array( 'required' => true, 'type' => 'integer' ),
			'title'    => array( 'type' => 'string' ),
			'content'  => array( 'type' => 'string' ),
			'excerpt'  => array( 'type' => 'string' ),
			'genres'   => array( 'type' => 'array' ),
			'tags'     => array( 'type' => 'array' ),
			'status'   => array( 'type' => 'string' ),
		),
	) );

	// ============================================================
	// 章节 CRUD
	// ============================================================

	// 获取小说的章节列表
	register_rest_route( 'luomor/v1', '/novels/(?P<novel_id>\d+)/chapters', array(
		'methods'             => 'GET',
		'callback'            => 'luomor_rest_get_novel_chapters',
		'permission_callback' => '__return_true',
		'args'                => array(
			'novel_id' => array(
				'required'          => true,
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param > 0;
				},
			),
			'order'    => array( 'default' => 'asc' ),
			'page'     => array( 'default' => 1, 'type' => 'integer' ),
			'per_page' => array( 'default' => 50, 'type' => 'integer' ),
		),
	) );

	// 创建章节
	register_rest_route( 'luomor/v1', '/novels/(?P<novel_id>\d+)/chapters', array(
		'methods'             => 'POST',
		'callback'            => 'luomor_rest_create_chapter',
		'permission_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
		'args'                => array(
			'novel_id' => array( 'required' => true, 'type' => 'integer' ),
			'title'    => array( 'required' => true, 'type' => 'string' ),
			'content'  => array( 'default' => '', 'type' => 'string' ),
			'order'    => array( 'default' => 0, 'type' => 'integer' ),
		),
	) );

	// 重排章节顺序
	register_rest_route( 'luomor/v1', '/novels/(?P<novel_id>\d+)/chapters/reorder', array(
		'methods'             => 'POST',
		'callback'            => 'luomor_rest_reorder_chapters',
		'permission_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
		'args'                => array(
			'novel_id' => array( 'required' => true, 'type' => 'integer' ),
			'order'    => array( 'required' => true, 'type' => 'array' ),
		),
	) );

	// 获取单章详情
	register_rest_route( 'luomor/v1', '/chapters/(?P<id>\d+)', array(
		'methods'             => 'GET',
		'callback'            => 'luomor_rest_get_chapter',
		'permission_callback' => '__return_true',
		'args'                => array(
			'id' => array(
				'required'          => true,
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param > 0;
				},
			),
		),
	) );

	// 更新章节
	register_rest_route( 'luomor/v1', '/chapters/(?P<id>\d+)', array(
		'methods'             => 'PUT',
		'callback'            => 'luomor_rest_update_chapter',
		'permission_callback' => function( $request ) {
			$chapter_id = $request['id'];
			return current_user_can( 'edit_post', $chapter_id );
		},
		'args'                => array(
			'id'      => array( 'required' => true, 'type' => 'integer' ),
			'title'   => array( 'type' => 'string' ),
			'content' => array( 'type' => 'string' ),
			'order'   => array( 'type' => 'integer' ),
		),
	) );

	// 删除章节
	register_rest_route( 'luomor/v1', '/chapters/(?P<id>\d+)', array(
		'methods'             => 'DELETE',
		'callback'            => 'luomor_rest_delete_chapter',
		'permission_callback' => function( $request ) {
			$chapter_id = $request['id'];
			return current_user_can( 'delete_post', $chapter_id );
		},
		'args'                => array(
			'id' => array(
				'required'          => true,
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param > 0;
				},
			),
		),
	) );

	// ============================================================
	// 收藏
	// ============================================================

	// 收藏/取消收藏
	register_rest_route( 'luomor/v1', '/bookmarks/(?P<novel_id>\d+)', array(
		'methods'             => 'POST',
		'callback'            => 'luomor_rest_toggle_bookmark',
		'permission_callback' => function() {
			return is_user_logged_in();
		},
		'args' => array(
			'novel_id' => array(
				'required'          => true,
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param > 0;
				},
			),
		),
	) );

	// 获取收藏列表
	register_rest_route( 'luomor/v1', '/bookmarks', array(
		'methods'             => 'GET',
		'callback'            => 'luomor_rest_get_bookmarks',
		'permission_callback' => function() {
			return is_user_logged_in();
		},
	) );

	// 检查是否已收藏
	register_rest_route( 'luomor/v1', '/bookmarks/(?P<novel_id>\d+)/check', array(
		'methods'             => 'GET',
		'callback'            => 'luomor_rest_check_bookmark',
		'permission_callback' => function() {
			return is_user_logged_in();
		},
		'args' => array(
			'novel_id' => array(
				'required'          => true,
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param > 0;
				},
			),
		),
	) );

	// ============================================================
	// 阅读进度
	// ============================================================

	// 保存阅读进度
	register_rest_route( 'luomor/v1', '/reading-progress', array(
		'methods'             => 'POST',
		'callback'            => 'luomor_rest_save_reading_progress',
		'permission_callback' => function() {
			return is_user_logged_in();
		},
		'args' => array(
			'novel_id'   => array( 'required' => true, 'type' => 'integer' ),
			'chapter_id' => array( 'required' => true, 'type' => 'integer' ),
		),
	) );

	// 获取阅读进度
	register_rest_route( 'luomor/v1', '/reading-progress/(?P<novel_id>\d+)', array(
		'methods'             => 'GET',
		'callback'            => 'luomor_rest_get_reading_progress',
		'permission_callback' => function() {
			return is_user_logged_in();
		},
		'args' => array(
			'novel_id' => array(
				'required'          => true,
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param > 0;
				},
			),
		),
	) );

	// ============================================================
	// 搜索
	// ============================================================

	register_rest_route( 'luomor/v1', '/search', array(
		'methods'             => 'GET',
		'callback'            => 'luomor_rest_search',
		'permission_callback' => '__return_true',
		'args'                => array(
			'q'        => array( 'required' => true, 'type' => 'string' ),
			'type'     => array( 'default' => 'all' ),
			'genre'    => array( 'type' => 'string' ),
			'tag'      => array( 'type' => 'string' ),
			'status'   => array( 'type' => 'string' ),
			'page'     => array( 'default' => 1, 'type' => 'integer' ),
			'per_page' => array( 'default' => 20, 'type' => 'integer' ),
		),
	) );

	// ============================================================
	// AI
	// ============================================================

	// AI 内容生成
	register_rest_route( 'luomor/v1', '/ai/generate', array(
		'methods'             => 'POST',
		'callback'            => 'luomor_rest_ai_generate',
		'permission_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
		'args'                => array(
			'prompt'        => array( 'required' => true, 'type' => 'string' ),
			'system_prompt' => array( 'default' => '', 'type' => 'string' ),
			'provider'      => array( 'default' => '', 'type' => 'string' ),
			'messages'      => array( 'default' => array(), 'type' => 'array' ),
		),
	) );

	// 获取可用 AI 提供商
	register_rest_route( 'luomor/v1', '/ai/providers', array(
		'methods'             => 'GET',
		'callback'            => 'luomor_rest_ai_providers',
		'permission_callback' => '__return_true',
	) );

	// 测试 AI 连接
	register_rest_route( 'luomor/v1', '/ai/test/(?P<provider>[a-z]+)', array(
		'methods'             => 'GET',
		'callback'            => 'luomor_rest_ai_test_connection',
		'permission_callback' => function() {
			return current_user_can( 'manage_options' );
		},
		'args'                => array(
			'provider' => array(
				'required' => true,
				'type'     => 'string',
			),
		),
	) );
}

// ============================================================
// 回调函数
// ============================================================

/**
 * 删除小说及其所有章节
 */
function luomor_rest_delete_novel( $request ) {
	$novel_id = (int) $request['id'];
	$post     = get_post( $novel_id );

	if ( ! $post || 'novel' !== $post->post_type ) {
		return new WP_Error( 'not_found', __( '小说不存在', 'luomor-novel' ), array( 'status' => 404 ) );
	}

	// 删除所有章节
	$chapters = luomor_novel_get_chapters_for_novel( $novel_id );
	foreach ( $chapters as $chapter ) {
		wp_delete_post( $chapter->ID, true );
	}

	// 删除小说
	$result = wp_delete_post( $novel_id, true );

	if ( $result ) {
		return new WP_REST_Response( array(
			'success' => true,
			'message' => __( '小说及其章节已删除', 'luomor-novel' ),
		), 200 );
	}

	return new WP_Error( 'delete_failed', __( '删除失败', 'luomor-novel' ), array( 'status' => 500 ) );
}

/**
 * 获取小说详情
 */
function luomor_rest_get_novel_detail( $request ) {
	$novel_id = (int) $request['id'];
	$post     = get_post( $novel_id );

	if ( ! $post || 'novel' !== $post->post_type ) {
		return new WP_Error( 'not_found', __( '小说不存在', 'luomor-novel' ), array( 'status' => 404 ) );
	}

	$chapters = luomor_novel_get_chapters_for_novel( $novel_id );
	$chapter_list = array();
	foreach ( $chapters as $chapter ) {
		$chapter_list[] = array(
			'id'         => $chapter->ID,
			'title'      => $chapter->post_title,
			'link'       => get_permalink( $chapter->ID ),
			'order'      => luomor_novel_get_chapter_order( $chapter->ID ),
			'word_count' => (int) get_post_meta( $chapter->ID, '_luomor_word_count', true ),
			'date'       => $chapter->post_date,
		);
	}

	return new WP_REST_Response( array(
		'id'            => $novel_id,
		'title'         => $post->post_title,
		'content'       => apply_filters( 'the_content', $post->post_content ),
		'excerpt'       => $post->post_excerpt,
		'cover_url'     => get_the_post_thumbnail_url( $novel_id, 'novel-cover' ),
		'author'        => get_the_author_meta( 'display_name', $post->post_author ),
		'author_id'     => $post->post_author,
		'genres'        => luomor_novel_get_genres( $novel_id ),
		'tags'          => luomor_novel_get_tags( $novel_id ),
		'status'        => luomor_novel_get_status( $novel_id ),
		'word_count'    => luomor_novel_get_word_count( $novel_id ),
		'chapter_count' => count( $chapter_list ),
		'bookmark_count'=> luomor_novel_get_bookmark_count( $novel_id ),
		'is_bookmarked' => luomor_novel_is_bookmarked( $novel_id ),
		'reading_progress' => luomor_novel_get_reading_progress( $novel_id ),
		'chapters'      => $chapter_list,
		'date'          => $post->post_date,
		'modified'      => $post->post_modified,
	), 200 );
}

/**
 * 获取小说列表
 */
function luomor_rest_list_novels( $request ) {
	$args = array(
		'post_type'      => 'novel',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $request['per_page'],
		'paged'          => (int) $request['page'],
		'order'          => sanitize_text_field( $request['order'] ),
		'orderby'        => sanitize_text_field( $request['orderby'] ),
	);

	// 搜索
	if ( ! empty( $request['search'] ) ) {
		$args['s'] = sanitize_text_field( $request['search'] );
	}

	// 分类过滤
	$tax_query = array();
	if ( ! empty( $request['genre'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'novel_genre',
			'field'    => 'slug',
			'terms'    => sanitize_text_field( $request['genre'] ),
		);
	}
	if ( ! empty( $request['status'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'novel_status',
			'field'    => 'slug',
			'terms'    => sanitize_text_field( $request['status'] ),
		);
	}
	if ( ! empty( $request['tag'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'novel_tag',
			'field'    => 'slug',
			'terms'    => sanitize_text_field( $request['tag'] ),
		);
	}
	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query;
	}

	// 作者过滤
	if ( ! empty( $request['author'] ) ) {
		$args['author'] = (int) $request['author'];
	}

	$query = new WP_Query( $args );
	$novels = array();

	foreach ( $query->posts as $post ) {
		$novels[] = array(
			'id'            => $post->ID,
			'title'         => $post->post_title,
			'excerpt'       => $post->post_excerpt,
			'cover_url'     => get_the_post_thumbnail_url( $post->ID, 'novel-cover-thumb' ),
			'author'        => get_the_author_meta( 'display_name', $post->post_author ),
			'genres'        => array_map( function( $t ) { return array( 'name' => $t->name, 'slug' => $t->slug ); }, luomor_novel_get_genres( $post->ID ) ),
			'status'        => luomor_novel_get_status( $post->ID ),
			'word_count'    => luomor_novel_get_word_count( $post->ID ),
			'chapter_count' => luomor_novel_get_total_chapters( $post->ID ),
			'link'          => get_permalink( $post->ID ),
			'date'          => $post->post_date,
		);
	}

	return new WP_REST_Response( array(
		'novels' => $novels,
		'total'  => (int) $query->found_posts,
		'pages'  => (int) $query->max_num_pages,
	), 200 );
}

/**
 * 创建小说
 */
function luomor_rest_create_novel( $request ) {
	$post_data = array(
		'post_title'   => sanitize_text_field( $request['title'] ),
		'post_content' => wp_kses_post( $request['content'] ),
		'post_excerpt' => sanitize_text_field( $request['excerpt'] ),
		'post_type'    => 'novel',
		'post_status'  => 'publish',
	);

	$post_id = wp_insert_post( $post_data, true );
	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	// 设置分类
	if ( ! empty( $request['genres'] ) ) {
		wp_set_post_terms( $post_id, array_map( 'sanitize_text_field', $request['genres'] ), 'novel_genre' );
	}
	if ( ! empty( $request['tags'] ) ) {
		wp_set_post_terms( $post_id, array_map( 'sanitize_text_field', $request['tags'] ), 'novel_tag' );
	}
	if ( ! empty( $request['status'] ) ) {
		wp_set_post_terms( $post_id, sanitize_text_field( $request['status'] ), 'novel_status' );
	}

	return new WP_REST_Response( array(
		'success' => true,
		'id'      => $post_id,
		'link'    => get_permalink( $post_id ),
	), 201 );
}

/**
 * 更新小说
 */
function luomor_rest_update_novel( $request ) {
	$novel_id = (int) $request['id'];
	$post     = get_post( $novel_id );

	if ( ! $post || 'novel' !== $post->post_type ) {
		return new WP_Error( 'not_found', __( '小说不存在', 'luomor-novel' ), array( 'status' => 404 ) );
	}

	$update_data = array( 'ID' => $novel_id );

	if ( ! empty( $request['title'] ) ) {
		$update_data['post_title'] = sanitize_text_field( $request['title'] );
	}
	if ( isset( $request['content'] ) ) {
		$update_data['post_content'] = wp_kses_post( $request['content'] );
	}
	if ( isset( $request['excerpt'] ) ) {
		$update_data['post_excerpt'] = sanitize_text_field( $request['excerpt'] );
	}

	wp_update_post( $update_data );

	if ( ! empty( $request['genres'] ) ) {
		wp_set_post_terms( $novel_id, array_map( 'sanitize_text_field', $request['genres'] ), 'novel_genre' );
	}
	if ( ! empty( $request['tags'] ) ) {
		wp_set_post_terms( $novel_id, array_map( 'sanitize_text_field', $request['tags'] ), 'novel_tag' );
	}
	if ( ! empty( $request['status'] ) ) {
		wp_set_post_terms( $novel_id, sanitize_text_field( $request['status'] ), 'novel_status' );
	}

	return new WP_REST_Response( array(
		'success' => true,
		'id'      => $novel_id,
	), 200 );
}

/**
 * 获取小说的章节列表
 */
function luomor_rest_get_novel_chapters( $request ) {
	$novel_id = (int) $request['novel_id'];
	$order    = sanitize_text_field( $request['order'] );
	$page     = (int) $request['page'];
	$per_page = (int) $request['per_page'];

	$chapters = luomor_novel_get_chapters_for_novel( $novel_id, $order );
	$total    = count( $chapters );

	// 分页
	$offset    = ( $page - 1 ) * $per_page;
	$paged     = array_slice( $chapters, $offset, $per_page );

	$result = array();
	foreach ( $paged as $chapter ) {
		$result[] = array(
			'id'         => $chapter->ID,
			'title'      => $chapter->post_title,
			'content'    => apply_filters( 'the_content', $chapter->post_content ),
			'link'       => get_permalink( $chapter->ID ),
			'order'      => luomor_novel_get_chapter_order( $chapter->ID ),
			'word_count' => (int) get_post_meta( $chapter->ID, '_luomor_word_count', true ),
			'date'       => $chapter->post_date,
		);
	}

	return new WP_REST_Response( array(
		'chapters' => $result,
		'total'    => $total,
		'pages'    => (int) ceil( $total / $per_page ),
	), 200 );
}

/**
 * 创建章节
 */
function luomor_rest_create_chapter( $request ) {
	$novel_id = (int) $request['novel_id'];
	$novel    = get_post( $novel_id );

	if ( ! $novel || 'novel' !== $novel->post_type ) {
		return new WP_Error( 'invalid_novel', __( '小说不存在', 'luomor-novel' ), array( 'status' => 404 ) );
	}

	// 计算排序号
	$max_order = 0;
	$existing  = luomor_novel_get_chapters_for_novel( $novel_id );
	if ( ! empty( $existing ) ) {
		$max_order = luomor_novel_get_chapter_order( end( $existing )->ID );
	}

	$post_data = array(
		'post_title'   => sanitize_text_field( $request['title'] ),
		'post_content' => wp_kses_post( $request['content'] ),
		'post_type'    => 'chapter',
		'post_status'  => 'publish',
		'menu_order'   => $max_order + 1,
	);

	$post_id = wp_insert_post( $post_data, true );
	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	// 设置关联
	update_post_meta( $post_id, '_luomor_novel_id', $novel_id );
	update_post_meta( $post_id, '_luomor_chapter_order', $max_order + 1 );

	// 清除缓存
	delete_transient( 'luomor_chapters_' . $novel_id . '_asc' );
	delete_transient( 'luomor_chapters_' . $novel_id . '_desc' );
	delete_transient( 'luomor_chapter_count_' . $novel_id );

	return new WP_REST_Response( array(
		'success' => true,
		'id'      => $post_id,
		'link'    => get_permalink( $post_id ),
	), 201 );
}

/**
 * 重排章节
 */
function luomor_rest_reorder_chapters( $request ) {
	$novel_id = (int) $request['novel_id'];
	$order    = $request['order'];

	if ( ! is_array( $order ) ) {
		return new WP_Error( 'invalid_order', __( '排序数据格式错误', 'luomor-novel' ), array( 'status' => 400 ) );
	}

	foreach ( $order as $item ) {
		if ( ! isset( $item['id'] ) || ! isset( $item['position'] ) ) {
			continue;
		}
		$chapter_id = (int) $item['id'];
		$position   = (int) $item['position'];

		// 验证章节属于该小说
		$actual_novel_id = get_post_meta( $chapter_id, '_luomor_novel_id', true );
		if ( (int) $actual_novel_id !== $novel_id ) {
			continue;
		}

		update_post_meta( $chapter_id, '_luomor_chapter_order', $position );
	}

	// 清除缓存
	delete_transient( 'luomor_chapters_' . $novel_id . '_asc' );
	delete_transient( 'luomor_chapters_' . $novel_id . '_desc' );

	return new WP_REST_Response( array(
		'success' => true,
		'message' => __( '章节排序已保存', 'luomor-novel' ),
	), 200 );
}

/**
 * 获取单章详情
 */
function luomor_rest_get_chapter( $request ) {
	$chapter_id = (int) $request['id'];
	$post       = get_post( $chapter_id );

	if ( ! $post || 'chapter' !== $post->post_type ) {
		return new WP_Error( 'not_found', __( '章节不存在', 'luomor-novel' ), array( 'status' => 404 ) );
	}

	$novel_id = luomor_novel_get_novel_id_from_chapter( $chapter_id );
	$novel    = $novel_id ? get_post( $novel_id ) : null;

	return new WP_REST_Response( array(
		'id'         => $chapter_id,
		'title'      => $post->post_title,
		'content'    => apply_filters( 'the_content', $post->post_content ),
		'novel_id'   => $novel_id,
		'novel_title'=> $novel ? $novel->post_title : '',
		'novel_link' => $novel ? get_permalink( $novel_id ) : '',
		'order'      => luomor_novel_get_chapter_order( $chapter_id ),
		'word_count' => (int) get_post_meta( $chapter_id, '_luomor_word_count', true ),
		'date'       => $post->post_date,
	), 200 );
}

/**
 * 更新章节
 */
function luomor_rest_update_chapter( $request ) {
	$chapter_id = (int) $request['id'];
	$post       = get_post( $chapter_id );

	if ( ! $post || 'chapter' !== $post->post_type ) {
		return new WP_Error( 'not_found', __( '章节不存在', 'luomor-novel' ), array( 'status' => 404 ) );
	}

	$update_data = array( 'ID' => $chapter_id );

	if ( ! empty( $request['title'] ) ) {
		$update_data['post_title'] = sanitize_text_field( $request['title'] );
	}
	if ( isset( $request['content'] ) ) {
		$update_data['post_content'] = wp_kses_post( $request['content'] );
	}

	wp_update_post( $update_data );

	if ( isset( $request['order'] ) ) {
		update_post_meta( $chapter_id, '_luomor_chapter_order', (int) $request['order'] );

		$novel_id = get_post_meta( $chapter_id, '_luomor_novel_id', true );
		if ( $novel_id ) {
			delete_transient( 'luomor_chapters_' . $novel_id . '_asc' );
			delete_transient( 'luomor_chapters_' . $novel_id . '_desc' );
		}
	}

	return new WP_REST_Response( array(
		'success' => true,
		'id'      => $chapter_id,
	), 200 );
}

/**
 * 删除章节
 */
function luomor_rest_delete_chapter( $request ) {
	$chapter_id = (int) $request['id'];
	$post       = get_post( $chapter_id );

	if ( ! $post || 'chapter' !== $post->post_type ) {
		return new WP_Error( 'not_found', __( '章节不存在', 'luomor-novel' ), array( 'status' => 404 ) );
	}

	$novel_id = get_post_meta( $chapter_id, '_luomor_novel_id', true );
	$result   = wp_delete_post( $chapter_id, true );

	if ( $result ) {
		if ( $novel_id ) {
			delete_transient( 'luomor_chapters_' . $novel_id . '_asc' );
			delete_transient( 'luomor_chapters_' . $novel_id . '_desc' );
			delete_transient( 'luomor_chapter_count_' . $novel_id );
		}

		return new WP_REST_Response( array(
			'success' => true,
			'message' => __( '章节已删除', 'luomor-novel' ),
		), 200 );
	}

	return new WP_Error( 'delete_failed', __( '删除失败', 'luomor-novel' ), array( 'status' => 500 ) );
}

/**
 * 切换收藏
 */
function luomor_rest_toggle_bookmark( $request ) {
	$novel_id = (int) $request['novel_id'];
	$result   = luomor_novel_toggle_bookmark( $novel_id );

	if ( 'error' === $result['action'] ) {
		return new WP_Error( 'bookmark_error', $result['error'], array( 'status' => 401 ) );
	}

	return new WP_REST_Response( $result, 200 );
}

/**
 * 获取收藏列表
 */
function luomor_rest_get_bookmarks( $request ) {
	$novels = luomor_novel_get_bookmarks();
	$result = array();

	foreach ( $novels as $novel ) {
		$result[] = array(
			'id'        => $novel->ID,
			'title'     => $novel->post_title,
			'cover_url' => get_the_post_thumbnail_url( $novel->ID, 'novel-cover-thumb' ),
			'link'      => get_permalink( $novel->ID ),
		);
	}

	return new WP_REST_Response( array(
		'bookmarks' => $result,
		'total'     => luomor_novel_get_bookmark_total(),
	), 200 );
}

/**
 * 检查是否已收藏
 */
function luomor_rest_check_bookmark( $request ) {
	$novel_id = (int) $request['novel_id'];
	return new WP_REST_Response( array(
		'is_bookmarked' => luomor_novel_is_bookmarked( $novel_id ),
	), 200 );
}

/**
 * 保存阅读进度
 */
function luomor_rest_save_reading_progress( $request ) {
	$novel_id   = (int) $request['novel_id'];
	$chapter_id = (int) $request['chapter_id'];

	$result = luomor_novel_save_reading_progress( $novel_id, $chapter_id );

	return new WP_REST_Response( array(
		'success'   => $result,
		'novel_id'  => $novel_id,
		'chapter_id'=> $chapter_id,
	), 200 );
}

/**
 * 获取阅读进度
 */
function luomor_rest_get_reading_progress( $request ) {
	$novel_id = (int) $request['novel_id'];
	$chapter_id = luomor_novel_get_reading_progress( $novel_id );

	return new WP_REST_Response( array(
		'novel_id'   => $novel_id,
		'chapter_id' => $chapter_id,
	), 200 );
}

/**
 * 搜索
 */
function luomor_rest_search( $request ) {
	$query  = sanitize_text_field( $request['q'] );
	$type   = sanitize_text_field( $request['type'] );
	$page   = (int) $request['page'];
	$per_page = (int) $request['per_page'];

	$results = array(
		'novels'   => array(),
		'chapters' => array(),
	);

	// 搜索小说
	if ( 'all' === $type || 'novel' === $type ) {
		$novel_args = array(
			'post_type'      => 'novel',
			'post_status'    => 'publish',
			's'              => $query,
			'posts_per_page' => $per_page,
			'paged'          => $page,
		);

		// 分类过滤
		$tax_query = array();
		if ( ! empty( $request['genre'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'novel_genre',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $request['genre'] ),
			);
		}
		if ( ! empty( $request['status'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'novel_status',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $request['status'] ),
			);
		}
		if ( ! empty( $tax_query ) ) {
			$novel_args['tax_query'] = $tax_query;
		}

		$novel_query = new WP_Query( $novel_args );
		foreach ( $novel_query->posts as $post ) {
			$results['novels'][] = array(
				'id'        => $post->ID,
				'title'     => $post->post_title,
				'excerpt'   => $post->post_excerpt,
				'cover_url' => get_the_post_thumbnail_url( $post->ID, 'novel-cover-thumb' ),
				'link'      => get_permalink( $post->ID ),
			);
		}
	}

	// 搜索章节
	if ( 'all' === $type || 'chapter' === $type ) {
		$chapter_args = array(
			'post_type'      => 'chapter',
			'post_status'    => 'publish',
			's'              => $query,
			'posts_per_page' => $per_page,
			'paged'          => $page,
		);

		$chapter_query = new WP_Query( $chapter_args );
		foreach ( $chapter_query->posts as $post ) {
			$novel_id = luomor_novel_get_novel_id_from_chapter( $post->ID );
			$results['chapters'][] = array(
				'id'         => $post->ID,
				'title'      => $post->post_title,
				'novel_id'   => $novel_id,
				'novel_title'=> $novel_id ? get_the_title( $novel_id ) : '',
				'link'       => get_permalink( $post->ID ),
			);
		}
	}

	return new WP_REST_Response( $results, 200 );
}

/**
 * AI 内容生成
 */
function luomor_rest_ai_generate( $request ) {
	$prompt        = sanitize_textarea_field( $request['prompt'] );
	$system_prompt = sanitize_textarea_field( $request['system_prompt'] );
	$provider      = sanitize_text_field( $request['provider'] );
	$messages      = $request['messages'] ?? array();

	$result = Luomor_AI_Service::generate( $prompt, $system_prompt, array(
		'provider' => $provider,
		'messages' => $messages,
	) );

	if ( ! $result['success'] ) {
		return new WP_Error( 'ai_generation_failed', $result['error'], array( 'status' => 500 ) );
	}

	return new WP_REST_Response( array(
		'success' => true,
		'content' => $result['content'],
		'error'   => '',
	), 200 );
}

/**
 * 获取可用 AI 提供商
 */
function luomor_rest_ai_providers( $request ) {
	return new WP_REST_Response( Luomor_AI_Service::get_available_providers(), 200 );
}

/**
 * 测试 AI 连接
 */
function luomor_rest_ai_test_connection( $request ) {
	$provider = sanitize_text_field( $request['provider'] );
	$result   = Luomor_AI_Service::test_provider( $provider );

	if ( ! $result['success'] ) {
		return new WP_REST_Response( $result, 200 );
	}

	return new WP_REST_Response( $result, 200 );
}
