<?php
/**
 * 自定义文章类型和分类法注册
 *
 * @package LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ============================================================
// 自定义文章类型：小说 (novel)
// ============================================================

add_action( 'init', 'luomor_novel_register_novel_cpt' );
/**
 * 注册小说文章类型
 */
function luomor_novel_register_novel_cpt() {
	$labels = array(
		'name'               => _x( '小说', 'post type general name', 'luomor-novel' ),
		'singular_name'      => _x( '小说', 'post type singular name', 'luomor-novel' ),
		'menu_name'          => _x( '小说管理', 'admin menu', 'luomor-novel' ),
		'name_admin_bar'     => _x( '小说', 'add new on admin bar', 'luomor-novel' ),
		'add_new'            => _x( '新建小说', 'novel', 'luomor-novel' ),
		'add_new_item'       => __( '新建小说', 'luomor-novel' ),
		'new_item'           => __( '新小说', 'luomor-novel' ),
		'edit_item'          => __( '编辑小说', 'luomor-novel' ),
		'view_item'          => __( '查看小说', 'luomor-novel' ),
		'all_items'          => __( '所有小说', 'luomor-novel' ),
		'search_items'       => __( '搜索小说', 'luomor-novel' ),
		'parent_item_colon'  => __( '父级小说：', 'luomor-novel' ),
		'not_found'          => __( '未找到小说', 'luomor-novel' ),
		'not_found_in_trash' => __( '回收站中未找到小说', 'luomor-novel' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'novel' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-book-alt',
		'show_in_rest'       => true,
		'rest_base'          => 'novels',
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'comments' ),
	);

	register_post_type( 'novel', $args );
}

// ============================================================
// 自定义文章类型：章节 (chapter)
// ============================================================

add_action( 'init', 'luomor_novel_register_chapter_cpt' );
/**
 * 注册章节文章类型
 */
function luomor_novel_register_chapter_cpt() {
	$labels = array(
		'name'               => _x( '章节', 'post type general name', 'luomor-novel' ),
		'singular_name'      => _x( '章节', 'post type singular name', 'luomor-novel' ),
		'menu_name'          => _x( '章节管理', 'admin menu', 'luomor-novel' ),
		'name_admin_bar'     => _x( '章节', 'add new on admin bar', 'luomor-novel' ),
		'add_new'            => _x( '新建章节', 'chapter', 'luomor-novel' ),
		'add_new_item'       => __( '新建章节', 'luomor-novel' ),
		'new_item'           => __( '新章节', 'luomor-novel' ),
		'edit_item'          => __( '编辑章节', 'luomor-novel' ),
		'view_item'          => __( '查看章节', 'luomor-novel' ),
		'all_items'          => __( '所有章节', 'luomor-novel' ),
		'search_items'       => __( '搜索章节', 'luomor-novel' ),
		'parent_item_colon'  => __( '父级章节：', 'luomor-novel' ),
		'not_found'          => __( '未找到章节', 'luomor-novel' ),
		'not_found_in_trash' => __( '回收站中未找到章节', 'luomor-novel' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => false, // 在小说编辑中管理章节
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'novel/%novel_slug%/chapter' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 6,
		'menu_icon'          => 'dashicons-media-document',
		'show_in_rest'       => true,
		'rest_base'          => 'chapters',
		'supports'           => array( 'title', 'editor', 'author', 'custom-fields', 'revisions', 'comments' ),
	);

	register_post_type( 'chapter', $args );
}

// ============================================================
// 自定义分类法：小说类型 (novel_genre)
// ============================================================

add_action( 'init', 'luomor_novel_register_genre_taxonomy' );
/**
 * 注册小说类型分类法
 */
function luomor_novel_register_genre_taxonomy() {
	$labels = array(
		'name'              => _x( '小说类型', 'taxonomy general name', 'luomor-novel' ),
		'singular_name'     => _x( '类型', 'taxonomy singular name', 'luomor-novel' ),
		'search_items'      => __( '搜索类型', 'luomor-novel' ),
		'all_items'         => __( '所有类型', 'luomor-novel' ),
		'parent_item'       => __( '父级类型', 'luomor-novel' ),
		'parent_item_colon' => __( '父级类型：', 'luomor-novel' ),
		'edit_item'         => __( '编辑类型', 'luomor-novel' ),
		'update_item'       => __( '更新类型', 'luomor-novel' ),
		'add_new_item'      => __( '添加新类型', 'luomor-novel' ),
		'new_item_name'     => __( '新类型名称', 'luomor-novel' ),
		'menu_name'         => __( '小说类型', 'luomor-novel' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'genre' ),
		'show_in_rest'      => true,
		'rest_base'         => 'novel-genres',
	);

	register_taxonomy( 'novel_genre', array( 'novel' ), $args );
}

// ============================================================
// 自定义分类法：标签 (novel_tag)
// ============================================================

add_action( 'init', 'luomor_novel_register_tag_taxonomy' );
/**
 * 注册小说标签分类法
 */
function luomor_novel_register_tag_taxonomy() {
	$labels = array(
		'name'              => _x( '标签', 'taxonomy general name', 'luomor-novel' ),
		'singular_name'     => _x( '标签', 'taxonomy singular name', 'luomor-novel' ),
		'search_items'      => __( '搜索标签', 'luomor-novel' ),
		'all_items'         => __( '所有标签', 'luomor-novel' ),
		'edit_item'         => __( '编辑标签', 'luomor-novel' ),
		'update_item'       => __( '更新标签', 'luomor-novel' ),
		'add_new_item'      => __( '添加新标签', 'luomor-novel' ),
		'new_item_name'     => __( '新标签名称', 'luomor-novel' ),
		'menu_name'         => __( '标签', 'luomor-novel' ),
	);

	$args = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'novel-tag' ),
		'show_in_rest'      => true,
		'rest_base'         => 'novel-tags',
	);

	register_taxonomy( 'novel_tag', array( 'novel' ), $args );
}

