/**
 * AI 写作助手 - 管理后台
 *
 * @package LuomorNovel
 */

( function() {
	'use strict';

	var metaBox;

	document.addEventListener( 'DOMContentLoaded', function() {
		createAiMetaBox();
	} );

	/**
	 * 创建 AI 元框
	 */
	function createAiMetaBox() {
		// 创建元框容器
		metaBox = document.createElement( 'div' );
		metaBox.id = 'luomor-ai-writer';
		metaBox.className = 'postbox';
		metaBox.innerHTML = getMetaBoxHtml();

		// 插入到编辑器之后
		var editorContainer = document.querySelector( '#poststuff' ) ||
			document.querySelector( '#editor' ) ||
			document.querySelector( '.block-editor' );

		if ( editorContainer ) {
			// 放在右侧栏
			var sideSortables = document.querySelector( '#side-sortables' ) ||
				editorContainer;
			sideSortables.insertBefore( metaBox, sideSortables.firstChild );
		}

		// 绑定事件
		bindEvents();
	}

	/**
	 * 元框 HTML
	 */
	function getMetaBoxHtml() {
		var i18n = luomorAiWriter.i18n;

		return '<h2 class="hndle"><span>' + i18n.generate + '</span></h2>' +
			'<div class="inside">' +
			'<div class="luomor-ai-controls">' +

			// 提供商选择
			'<div class="luomor-ai-field">' +
			'<label>' + i18n.provider + '</label>' +
			'<select id="luomor-ai-provider" class="widefat">' +
			'<option value="">自动选择</option>' +
			'<option value="openai">OpenAI</option>' +
			'<option value="claude">Claude</option>' +
			'<option value="gemini">Gemini</option>' +
			'</select>' +
			'</div>' +

			// 模板选择
			'<div class="luomor-ai-field">' +
			'<label>' + i18n.template + '</label>' +
			'<select id="luomor-ai-template" class="widefat">' +
			'<option value="chapter">' + i18n.chapterContent + '</option>' +
			'<option value="outline">' + i18n.outline + '</option>' +
			'<option value="character">' + i18n.character + '</option>' +
			'<option value="custom">自定义提示词</option>' +
			'</select>' +
			'</div>' +

			// 提示词输入
			'<div class="luomor-ai-field">' +
			'<label>提示词</label>' +
			'<textarea id="luomor-ai-prompt" class="widefat" rows="4" ' +
			'placeholder="输入你的提示词..."></textarea>' +
			'</div>' +

			// 生成按钮
			'<button type="button" id="luomor-ai-generate-btn" class="button button-primary button-large">' +
			i18n.generate +
			'</button>' +

			// 结果展示
			'<div id="luomor-ai-result" style="display:none;margin-top:15px;">' +
			'<textarea id="luomor-ai-output" class="widefat" rows="8" readonly></textarea>' +
			'<button type="button" id="luomor-ai-insert-btn" class="button button-secondary" style="margin-top:8px;">' +
			i18n.insertEditor +
			'</button>' +
			'</div>' +

			// 错误提示
			'<div id="luomor-ai-error" style="display:none;margin-top:10px;color:#dc3232;"></div>' +

			'</div>';
	}

	/**
	 * 绑定事件
	 */
	function bindEvents() {
		var generateBtn = document.getElementById( 'luomor-ai-generate-btn' );
		var insertBtn = document.getElementById( 'luomor-ai-insert-btn' );
		var templateSelect = document.getElementById( 'luomor-ai-template' );

		if ( generateBtn ) {
			generateBtn.addEventListener( 'click', handleGenerate );
		}

		if ( insertBtn ) {
			insertBtn.addEventListener( 'click', handleInsert );
		}

		// 模板切换自动填充
		if ( templateSelect ) {
			templateSelect.addEventListener( 'change', function() {
				var prompt = document.getElementById( 'luomor-ai-prompt' );
				var template = this.value;
				var title = document.querySelector( '#title' );
				var titleText = title ? title.value : '';

				switch ( template ) {
					case 'chapter':
						prompt.value = '请为以下小说编写章节内容。\n\n小说标题：' + titleText + '\n章节标题：';
						break;
					case 'outline':
						prompt.value = '请为以下小说生成详细的章节大纲。\n\n小说标题：' + titleText + '\n简介：';
						break;
					case 'character':
						prompt.value = '请为以下小说生成角色描述。\n\n小说标题：' + titleText + '\n简介：';
						break;
				}
			} );
		}
	}

	/**
	 * 处理生成
	 */
	function handleGenerate() {
		var prompt = document.getElementById( 'luomor-ai-prompt' );
		var provider = document.getElementById( 'luomor-ai-provider' );
		var btn = document.getElementById( 'luomor-ai-generate-btn' );
		var resultDiv = document.getElementById( 'luomor-ai-result' );
		var output = document.getElementById( 'luomor-ai-output' );
		var errorDiv = document.getElementById( 'luomor-ai-error' );

		if ( ! prompt.value.trim() ) {
			errorDiv.textContent = '请输入提示词';
			errorDiv.style.display = 'block';
			return;
		}

		// 禁用按钮，显示加载状态
		btn.disabled = true;
		btn.textContent = luomorAiWriter.i18n.generating;
		errorDiv.style.display = 'none';

		var data = {
			prompt: prompt.value,
			system_prompt: getSystemPrompt(),
			provider: provider.value
		};

		fetch( luomorAiWriter.apiRoot + 'ai/generate', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': luomorAiWriter.nonce
			},
			body: JSON.stringify( data )
		} )
		.then( function( res ) {
			if ( ! res.ok ) {
				return res.json().then( function( err ) {
					throw new Error( err.message || '请求失败' );
				} );
			}
			return res.json();
		} )
		.then( function( data ) {
			if ( data.success ) {
				output.value = data.content;
				resultDiv.style.display = 'block';
			} else {
				errorDiv.textContent = data.error || '生成失败';
				errorDiv.style.display = 'block';
			}
		} )
		.catch( function( err ) {
			errorDiv.textContent = err.message;
			errorDiv.style.display = 'block';
		} )
		.finally( function() {
			btn.disabled = false;
			btn.textContent = luomorAiWriter.i18n.generate;
		} );
	}

	/**
	 * 获取系统提示词
	 */
	function getSystemPrompt() {
		return '你是一个专业的小说作家。请用优美流畅的中文写作。注意情节连贯性、人物性格一致性和文学性。';
	}

	/**
	 * 处理插入编辑器
	 */
	function handleInsert() {
		var output = document.getElementById( 'luomor-ai-output' );
		var content = output.value;

		if ( ! content ) {
			return;
		}

		// 尝试插入到 WordPress 编辑器
		if ( window.wp && wp.data ) {
			try {
				// Gutenberg 编辑器
				var blocks = wp.data.select( 'core/block-editor' ).getBlocks();
				var lastBlock = blocks[ blocks.length - 1 ];

				if ( lastBlock && lastBlock.name === 'core/paragraph' ) {
					// 插入到最后一个段落块
					wp.data.dispatch( 'core/block-editor' ).updateBlock( lastBlock.clientId, {
						attributes: {
							content: content
						}
					} );
				} else {
					// 创建新的段落块
					var newBlock = wp.blocks.createBlock( 'core/paragraph', {
						content: content
					} );
					wp.data.dispatch( 'core/block-editor' ).insertBlock( newBlock );
				}

				luomorNovel.notify( '内容已插入编辑器', 'success' );
			} catch ( e ) {
				// Fallback: 复制到剪贴板
				navigator.clipboard.writeText( content )
					.then( function() {
						luomorNovel.notify( '内容已复制到剪贴板', 'success' );
					} )
					.catch( function() {
						luomorNovel.notify( '无法插入内容', 'error' );
					} );
			}
		} else {
			// Fallback: 复制到剪贴板
			if ( navigator.clipboard ) {
				navigator.clipboard.writeText( content )
					.then( function() {
						luomorNovel.notify( '内容已复制到剪贴板', 'success' );
					} );
			}
		}
	}

} )();
