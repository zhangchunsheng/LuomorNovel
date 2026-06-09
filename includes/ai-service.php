<?php
/**
 * AI 服务抽象层 - 多提供商支持
 *
 * @package LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ============================================================
// AI 提供商接口
// ============================================================

/**
 * AI 提供商接口
 */
interface Luomor_AI_Provider_Interface {
	public function get_slug(): string;
	public function get_name(): string;
	public function is_configured(): bool;
	public function generate( array $args ): array;
	public function test_connection(): array;
}

// ============================================================
// OpenAI 提供商
// ============================================================

class Luomor_AI_OpenAI implements Luomor_AI_Provider_Interface {
	public function get_slug(): string { return 'openai'; }
	public function get_name(): string { return 'OpenAI'; }

	public function is_configured(): bool {
		return ! empty( get_option( 'luomor_ai_openai_key' ) );
	}

	public function generate( array $args ): array {
		$api_key = get_option( 'luomor_ai_openai_key', '' );
		$model   = get_option( 'luomor_ai_openai_model', 'gpt-4o' );
		$base_url = rtrim( get_option( 'luomor_ai_openai_base_url', 'https://api.openai.com/v1/chat/completions' ), '/' );
		$max_tokens = (int) get_option( 'luomor_ai_max_tokens', 4000 );
		$temperature = (float) get_option( 'luomor_ai_temperature', 0.7 );

		$messages = array();

		if ( ! empty( $args['system_prompt'] ) ) {
			$messages[] = array(
				'role'    => 'system',
				'content' => $args['system_prompt'],
			);
		}

		$messages[] = array(
			'role'    => 'user',
			'content' => $args['prompt'],
		);

		// 如果有历史消息
		if ( ! empty( $args['messages'] ) && is_array( $args['messages'] ) ) {
			$messages = array_merge( $args['messages'], $messages );
		}

		$response = wp_remote_post( $base_url , array(
			'timeout' => 30,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'body' => wp_json_encode( array(
				'model'       => $model,
				'messages'    => $messages,
				'max_tokens'  => $max_tokens,
				'temperature' => $temperature,
			) ),
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'content' => '',
				'error'   => $response->get_error_message(),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $status_code ) {
			return array(
				'success' => false,
				'content' => '',
				'error'   => isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'API 请求失败', 'luomor-novel' ),
			);
		}

		$content = $body['choices'][0]['message']['content'] ?? '';
		return array(
			'success' => true,
			'content' => $content,
			'error'   => '',
		);
	}

	public function test_connection(): array {
		return $this->generate( array(
			'prompt'       => '你好，请回复"连接成功"',
			'system_prompt' => '',
		) );
	}
}

// ============================================================
// Claude (Anthropic) 提供商
// ============================================================

class Luomor_AI_Claude implements Luomor_AI_Provider_Interface {
	public function get_slug(): string { return 'claude'; }
	public function get_name(): string { return 'Claude'; }

	public function is_configured(): bool {
		return ! empty( get_option( 'luomor_ai_claude_key' ) );
	}

	public function generate( array $args ): array {
		$api_key = get_option( 'luomor_ai_claude_key', '' );
		$model   = get_option( 'luomor_ai_claude_model', 'claude-sonnet-4-20250514' );
		$base_url = rtrim( get_option( 'luomor_ai_claude_base_url', 'https://api.anthropic.com/v1/messages' ), '/' );
		$max_tokens = (int) get_option( 'luomor_ai_max_tokens', 4000 );
		$temperature = (float) get_option( 'luomor_ai_temperature', 0.7 );

		$messages = array();

		if ( ! empty( $args['messages'] ) && is_array( $args['messages'] ) ) {
			foreach ( $args['messages'] as $msg ) {
				if ( 'system' !== $msg['role'] ) {
					$messages[] = array(
						'role'    => $msg['role'],
						'content' => $msg['content'],
					);
				}
			}
		}

		// 添加当前 prompt
		$messages[] = array(
			'role'    => 'user',
			'content' => $args['prompt'],
		);

		$body = array(
			'model'       => $model,
			'messages'    => $messages,
			'max_tokens'  => $max_tokens,
			'temperature' => $temperature,
		);

		if ( ! empty( $args['system_prompt'] ) ) {
			$body['system'] = $args['system_prompt'];
		}

		$response = wp_remote_post( $base_url , array(
			'timeout' => 30,
			'headers' => array(
				'Content-Type'      => 'application/json',
				'x-api-key'         => $api_key,
				'anthropic-version' => '2023-06-01',
			),
			'body' => wp_json_encode( $body ),
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'content' => '',
				'error'   => $response->get_error_message(),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$result_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $status_code ) {
			return array(
				'success' => false,
				'content' => '',
				'error'   => isset( $result_body['error']['message'] ) ? $result_body['error']['message'] : __( 'API 请求失败', 'luomor-novel' ),
			);
		}

		$content = '';
		if ( isset( $result_body['content'] ) && is_array( $result_body['content'] ) ) {
			foreach ( $result_body['content'] as $block ) {
				if ( 'text' === ( $block['type'] ?? '' ) ) {
					$content .= $block['text'];
				}
			}
		}

		return array(
			'success' => true,
			'content' => $content,
			'error'   => '',
		);
	}

	public function test_connection(): array {
		return $this->generate( array(
			'prompt'       => '你好，请回复"连接成功"',
			'system_prompt' => '',
		) );
	}
}

// ============================================================
// Gemini (Google) 提供商
// ============================================================

class Luomor_AI_Gemini implements Luomor_AI_Provider_Interface {
	public function get_slug(): string { return 'gemini'; }
	public function get_name(): string { return 'Gemini'; }

