<?php
/**
 * LuomorNovel 主题引导文件
 *
 * @package     LuomorNovel
 * @version     1.0.0
 * @link        https://github.com/luomor/LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LUOMOR_NOVEL_DIR', get_template_directory() );
define( 'LUOMOR_NOVEL_URI', get_template_directory_uri() );
define( 'LUOMOR_NOVEL_VERSION', '1.0.0' );

// ============================================================
// 加载模块（按依赖顺序）
// ============================================================

$luomor_includes = array(
	'includes/core.php',            // CPT 和分类法注册
	'includes/meta.php',            // 自定义字段注册
	'includes/blocks.php',          // 自定义区块注册
	'includes/settings.php',        // 主题设置页
	'includes/template-functions.php', // 模板辅助函数
	'includes/bookmarks.php',       // 收藏功能
	'includes/reading-progress.php', // 阅读进度
	'includes/ai-service.php',      // AI 服务抽象层
	'includes/rest-api.php',        // REST API 端点
);

foreach ( $luomor_includes as $file ) {
	require_once LUOMOR_NOVEL_DIR . '/' . $file;
}

// ============================================================
// 主题设置支持
// ============================================================

add_action( 'after_setup_theme', 'luomor_novel_setup' );
/**
 * 初始化主题功能
 */
function luomor_novel_setup() {
	// 区块模板支持
	add_theme_support( 'block-templates' );
	// 缩略图
	add_theme_support( 'post-thumbnails' );
	// RSS Feed
	add_theme_support( 'automatic-feed-links' );
	// 宽幅对齐
	add_theme_support( 'align-wide' );
	// 编辑器样式
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	// 标题标签
	add_theme_support( 'title-tag' );
	// 自定义 Logo
	add_theme_support( 'custom-logo', array(
		'height'      => 60,
		'width'       => 200,
		'flex-height' => true,
	) );
	// 加载翻译
	load_theme_textdomain( 'luomor-novel', LUOMOR_NOVEL_DIR . '/languages' );
	// 注册导航菜单
	register_nav_menus( array(
		'primary'   => esc_html__( '主导航', 'luomor-novel' ),
		'secondary' => esc_html__( '分类导航', 'luomor-novel' ),
	) );
	// 小说封面图片尺寸
	add_image_size( 'novel-cover', 300, 450, true );
	add_image_size( 'novel-cover-thumb', 150, 225, true );
}

// ============================================================
// 资源加载
// ============================================================

add_action( 'wp_enqueue_scripts', 'luomor_novel_enqueue_assets' );
/**
 * 加载前端资源
 */
function luomor_novel_enqueue_assets() {
	$version = LUOMOR_NOVEL_VERSION;
	$uri     = LUOMOR_NOVEL_URI;

	// CSS
	wp_enqueue_style( 'luomor-novel-reading', $uri . '/assets/css/reading.css', array(), $version );

	// 通用 JS
	$script_deps = array( 'wp-api-fetch', 'wp-i18n', 'wp-dom-ready' );
	wp_enqueue_script( 'luomor-novel-main', $uri . '/assets/js/main.js', $script_deps, $version, true );
	wp_localize_script( 'luomor-novel-main', 'luomorNovel', array(
		'apiRoot'  => esc_url_raw( rest_url( 'luomor/v1' ) ),
		'nonce'    => wp_create_nonce( 'wp_rest' ),
		'userId'   => get_current_user_id(),
		'isLoggedIn' => is_user_logged_in(),
		'loginUrl' => wp_login_url( get_permalink() ),
		'i18n'     => array(
			'bookmark'       => __( '收藏', 'luomor-novel' ),
			'bookmarked'     => __( '已收藏', 'luomor-novel' ),
			'loginRequired'  => __( '请先登录后再操作', 'luomor-novel' ),
			'saveProgress'   => __( '保存阅读进度', 'luomor-novel' ),
			'search'         => __( '搜索小说...', 'luomor-novel' ),
		),
	) );

	// 按需加载 JS
	if ( is_singular( 'chapter' ) ) {
		wp_enqueue_script( 'luomor-novel-reading-progress', $uri . '/assets/js/reading-progress.js', array( 'luomor-novel-main' ), $version, true );
	}

	if ( is_singular( 'novel' ) ) {
		wp_enqueue_script( 'luomor-novel-bookmark', $uri . '/assets/js/bookmark.js', array( 'luomor-novel-main' ), $version, true );
	}

	wp_enqueue_script( 'luomor-novel-search', $uri . '/assets/js/search.js', array( 'luomor-novel-main' ), $version, true );
}

