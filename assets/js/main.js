/**
 * LuomorNovel 主脚本
 *
 * @package LuomorNovel
 */

( function() {
	'use strict';

	// 全局工具对象
	window.luomorNovel = window.luomorNovel || {};

	/**
	 * 简单的 API 请求封装
	 */
	window.luomorNovel.api = {
		/**
		 * GET 请求
		 */
		get: function( path, params ) {
			var url = luomorNovel.apiRoot + path;
			if ( params ) {
				url += '?' + Object.keys( params )
					.map( function( key ) {
						return encodeURIComponent( key ) + '=' + encodeURIComponent( params[ key ] );
					} )
					.join( '&' );
			}
			return fetch( url, {
				headers: {
					'X-WP-Nonce': luomorNovel.nonce
				}
			} ).then( function( res ) {
				if ( ! res.ok ) {
					throw new Error( 'HTTP ' + res.status );
				}
				return res.json();
			} );
		},

		/**
		 * POST 请求
		 */
		post: function( path, data ) {
			return fetch( luomorNovel.apiRoot + path, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': luomorNovel.nonce
				},
				body: JSON.stringify( data )
			} ).then( function( res ) {
				if ( ! res.ok ) {
					return res.json().then( function( err ) {
						throw new Error( err.message || 'HTTP ' + res.status );
					} );
				}
				return res.json();
			} );
		},

		/**
		 * DELETE 请求
		 */
		del: function( path ) {
			return fetch( luomorNovel.apiRoot + path, {
				method: 'DELETE',
				headers: {
					'X-WP-Nonce': luomorNovel.nonce
				}
			} ).then( function( res ) {
				if ( ! res.ok ) {
					throw new Error( 'HTTP ' + res.status );
				}
				return res.json();
			} );
		},

		/**
		 * PUT 请求
		 */
		put: function( path, data ) {
			return fetch( luomorNovel.apiRoot + path, {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': luomorNovel.nonce
				},
				body: JSON.stringify( data )
			} ).then( function( res ) {
				if ( ! res.ok ) {
					throw new Error( 'HTTP ' + res.status );
				}
				return res.json();
			} );
		}
	};

	/**
	 * 显示通知消息
	 */
	window.luomorNovel.notify = function( message, type ) {
		type = type || 'info';
		var notice = document.createElement( 'div' );
		notice.className = 'luomor-notice luomor-notice-' + type;
		notice.textContent = message;
		notice.style.cssText = 'position:fixed;top:20px;right:20px;padding:12px 20px;border-radius:8px;background:' +
			( type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6' ) +
			';color:#fff;z-index:9999;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.15);transition:opacity 0.3s;';
		document.body.appendChild( notice );
		setTimeout( function() {
			notice.style.opacity = '0';
			setTimeout( function() {
				notice.remove();
			}, 300 );
		}, 3000 );
	};

	/**
	 * 暗色模式切换
	 */
	window.luomorNovel.toggleDarkMode = function() {
		document.body.classList.toggle( 'luomor-dark-mode' );
		var isDark = document.body.classList.contains( 'luomor-dark-mode' );
		localStorage.setItem( 'luomor-dark-mode', isDark ? '1' : '0' );
	};

	// 初始化暗色模式
	( function() {
		var saved = localStorage.getItem( 'luomor-dark-mode' );
		if ( saved === '1' ) {
			document.body.classList.add( 'luomor-dark-mode' );
		}
	} )();

	// DOM 加载完成后初始化
	document.addEventListener( 'DOMContentLoaded', function() {
		// 暗色模式按钮
		var darkBtn = document.querySelector( '.luomor-dark-mode-btn' );
		if ( darkBtn ) {
			darkBtn.addEventListener( 'click', function( e ) {
				e.preventDefault();
				luomorNovel.toggleDarkMode();
			} );
		}

		// 字号切换按钮
		var fontBtn = document.querySelector( '.luomor-font-size-btn' );
		if ( fontBtn ) {
			var sizes = [ 'small', 'normal', 'medium', 'large', 'x-large' ];
			var current = parseInt( localStorage.getItem( 'luomor-font-size' ) || '3', 10 );
			fontBtn.addEventListener( 'click', function( e ) {
				e.preventDefault();
				current = ( current + 1 ) % sizes.length;
				localStorage.setItem( 'luomor-font-size', current.toString() );
				var content = document.querySelector( '.luomor-chapter-content' );
				if ( content ) {
					content.classList.remove( 'luomor-font-' + sizes.join( ' luomor-font-' ) );
					content.classList.add( 'luomor-font-' + sizes[ current ] );
				}
				fontBtn.querySelector( 'button' ).textContent = 'A' +
					( current > 2 ? '+' : current < 2 ? '-' : '' );
			} );
		}

		// 小说详情页：返回链接
		var backLink = document.querySelector( '.luomor-novel-back-link' );
		if ( backLink ) {
			var novelTitle = document.querySelector( '.luomor-chapter-novel-link' );
			if ( novelTitle && novelTitle.dataset.novelUrl ) {
				backLink.href = novelTitle.dataset.novelUrl;
			}
		}
	} );

} )();
