/**
 * 阅读进度追踪
 *
 * @package LuomorNovel
 */

( function() {
	'use strict';

	var saveTimeout;
	var hasProgress = false;

	/**
	 * 保存阅读进度
	 */
	function saveProgress( novelId, chapterId ) {
		if ( ! novelId || ! chapterId ) {
			return;
		}

		luomorNovel.api.post( 'reading-progress', {
			novel_id: parseInt( novelId ),
			chapter_id: parseInt( chapterId )
		} )
		.then( function() {
			hasProgress = true;
		} )
		.catch( function() {
			// 静默失败
		} );
	}

	/**
	 * 获取章节对应的小说 ID
	 */
	function getNovelId() {
		// 尝试从页面获取
		var el = document.querySelector( '[data-novel-id]' );
		if ( el ) {
			return parseInt( el.dataset.novelId, 10 );
		}
		// 从 URL 解析
		var match = window.location.pathname.match( /\/novel\/[^/]+\/chapter\// );
		if ( match ) {
			// 通过 API 获取
			var chapterId = getCurrentChapterId();
			if ( chapterId ) {
				luomorNovel.api.get( 'chapters/' + chapterId )
					.then( function( data ) {
						return data.novel_id;
					} )
					.catch( function() { return null; } );
			}
		}
		return null;
	}

	/**
	 * 获取当前章节 ID
	 */
	function getCurrentChapterId() {
		var el = document.querySelector( '[data-chapter-id]' );
		if ( el ) {
			return parseInt( el.dataset.chapterId, 10 );
		}
		// 尝试从 URL 解析
		var match = window.location.pathname.match( /\/chapter\/(\d+)/ );
		if ( match ) {
			return parseInt( match[1], 10 );
		}
		return null;
	}

	/**
	 * 获取当前阅读进度百分比
	 */
	function getScrollPercentage() {
		var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
		var docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
		if ( docHeight === 0 ) {
			return 0;
		}
		return Math.round( ( scrollTop / docHeight ) * 100 );
	}

	/**
	 * 更新进度条
	 */
	function updateProgressBar() {
		var progressBar = document.querySelector( '.luomor-progress-bar' );
		if ( ! progressBar ) {
			// 创建进度条
			progressBar = document.createElement( 'div' );
			progressBar.className = 'luomor-progress-bar';
			progressBar.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:var(--wp--preset--color--primary);z-index:9999;transition:width 0.1s;';
			document.body.appendChild( progressBar );
		}
		progressBar.style.width = getScrollPercentage() + '%';
	}

	document.addEventListener( 'DOMContentLoaded', function() {
		if ( ! luomorNovel.isLoggedIn ) {
			return;
		}

		var novelId = getNovelId();
		var chapterId = getCurrentChapterId();

		if ( ! novelId || ! chapterId ) {
			return;
		}

		// 滚动时更新进度条
		window.addEventListener( 'scroll', function() {
			updateProgressBar();
		}, { passive: true } );

		// 阅读超过 50% 时保存进度
		window.addEventListener( 'scroll', function() {
			if ( getScrollPercentage() >= 50 && ! hasProgress ) {
				clearTimeout( saveTimeout );
				saveTimeout = setTimeout( function() {
					saveProgress( novelId, chapterId );
				}, 2000 );
			}
		}, { passive: true } );

		// 页面关闭时保存
		window.addEventListener( 'beforeunload', function() {
			if ( getScrollPercentage() > 10 ) {
				saveProgress( novelId, chapterId );
			}
		} );

		// 页面可见性变化时保存
		document.addEventListener( 'visibilitychange', function() {
			if ( document.visibilityState === 'hidden' ) {
				saveProgress( novelId, chapterId );
			}
		} );
	} );

} )();