	public function is_configured(): bool {
		return ! empty( get_option( 'luomor_ai_gemini_key' ) );
	}

	public function generate( array $args ): array {
		$api_key = get_option( 'luomor_ai_gemini_key', '' );
		$model   = get_option( 'luomor_ai_gemini_model', 'gemini-2.5-pro' );
		$base_url = rtrim( get_option( 'luomor_ai_gemini_base_url', 'https://generativelanguage.googleapis.com/v1beta' ), '/' );
		$temperature = (float) get_option( 'luomor_ai_temperature', 0.7 );

		// 构建内容
		$parts = array();
		if ( ! empty( $args['system_prompt'] ) ) {
			$parts[] = array( 'text' => $args['system_prompt'] . "\n\n---\n\n" );
		}
		$parts[] = array( 'text' => $args['prompt'] );

		// 添加历史消息
		if ( ! empty( $args['messages'] ) && is_array( $args['messages'] ) ) {
			$history_parts = array();
			foreach ( $args['messages'] as $msg ) {
				$role = ( 'assistant' === $msg['role'] ) ? 'model' : 'user';
				$history_parts[] = array(
					'role'  => $role,
					'parts' => array( array( 'text' => $msg['content'] ) ),
				);
			}
		}

		$request_body = array(
			'contents' => array(
				array(
					'role'  => 'user',
					'parts' => $parts,
				),
			),
			'generationConfig' => array(
				'temperature' => $temperature,
			),
		);

		$url = sprintf(
			'%s/models/%s:generateContent?key=%s',
			$base_url,
			$model,
			$api_key
		);

		$response = wp_remote_post( $url, array(
			'timeout' => 30,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body' => wp_json_encode( $request_body ),
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'content' => '',
				'error'   => $response->get_error_message(),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$result_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $status_code ) {
			$error_msg = __( 'API 请求失败', 'luomor-novel' );
			if ( isset( $result_body['error']['message'] ) ) {
				$error_msg = $result_body['error']['message'];
			}
			return array(
				'success' => false,
				'content' => '',
				'error'   => $error_msg,
			);
		}

		$content = '';
		if ( isset( $result_body['candidates'][0]['content']['parts'] ) ) {
			foreach ( $result_body['candidates'][0]['content']['parts'] as $part ) {
				if ( ! empty( $part['text'] ) ) {
					$content .= $part['text'];
				}
			}
		}

		return array(
			'success' => true,
			'content' => $content,
			'error'   => '',
		);
	}

	public function test_connection(): array {
		return $this->generate( array(
			'prompt'       => '你好，请回复"连接成功"',
			'system_prompt' => '',
		) );
	}
}

// ============================================================
// AI 服务门面
// ============================================================

class Luomor_AI_Service {

	private static $providers = null;

	/**
	 * 获取所有已注册的提供商
	 */
	private static function get_providers(): array {
		if ( null === self::$providers ) {
			self::$providers = array(
				'openai'  => new Luomor_AI_OpenAI(),
				'claude'  => new Luomor_AI_Claude(),
				'gemini'  => new Luomor_AI_Gemini(),
			);
		}
		return self::$providers;
	}

	/**
	 * 根据 slug 获取提供商
	 */
	public static function get_provider( string $slug ): ?Luomor_AI_Provider_Interface {
		$providers = self::get_providers();
		return $providers[ $slug ] ?? null;
	}

	/**
	 * 获取当前活跃的提供商
	 */
	public static function get_active_provider(): ?Luomor_AI_Provider_Interface {
		$providers = self::get_providers();

		// 优先使用设置的默认提供商
		$default = get_option( 'luomor_ai_default_provider', 'openai' );
		if ( isset( $providers[ $default ] ) && $providers[ $default ]->is_configured() ) {
			return $providers[ $default ];
		}

		// 回退到第一个已配置的提供商
		foreach ( $providers as $provider ) {
			if ( $provider->is_configured() ) {
				return $provider;
			}
		}

		return null;
	}

	/**
	 * 获取可用的提供商列表
	 */
	public static function get_available_providers(): array {
		$available = array();
		foreach ( self::get_providers() as $slug => $provider ) {
			if ( $provider->is_configured() ) {
				$available[ $slug ] = $provider->get_name();
			}
		}
		return $available;
	}