add_action( 'admin_enqueue_scripts', 'luomor_novel_admin_assets' );
/**
 * 加载管理后台资源
 */
function luomor_novel_admin_assets() {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	$version = LUOMOR_NOVEL_VERSION;
	$uri     = LUOMOR_NOVEL_URI;

	// 管理后台通用 CSS
	wp_enqueue_style( 'luomor-novel-admin', $uri . '/assets/css/admin.css', array(), $version );

	// 小说编辑页：章节排序 + AI 写作
	if ( in_array( $screen->post_type, array( 'novel', 'chapter' ), true ) ) {
		wp_enqueue_script( 'luomor-novel-ai-writer', $uri . '/assets/js/ai-writer.js', array( 'wp-api-fetch', 'wp-edit-post', 'wp-components' ), $version, true );
		wp_localize_script( 'luomor-novel-ai-writer', 'luomorAiWriter', array(
			'apiRoot'  => esc_url_raw( rest_url( 'luomor/v1' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'i18n'     => array(
				'generate'       => __( 'AI 生成', 'luomor-novel' ),
				'generating'     => __( '生成中...', 'luomor-novel' ),
				'insertEditor'   => __( '插入编辑器', 'luomor-novel' ),
				'provider'       => __( 'AI 提供商', 'luomor-novel' ),
				'template'       => __( '提示词模板', 'luomor-novel' ),
				'chapterContent' => __( '生成章节内容', 'luomor-novel' ),
				'outline'        => __( '生成大纲', 'luomor-novel' ),
				'character'      => __( '生成角色', 'luomor-novel' ),
			),
		) );
	}

	if ( 'novel' === $screen->post_type ) {
		wp_enqueue_script( 'luomor-novel-chapter-sort', $uri . '/assets/js/chapter-sort.js', array( 'wp-api-fetch' ), $version, true );
		wp_localize_script( 'luomor-novel-chapter-sort', 'luomorChapterSort', array(
			'apiRoot' => esc_url_raw( rest_url( 'luomor/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'i18n'    => array(
				'reorder'      => __( '章节排序', 'luomor-novel' ),
				'saveOrder'    => __( '保存排序', 'luomor-novel' ),
				'orderSaved'   => __( '排序已保存', 'luomor-novel' ),
				'saveFailed'   => __( '保存失败', 'luomor-novel' ),
			),
		) );
	}
}

// ============================================================
// 激活主题时预置数据
// ============================================================

add_action( 'after_switch_theme', 'luomor_novel_seed_status_terms' );
/**
 * 预置小说状态术语
 */
function luomor_novel_seed_status_terms() {
	$terms = array(
		array(
			'name'        => '连载中',
			'slug'        => 'ongoing',
			'description' => '小说正在连载中',
		),
		array(
			'name'        => '已完结',
			'slug'        => 'completed',
			'description' => '小说已完结',
		),
		array(
			'name'        => '已暂停',
			'slug'        => 'hiatus',
			'description' => '小说更新已暂停',
		),
	);

	foreach ( $terms as $term ) {
		if ( ! term_exists( $term['slug'], 'novel_status' ) ) {
			wp_insert_term( $term['name'], 'novel_status', array(
				'slug'        => $term['slug'],
				'description' => $term['description'],
			) );
		}
	}

	// 刷新重写规则
	flush_rewrite_rules();
}
