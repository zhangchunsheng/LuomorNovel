/**
 * 搜索建议
 *
 * @package LuomorNovel
 */

( function() {
	'use strict';

	var debounceTimer;
	var currentResults;

	document.addEventListener( 'DOMContentLoaded', function() {
		var searchInputs = document.querySelectorAll( '.luomor-header-search input[type="search"], input[placeholder*="搜索"]' );

		searchInputs.forEach( function( input ) {
			// 创建建议下拉框
			var dropdown = document.createElement( 'div' );
			dropdown.className = 'luomor-search-dropdown';
			dropdown.style.cssText = 'display:none;position:absolute;top:100%;left:0;right:0;background:var(--wp--preset--color--surface);border:1px solid var(--wp--preset--color--muted);border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.1);z-index:1000;max-height:400px;overflow-y:auto;';
			input.parentNode.style.position = 'relative';
			input.parentNode.appendChild( dropdown );

			var activeIndex = -1;

			input.addEventListener( 'input', function() {
				var query = input.value.trim();
				activeIndex = -1;

				if ( query.length < 2 ) {
					dropdown.style.display = 'none';
					return;
				}

				clearTimeout( debounceTimer );
				debounceTimer = setTimeout( function() {
					fetchSuggestions( query, dropdown );
				}, 300 );
			} );

			input.addEventListener( 'keydown', function( e ) {
				var items = dropdown.querySelectorAll( '.luomor-search-item' );
				if ( ! items.length ) {
					return;
				}

				if ( e.key === 'ArrowDown' ) {
					e.preventDefault();
					activeIndex = Math.min( activeIndex + 1, items.length - 1 );
					updateActiveItem( items, activeIndex );
				} else if ( e.key === 'ArrowUp' ) {
					e.preventDefault();
					activeIndex = Math.max( activeIndex - 1, 0 );
					updateActiveItem( items, activeIndex );
				} else if ( e.key === 'Enter' && activeIndex >= 0 ) {
					e.preventDefault();
					items[ activeIndex ].click();
				} else if ( e.key === 'Escape' ) {
					dropdown.style.display = 'none';
				}
			} );

			// 点击外部关闭
			document.addEventListener( 'click', function( e ) {
				if ( ! input.parentNode.contains( e.target ) ) {
					dropdown.style.display = 'none';
				}
			} );
		} );
	} );

	/**
	 * 获取搜索建议
	 */
	function fetchSuggestions( query, dropdown ) {
		luomorNovel.api.get( 'search', { q: query, type: 'all', per_page: 8 } )
			.then( function( data ) {
				dropdown.innerHTML = '';

				var results = [];

				if ( data.novels && data.novels.length ) {
					data.novels.forEach( function( novel ) {
						results.push( {
							type: 'novel',
							title: novel.title,
							link: novel.link,
							excerpt: novel.excerpt || ''
						} );
					} );
				}

				if ( data.chapters && data.chapters.length ) {
					data.chapters.forEach( function( chapter ) {
						results.push( {
							type: 'chapter',
							title: chapter.title,
							link: chapter.link,
							excerpt: chapter.novel_title || ''
						} );
					} );
				}

				if ( results.length === 0 ) {
					dropdown.innerHTML = '<div class="luomor-search-empty">' +
						luomorNovel.i18n.search + '</div>';
					dropdown.style.display = 'block';
					return;
				}

				results.forEach( function( result ) {
					var item = document.createElement( 'a' );
					item.href = result.link;
					item.className = 'luomor-search-item';
					item.style.cssText = 'display:block;padding:10px 15px;border-bottom:1px solid var(--wp--preset--color--muted);text-decoration:none;color:inherit;';
					item.innerHTML = '<div class="luomor-search-item-title" style="font-weight:600;font-size:14px;">' +
						escapeHtml( result.title ) + '</div>' +
						'<div class="luomor-search-item-excerpt" style="font-size:12px;color:#666;margin-top:2px;">' +
						( result.type === 'chapter' ? '[章节] ' : '[小说] ' ) +
						escapeHtml( result.excerpt ).substring( 0, 60 ) + '</div>';
					dropdown.appendChild( item );
				} );

				dropdown.style.display = 'block';
			} )
			.catch( function() {
				// 静默失败
			} );
	}

	/**
	 * 更新活动项
	 */
	function updateActiveItem( items, index ) {
		items.forEach( function( item, i ) {
			item.style.background = i === index ? 'var(--wp--preset--color--muted)' : '';
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
