/**
 * 章节排序 - 管理后台拖拽排序
 *
 * @package LuomorNovel
 */

( function() {
	'use strict';

	var sortList;
	var novelId;

	document.addEventListener( 'DOMContentLoaded', function() {
		// 只在小说编辑页初始化
		var screen = document.getElementById( 'post_ID' );
		if ( ! screen ) {
			return;
		}

		novelId = screen.value;
		initChapterSort();
	} );

	/**
	 * 初始化章节排序
	 */
	function initChapterSort() {
		// 检查是否在小说编辑页
		var postType = document.querySelector( 'input[name="post_type"]' );
		if ( ! postType || postType.value !== 'novel' ) {
			return;
		}

		// 创建排序面板
		var panel = document.createElement( 'div' );
		panel.id = 'luomor-chapter-sort-panel';
		panel.className = 'postbox';
		panel.innerHTML = getPanelHtml();

		var sideSortables = document.querySelector( '#side-sortables' );
		if ( sideSortables ) {
			sideSortables.appendChild( panel );
		}

		// 绑定拖拽
		bindDragDrop();

		// 加载章节列表
		loadChapters();

		// 绑定保存按钮
		var saveBtn = document.getElementById( 'luomor-save-order-btn' );
		if ( saveBtn ) {
			saveBtn.addEventListener( 'click', saveOrder );
		}
	}

	/**
	 * 面板 HTML
	 */
	function getPanelHtml() {
		var i18n = luomorChapterSort.i18n;

		return '<h2 class="hndle"><span>' + i18n.reorder + '</span></h2>' +
			'<div class="inside">' +
			'<ul id="luomor-chapter-sort-list" style="list-style:none;margin:0;padding:0;min-height:50px;">' +
			'<li class="luomor-chapter-sort-item" style="padding:8px;margin:4px 0;background:#f9f9f9;border:1px solid #ddd;border-radius:4px;cursor:move;">' +
			'<span style="color:#999;margin-right:8px;">&#9776;</span> ' +
			'加载中...' +
			'</li>' +
			'</ul>' +
			'<button type="button" id="luomor-save-order-btn" class="button button-primary" style="margin-top:10px;">' +
			i18n.saveOrder +
			'</button>' +
			'<div id="luomor-sort-message" style="margin-top:8px;font-size:12px;display:none;"></div>' +
			'</div>';
	}

	/**
	 * 加载章节列表
	 */
	function loadChapters() {
		var list = document.getElementById( 'luomor-chapter-sort-list' );
		if ( ! list ) {
			return;
		}

		var base = luomorChapterSort.apiRoot.replace( /\/+$/, '' ) + '/';

		fetch( base + 'novels/' + novelId + '/chapters?per_page=500', {
			headers: {
				'X-WP-Nonce': luomorChapterSort.nonce
			}
		} )
		.then( function( res ) { return res.json(); } )
		.then( function( data ) {
			list.innerHTML = '';

			if ( ! data.chapters || data.chapters.length === 0 ) {
				list.innerHTML = '<li style="padding:8px;color:#999;">暂无章节</li>';
				return;
			}

			data.chapters.forEach( function( chapter ) {
				var li = document.createElement( 'li' );
				li.className = 'luomor-chapter-sort-item';
				li.draggable = true;
				li.dataset.chapterId = chapter.id;
				li.style.cssText = 'padding:8px;margin:4px 0;background:#f9f9f9;border:1px solid #ddd;border-radius:4px;cursor:move;';
				li.innerHTML = '<span style="color:#999;margin-right:8px;">&#9776;</span> ' +
					'<span class="luomor-chapter-title">' + escapeHtml( chapter.title ) + '</span>';
				list.appendChild( li );
			} );
		} )
		.catch( function() {
			list.innerHTML = '<li style="padding:8px;color:#dc3232;">加载失败</li>';
		} );
	}

	/**
	 * 绑定拖拽事件
	 */
	function bindDragDrop() {
		var list = document.getElementById( 'luomor-chapter-sort-list' );
		if ( ! list ) {
			return;
		}

		var draggedItem = null;

		list.addEventListener( 'dragstart', function( e ) {
			draggedItem = e.target.closest( '.luomor-chapter-sort-item' );
			if ( draggedItem ) {
				draggedItem.style.opacity = '0.5';
				e.dataTransfer.effectAllowed = 'move';
			}
		} );

		list.addEventListener( 'dragend', function( e ) {
			if ( draggedItem ) {
				draggedItem.style.opacity = '1';
				draggedItem = null;
			}

			// 更新序号
			updateOrderNumbers();
		} );

		list.addEventListener( 'dragover', function( e ) {
			e.preventDefault();
			e.dataTransfer.dropEffect = 'move';

			var target = e.target.closest( '.luomor-chapter-sort-item' );
			if ( target && target !== draggedItem ) {
				var rect = target.getBoundingClientRect();
				var midpoint = rect.top + rect.height / 2;

				if ( e.clientY < midpoint ) {
					list.insertBefore( draggedItem, target );
				} else {
					list.insertBefore( draggedItem, target.nextSibling );
				}
			}
		} );

		// 触摸设备支持
		var touchItem = null;
		list.addEventListener( 'touchstart', function( e ) {
			touchItem = e.target.closest( '.luomor-chapter-sort-item' );
			if ( touchItem ) {
				touchItem.style.opacity = '0.5';
			}
		} );

		list.addEventListener( 'touchmove', function( e ) {
			if ( ! touchItem ) {
				return;
			}
			e.preventDefault();
			var touch = e.touches[0];
			var target = document.elementFromPoint( touch.clientX, touch.clientY );
			target = target ? target.closest( '.luomor-chapter-sort-item' ) : null;

			if ( target && target !== touchItem ) {
				var rect = target.getBoundingClientRect();
				var midpoint = rect.top + rect.height / 2;

				if ( touch.clientY < midpoint ) {
					list.insertBefore( touchItem, target );
				} else {
					list.insertBefore( touchItem, target.nextSibling );
				}
			}
		} );

		list.addEventListener( 'touchend', function() {
			if ( touchItem ) {
				touchItem.style.opacity = '1';
				touchItem = null;
			}
			updateOrderNumbers();
		} );
	}

	/**
	 * 更新序号显示
	 */
	function updateOrderNumbers() {
		var items = document.querySelectorAll( '.luomor-chapter-sort-item' );
		items.forEach( function( item, index ) {
			var titleSpan = item.querySelector( '.luomor-chapter-title' );
			if ( titleSpan ) {
				titleSpan.textContent = ( index + 1 ) + '. ' + titleSpan.textContent.replace( /^\d+\.\s*/, '' );
			}
		} );
	}

	/**
	 * 保存排序
	 */
	function saveOrder() {
		var items = document.querySelectorAll( '.luomor-chapter-sort-item' );
		var msg = document.getElementById( 'luomor-sort-message' );
		var i18n = luomorChapterSort.i18n;

		var order = [];
		items.forEach( function( item, index ) {
			order.push( {
				id: parseInt( item.dataset.chapterId, 10 ),
				position: index + 1
			} );
		} );

		if ( msg ) {
			msg.style.display = 'block';
			msg.textContent = '保存中...';
			msg.style.color = '#666';
		}

		fetch( base + 'novels/' + novelId + '/chapters/reorder', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': luomorChapterSort.nonce
			},
			body: JSON.stringify( { novel_id: parseInt( novelId ), order: order } )
		} )
		.then( function( res ) { return res.json(); } )
		.then( function( data ) {
			if ( data.success && msg ) {
				msg.textContent = i18n.orderSaved;
				msg.style.color = '#10b981';
			} else if ( msg ) {
				msg.textContent = i18n.saveFailed;
				msg.style.color = '#dc3232';
			}
		} )
		.catch( function() {
			if ( msg ) {
				msg.textContent = i18n.saveFailed;
				msg.style.color = '#dc3232';
			}
		} );
	}

	/**
	 * HTML 转义
	 */
	function escapeHtml( text ) {
		var div = document.createElement( 'div' );
		div.textContent = text;
		return div.innerHTML;
	}

} )();
