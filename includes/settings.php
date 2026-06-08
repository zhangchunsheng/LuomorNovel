<?php
/**
 * 主题设置页面
 *
 * @package LuomorNovel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'luomor_novel_add_settings_page' );
/**
 * 添加主题设置菜单页
 */
function luomor_novel_add_settings_page() {
	add_theme_page(
		__( 'LuomorNovel 设置', 'luomor-novel' ),
		__( 'LuomorNovel 设置', 'luomor-novel' ),
		'manage_options',
		'luomor-novel-settings',
		'luomor_novel_settings_page_render'
	);
}

add_action( 'admin_init', 'luomor_novel_register_settings' );
/**
 * 注册设置
 */
function luomor_novel_register_settings() {
	// AI 设置
	register_setting( 'luomor_ai_settings', 'luomor_ai_openai_key' );
	register_setting( 'luomor_ai_settings', 'luomor_ai_openai_model' );
	register_setting( 'luomor_ai_settings', 'luomor_ai_claude_key' );
	register_setting( 'luomor_ai_settings', 'luomor_ai_claude_model' );
	register_setting( 'luomor_ai_settings', 'luomor_ai_gemini_key' );
	register_setting( 'luomor_ai_settings', 'luomor_ai_gemini_model' );
	register_setting( 'luomor_ai_settings', 'luomor_ai_default_provider' );
	register_setting( 'luomor_ai_settings', 'luomor_ai_max_tokens' );
	register_setting( 'luomor_ai_settings', 'luomor_ai_temperature' );

	// 阅读设置
	register_setting( 'luomor_reading_settings', 'luomor_reading_font_size' );
	register_setting( 'luomor_reading_settings', 'luomor_reading_dark_mode' );
}

/**
 * 渲染设置页面
 */