	/**
	 * 统一生成方法
	 *
	 * @param string $prompt        提示词
	 * @param string $system_prompt 系统提示词
	 * @param array  $options       额外选项 ['provider' => 'openai'|'claude'|'gemini']
	 * @return array ['success' => bool, 'content' => string, 'error' => string]
	 */
	public static function generate( string $prompt, string $system_prompt = '', array $options = array() ): array {
		// 速率限制
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$rate_key = 'luomor_ai_rate_' . $user_id;
			$rate     = get_transient( $rate_key );
			if ( $rate !== false && (int) $rate >= 5 ) {
				return array(
					'success' => false,
					'content' => '',
					'error'   => __( '请求过于频繁，请稍后再试', 'luomor-novel' ),
				);
			}
			if ( $rate === false ) {
				set_transient( $rate_key, 1, MINUTE_IN_SECONDS );
			} else {
				set_transient( $rate_key, (int) $rate + 1, MINUTE_IN_SECONDS );
			}
		}

		// 确定提供商
		$provider_slug = $options['provider'] ?? '';
		$provider      = null;

		if ( $provider_slug ) {
			$provider = self::get_provider( $provider_slug );
		}

		if ( ! $provider ) {
			$provider = self::get_active_provider();
		}

		if ( ! $provider ) {
			return array(
				'success' => false,
				'content' => '',
				'error'   => __( '未配置任何 AI 提供商，请在设置中配置 API Key', 'luomor-novel' ),
			);
		}

		return $provider->generate( array(
			'prompt'        => $prompt,
			'system_prompt' => $system_prompt,
			'messages'      => $options['messages'] ?? array(),
		) );
	}

	/**
	 * 测试提供商连接
	 */
	public static function test_provider( string $slug ): array {
		$provider = self::get_provider( $slug );
		if ( ! $provider ) {
			return array(
				'success' => false,
				'content' => '',
				'error'   => __( '未知的提供商', 'luomor-novel' ),
			);
		}
		return $provider->test_connection();
	}
}

// ============================================================
// 提示词构建器
// ============================================================

/**
 * 构建章节内容生成的提示词
 */
function luomor_ai_build_chapter_prompt( array $context ): string {
	$prompt = sprintf(
		"请为以下小说编写章节内容。\n\n小说标题：%s\n",
		$context['novel_title'] ?? '未知'
	);

	if ( ! empty( $context['novel_synopsis'] ) ) {
		$prompt .= "小说简介：{$context['novel_synopsis']}\n";
	}

	if ( ! empty( $context['genre'] ) ) {
		$prompt .= "小说类型：{$context['genre']}\n";
	}

	$prompt .= "\n章节标题：{$context['chapter_title']}\n";

	if ( ! empty( $context['previous_chapter_content'] ) ) {
		$prompt .= "\n上一章内容摘要：\n" . mb_strimwidth( $context['previous_chapter_content'], 0, 2000, '...' ) . "\n";
	}

	if ( ! empty( $context['outline'] ) ) {
		$prompt .= "\n章节大纲：{$context['outline']}\n";
	}

	$prompt .= "\n请根据以上信息编写章节内容。要求：\n";
	$prompt .= "1. 内容连贯，与上下文衔接自然\n";
	$prompt .= "2. 语言风格与小说类型匹配\n";
	$prompt .= "3. 内容长度适中（约 2000-5000 字）\n";
	$prompt .= "4. 注意人物性格一致性和情节推进\n";

	return $prompt;
}

/**
 * 构建大纲生成的提示词
 */
function luomor_ai_build_outline_prompt( array $context ): string {
	$prompt = sprintf(
		"请为以下小说生成详细的章节大纲。\n\n小说标题：%s\n小说简介：%s\n",
		$context['novel_title'] ?? '未知',
		$context['novel_synopsis'] ?? ''
	);

	if ( ! empty( $context['genre'] ) ) {
		$prompt .= "小说类型：{$context['genre']}\n";
	}

	if ( ! empty( $context['target_chapters'] ) ) {
		$prompt .= "目标章节数：{$context['target_chapters']}\n";
	}

	$prompt .= "\n请以 JSON 格式返回大纲，包含以下字段：\n";
	$prompt .= "- chapters: 数组，每个元素包含 title（标题）和 summary（摘要）\n";

	return $prompt;
}

/**
 * 构建角色描述的提示词
 */
function luomor_ai_build_character_prompt( array $context ): string {
	$prompt = sprintf(
		"请为以下小说生成角色描述。\n\n小说标题：%s\n小说简介：%s\n",
		$context['novel_title'] ?? '未知',
		$context['novel_synopsis'] ?? ''
	);

	if ( ! empty( $context['existing_characters'] ) ) {
		$prompt .= "已有角色：{$context['existing_characters']}\n";
	}

	$prompt .= "\n请为每个主要角色生成描述，包括：\n";
	$prompt .= "- 角色名称\n- 外貌特征\n- 性格特点\n- 背景故事\n- 在故事中的作用\n";

	return $prompt;
}