// ============================================================
// 自定义分类法：状态 (novel_status)
// ============================================================

add_action( 'init', 'luomor_novel_register_status_taxonomy' );
/**
 * 注册小说状态分类法
 */
function luomor_novel_register_status_taxonomy() {
	$labels = array(
		'name'              => _x( '状态', 'taxonomy general name', 'luomor-novel' ),
		'singular_name'     => _x( '状态', 'taxonomy singular name', 'luomor-novel' ),
		'search_items'      => __( '搜索状态', 'luomor-novel' ),
		'all_items'         => __( '所有状态', 'luomor-novel' ),
		'edit_item'         => __( '编辑状态', 'luomor-novel' ),
		'update_item'       => __( '更新状态', 'luomor-novel' ),
		'add_new_item'      => __( '添加新状态', 'luomor-novel' ),
		'new_item_name'     => __( '新状态名称', 'luomor-novel' ),
		'menu_name'         => __( '状态', 'luomor-novel' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'novel-status' ),
		'show_in_rest'      => true,
		'rest_base'         => 'novel-statuses',
	);

	register_taxonomy( 'novel_status', array( 'novel' ), $args );
}

// ============================================================
// 章节 URL 重写（支持 /novel/%novel_slug%/chapter/%postname%/）
// ============================================================

add_filter( 'post_type_link', 'luomor_novel_chapter_permalink', 10, 2 );
/**
 * 生成章节永久链接
 */
function luomor_novel_chapter_permalink( $permalink, $post ) {
	if ( $post->post_type !== 'chapter' ) {
		return $permalink;
	}

	$novel_id = get_post_meta( $post->ID, '_luomor_novel_id', true );
	if ( ! $novel_id ) {
		return $permalink;
	}

	$novel = get_post( $novel_id );
	if ( ! $novel ) {
		return $permalink;
	}

	$novel_slug = $novel->post_name;
	$chapter_slug = $post->post_name;

	return str_replace(
		array( '%novel_slug%', '%postname%' ),
		array( $novel_slug, $chapter_slug ),
		$permalink
	);
}

add_action( 'init', 'luomor_novel_chapter_rewrite_rules' );
/**
 * 添加章节重写规则
 */
function luomor_novel_chapter_rewrite_rules() {
	add_rewrite_rule(
		'^novel/([^/]+)/chapter/([^/]+)/?$',
		'index.php?chapter=$matches[2]',
		'top'
	);
}