function luomor_novel_settings_page_render() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<form method="post" action="options.php">
			<?php
			// AI 设置
			settings_fields( 'luomor_ai_settings' );
			?>

			<h2 class="title"><?php esc_html_e( 'AI 提供商配置', 'luomor-novel' ); ?></h2>
			<table class="form-table">
				<!-- OpenAI -->
				<tr>
					<th scope="row">
						<label for="luomor_ai_openai_key"><?php esc_html_e( 'OpenAI API Key', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<input type="password" id="luomor_ai_openai_key" name="luomor_ai_openai_key"
							value="<?php echo esc_attr( get_option( 'luomor_ai_openai_key' ) ); ?>"
							class="regular-text" autocomplete="off" />
						<p class="description"><?php esc_html_e( 'OpenAI API 密钥，用于 GPT-4o 等模型', 'luomor-novel' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="luomor_ai_openai_model"><?php esc_html_e( 'OpenAI 模型', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<input type="text" id="luomor_ai_openai_model" name="luomor_ai_openai_model"
							value="<?php echo esc_attr( get_option( 'luomor_ai_openai_model', 'gpt-4o' ) ); ?>"
							class="regular-text" />
						<p class="description"><?php esc_html_e( '默认：gpt-4o', 'luomor-novel' ); ?></p>
					</td>
				</tr>

				<!-- Claude -->
				<tr>
					<th scope="row">
						<label for="luomor_ai_claude_key"><?php esc_html_e( 'Claude API Key', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<input type="password" id="luomor_ai_claude_key" name="luomor_ai_claude_key"
							value="<?php echo esc_attr( get_option( 'luomor_ai_claude_key' ) ); ?>"
							class="regular-text" autocomplete="off" />
						<p class="description"><?php esc_html_e( 'Anthropic API 密钥，用于 Claude 模型', 'luomor-novel' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="luomor_ai_claude_model"><?php esc_html_e( 'Claude 模型', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<input type="text" id="luomor_ai_claude_model" name="luomor_ai_claude_model"
							value="<?php echo esc_attr( get_option( 'luomor_ai_claude_model', 'claude-sonnet-4-20250514' ) ); ?>"
							class="regular-text" />
						<p class="description"><?php esc_html_e( '默认：claude-sonnet-4-20250514', 'luomor-novel' ); ?></p>
					</td>
				</tr>

				<!-- Gemini -->
				<tr>
					<th scope="row">
						<label for="luomor_ai_gemini_key"><?php esc_html_e( 'Gemini API Key', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<input type="password" id="luomor_ai_gemini_key" name="luomor_ai_gemini_key"
							value="<?php echo esc_attr( get_option( 'luomor_ai_gemini_key' ) ); ?>"
							class="regular-text" autocomplete="off" />
						<p class="description"><?php esc_html_e( 'Google API 密钥，用于 Gemini 模型', 'luomor-novel' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="luomor_ai_gemini_model"><?php esc_html_e( 'Gemini 模型', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<input type="text" id="luomor_ai_gemini_model" name="luomor_ai_gemini_model"
							value="<?php echo esc_attr( get_option( 'luomor_ai_gemini_model', 'gemini-2.5-pro' ) ); ?>"
							class="regular-text" />
						<p class="description"><?php esc_html_e( '默认：gemini-2.5-pro', 'luomor-novel' ); ?></p>
					</td>
				</tr>

				<!-- 默认提供商 -->
				<tr>
					<th scope="row">
						<label for="luomor_ai_default_provider"><?php esc_html_e( '默认 AI 提供商', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<select id="luomor_ai_default_provider" name="luomor_ai_default_provider">
							<option value="openai" <?php selected( get_option( 'luomor_ai_default_provider', 'openai' ), 'openai' ); ?>>OpenAI</option>
							<option value="claude" <?php selected( get_option( 'luomor_ai_default_provider' ), 'claude' ); ?>>Claude</option>
							<option value="gemini" <?php selected( get_option( 'luomor_ai_default_provider' ), 'gemini' ); ?>>Gemini</option>
						</select>
					</td>
				</tr>

				<!-- Max Tokens -->
				<tr>
					<th scope="row">
						<label for="luomor_ai_max_tokens"><?php esc_html_e( '最大 Token 数', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<input type="number" id="luomor_ai_max_tokens" name="luomor_ai_max_tokens"
							value="<?php echo esc_attr( get_option( 'luomor_ai_max_tokens', 4000 ) ); ?>"
							class="small-text" min="100" max="32000" />
						<p class="description"><?php esc_html_e( '单次生成的最大 token 数量', 'luomor-novel' ); ?></p>
					</td>
				</tr>

				<!-- Temperature -->
				<tr>
					<th scope="row">
						<label for="luomor_ai_temperature"><?php esc_html_e( '创意度 (Temperature)', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<input type="number" id="luomor_ai_temperature" name="luomor_ai_temperature"
							value="<?php echo esc_attr( get_option( 'luomor_ai_temperature', 0.7 ) ); ?>"
							class="small-text" min="0" max="2" step="0.1" />
						<p class="description"><?php esc_html_e( '0 = 确定性输出，1 = 更具创意', 'luomor-novel' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button( __( '保存 AI 设置', 'luomor-novel' ) ); ?>
		</form>

		<hr />

		<h2 class="title"><?php esc_html_e( '连接测试', 'luomor-novel' ); ?></h2>
		<div id="luomor-ai-test-result" class="notice" style="display:none; padding:10px; margin:10px 0;"></div>
		<button type="button" class="button" onclick="luomorTestAiConnection('openai')">
			<?php esc_html_e( '测试 OpenAI 连接', 'luomor-novel' ); ?>
		</button>
		<button type="button" class="button" onclick="luomorTestAiConnection('claude')">
			<?php esc_html_e( '测试 Claude 连接', 'luomor-novel' ); ?>
		</button>
		<button type="button" class="button" onclick="luomorTestAiConnection('gemini')">
			<?php esc_html_e( '测试 Gemini 连接', 'luomor-novel' ); ?>
		</button>

		<script>
		function luomorTestAiConnection(provider) {
			var resultDiv = document.getElementById('luomor-ai-test-result');
			resultDiv.style.display = 'block';
			resultDiv.className = 'notice notice-info';
			resultDiv.innerHTML = '<p>正在测试 ' + provider + ' 连接...</p>';

			fetch('<?php echo esc_url_raw( rest_url( 'luomor/v1/ai/test' ) ); ?>' + '/' + provider, {
				method: 'GET',
				headers: {
					'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
				}
			})
			.then(function(res) { return res.json(); })
			.then(function(data) {
				if (data.success) {
					resultDiv.className = 'notice notice-success';
					resultDiv.innerHTML = '<p>' + provider + ' 连接成功！AI 回复：' + data.content + '</p>';
				} else {
					resultDiv.className = 'notice notice-error';
					resultDiv.innerHTML = '<p>' + provider + ' 连接失败：' + data.error + '</p>';
				}
			})
			.catch(function(err) {
				resultDiv.className = 'notice notice-error';
				resultDiv.innerHTML = '<p>请求失败：' + err.message + '</p>';
			});
		}
		</script>

		<hr />

		<h2 class="title"><?php esc_html_e( '阅读设置', 'luomor-novel' ); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'luomor_reading_settings' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="luomor_reading_font_size"><?php esc_html_e( '默认阅读字号', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<select id="luomor_reading_font_size" name="luomor_reading_font_size">
							<option value="small" <?php selected( get_option( 'luomor_reading_font_size' ), 'small' ); ?>><?php esc_html_e( '小', 'luomor-novel' ); ?></option>
							<option value="normal" <?php selected( get_option( 'luomor_reading_font_size', 'normal' ), 'normal' ); ?>><?php esc_html_e( '正常', 'luomor-novel' ); ?></option>
							<option value="medium" <?php selected( get_option( 'luomor_reading_font_size' ), 'medium' ); ?>><?php esc_html_e( '中', 'luomor-novel' ); ?></option>
							<option value="large" <?php selected( get_option( 'luomor_reading_font_size' ), 'large' ); ?>><?php esc_html_e( '大', 'luomor-novel' ); ?></option>
							<option value="x-large" <?php selected( get_option( 'luomor_reading_font_size' ), 'x-large' ); ?>><?php esc_html_e( '特大', 'luomor-novel' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="luomor_reading_dark_mode"><?php esc_html_e( '启用暗色模式切换', 'luomor-novel' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="luomor_reading_dark_mode" name="luomor_reading_dark_mode"
							value="1" <?php checked( get_option( 'luomor_reading_dark_mode' ), '1' ); ?> />
					</td>
				</tr>
			</table>
			<?php submit_button( __( '保存阅读设置', 'luomor-novel' ) ); ?>
		</form>
	</div>
	<?php
}
