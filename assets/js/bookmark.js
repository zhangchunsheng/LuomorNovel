/**
 * 收藏功能
 *
 * @package LuomorNovel
 */

( function() {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function() {
		var bookmarkBtns = document.querySelectorAll( '.luomor-bookmark-btn' );

		bookmarkBtns.forEach( function( btn ) {
			btn.addEventListener( 'click', function( e ) {
				e.preventDefault();

				if ( ! luomorNovel.isLoggedIn ) {
					luomorNovel.notify( luomorNovel.i18n.loginRequired, 'error' );
					window.location.href = luomorNovel.loginUrl;
					return;
				}

				var novelId = btn.dataset.novelId || getNovelIdFromUrl();
				if ( ! novelId ) {
					return;
				}

				var icon = btn.querySelector( '.bookmark-icon' );
				var text = btn.querySelector( '.bookmark-text' );

				// 检查是否已收藏
				luomorNovel.api.get( 'bookmarks/' + novelId + '/check' )
					.then( function( data ) {
						if ( data.is_bookmarked ) {
							// 取消收藏
							return luomorNovel.api.del( 'bookmarks/' + novelId )
								.then( function( result ) {
									icon.innerHTML = '&#9825;';
									text.textContent = luomorNovel.i18n.bookmark;
									luomorNovel.notify( '已取消收藏', 'info' );
								} );
						} else {
							// 添加收藏
							return luomorNovel.api.post( 'bookmarks/' + novelId, { novel_id: parseInt( novelId ) } )
								.then( function( result ) {
									icon.innerHTML = '&#9829;';
									text.textContent = luomorNovel.i18n.bookmarked;
									luomorNovel.notify( '收藏成功', 'success' );
								} );
						}
					} )
					.catch( function( err ) {
						luomorNovel.notify( err.message, 'error' );
					} );
			} );
		} );

		// 初始化收藏按钮状态
		initBookmarkState();
	} );

	/**
	 * 从 URL 获取小说 ID
	 */
	function getNovelIdFromUrl() {
		// 尝试从页面 data 属性获取
		var el = document.querySelector( '[data-novel-id]' );
		if ( el ) {
			return el.dataset.novelId;
		}
		return null;
	}

	/**
	 * 初始化收藏按钮状态
	 */
	function initBookmarkState() {
		var novelId = getNovelIdFromUrl();
		if ( ! novelId || ! luomorNovel.isLoggedIn ) {
			return;
		}

		luomorNovel.api.get( 'bookmarks/' + novelId + '/check' )
			.then( function( data ) {
				if ( data.is_bookmarked ) {
					var btn = document.querySelector( '.luomor-bookmark-btn' );
					if ( btn ) {
						var icon = btn.querySelector( '.bookmark-icon' );
						var text = btn.querySelector( '.bookmark-text' );
						if ( icon ) icon.innerHTML = '&#9829;';
						if ( text ) text.textContent = luomorNovel.i18n.bookmarked;
					}
				}
			} )
			.catch( function() {
				// 静默失败
			} );
	}

} )();
