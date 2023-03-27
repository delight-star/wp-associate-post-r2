/* global wpapSearch */
(function( $, wpapSearch ) {

	var $subwindow, $subwindowOverlay,
		parent = window.parent;

	function openPreview() {
		var width = ( $(window).width() - $subwindow.width() ) / 2,
			height = ( $(window).height() - $subwindow.height() ) / 2;
		$subwindow
			.css({ 'left': width, 'top': height })
			.add( $subwindowOverlay )
				.fadeIn( 150 );
	}

	function closePreview() {
		$subwindow.add( $subwindowOverlay )
			.fadeOut( 150, function() {
				$subwindow.css({ 'left': '', 'top': '' });
			});
	}

	function insertShortcode( params ) {
		var paramsStr = '';
		for ( var key in params ) {
			paramsStr += ' ' + key + '="' + params[ key ] + '"';
		}
		var shortcode = '[wpap' + paramsStr + ']',
			wpActiveEditor = parent.wpActiveEditor,
			hasTinymce = ( typeof( parent.tinymce ) !== 'undefined' ),
			editor = hasTinymce ? parent.tinymce.get( wpActiveEditor ) : false,
			hasQuicktags = ( typeof( parent.QTags ) !== 'undefined' ),
			isGutenberg = !! wpapSearch.isGutenberg;

		if ( isGutenberg ) {
			var wpData = parent.wp.data,
				wpBlocks = parent.wp.blocks,
				selectedBlockId = wpData.select( 'core/editor' ).getSelectedBlockClientId();

			wpData.dispatch( 'core/editor' ).replaceBlock(
				selectedBlockId,
				wpBlocks.createBlock(
					'wp-associate-post-r2/product',
					params
				)
			);
		} else if ( editor && ! editor.isHidden() ) {
			editor.execCommand( 'mceInsertContent', false, shortcode );
		} else if ( hasQuicktags ) {
			parent.QTags.insertContent( shortcode + '\n' );
		} else {
			parent.document.getElementById( wpActiveEditor ).value += shortcode;
		}
	}

	function closeThickbox() {
		parent.tb_remove();
	}

	function selectorEscape( val ) {
		return val.replace( /[ !"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~]/g, '\\$&' );
	}

	function encodeUrl( str ) {
		return encodeURIComponent( str ).replace( /[!'()*]/g, function( c ) {
			return '%' + c.charCodeAt(0).toString(16);
		});
	}

	function replaceShortcodeParameter( str ) {
		return str.replace( /\[/g, '［' ).replace( /\]/g, '］' );
	}

	function createRakutenUrl( affiliateId, keyword ) {
		var searchUrl = 'https://search.rakuten.co.jp/search/mall/' + keyword + '/',
			encodeSearchUrl = encodeUrl( searchUrl );
		return 'https://hb.afl.rakuten.co.jp/hgc/' + affiliateId + '/?pc=' + encodeSearchUrl + '&m=' + encodeSearchUrl;
	}

	function createYahooUrl( keyword, sid, pid ) {
		var searchUrl = 'https://shopping.yahoo.co.jp/search?p=' + keyword + '&view=list',
			encodeSearchUrl = encodeUrl( searchUrl );
		return '//ck.jp.ap.valuecommerce.com/servlet/referral?sid=' + sid + '&pid=' + pid + '&vc_url=' + encodeSearchUrl;
	}

	$( document ).ready(function() {

		setTimeout(function() {
			$('#wpap-search-keyword').focus();
		}, 0 );

		$.pjax({
			area: '#media-upload-header, .wpap-search',
			link: 'a:not([target])',
			form: 'form:not([method])'
		});
		$( document ).bind( 'pjax:fetch', function() {
			$( '#wpap-overlay-loading' ).fadeIn( 150 );
		});
		$( document ).bind( 'pjax:render', function() {
			$( '#wpap-overlay-loading' ).fadeOut( 150 );
		});

		$( document ).on( 'click', '.wpap-search .result-item tr', function( event ) {
			event.preventDefault();
			var id = $( this ).attr( 'id' ).replace( /(item_)/g ,'' ),
				title = $( this ).attr( 'data-title' );
			$( 'input[name="id"]' ).val( id );
			$( 'input[name="title"]' ).val( title );
			$( 'input[name="tpl"]:first' ).prop( 'checked', true ).trigger( 'change' );
			$( 'input[name="with_search_keyword"]' ).val( title );
			$subwindow = $( '#wpap-admin-subwindow' );
			$subwindowOverlay = $( '#wpap-overlay-subwindow' );
			openPreview();
		});

		$( document ).on( 'change', 'input[name="tpl"]', function() {
			var id = $( 'input[name="id"]' ).val(),
				tpl = $( 'input[name="tpl"]:checked' ).val(),
				selector = selectorEscape( id + '_' + tpl );
			$( '.display-preview-html' ).hide();
			$( '.' + selector ).show();
		});

		$( document ).on( 'click', '.display-preview .wpap-link-rakuten', function() {
			return false;
		});

		$( document ).on( 'click', '.display-preview .wpap-link-yahoo', function() {
			return false;
		});

		$( document ).on( 'click', '#wpap-admin-rakuten-ichiba-search', function() {
			var searchKeyword = $( 'input[name="with_search_keyword"]' ).val(),
				affiliateId =  $( 'input[name="rakuten_affiliate_id"]' ).val(),
				affiliateUrl = createRakutenUrl( affiliateId, searchKeyword );
			window.open( affiliateUrl, '_blank' );
		});

		$( document ).on( 'click', '#wpap-admin-yahoo-search', function() {
			var searchKeyword = $( 'input[name="with_search_keyword"]' ).val(),
				sid =  $( 'input[name="yahoo_vc_sid"]' ).val(),
				pid =  $( 'input[name="yahoo_vc_pid"]' ).val(),
				affiliateUrl = createYahooUrl( searchKeyword, sid, pid );
			window.open( affiliateUrl, '_blank' );
		});

		$( document ).on( 'click', '.wpap-insert-button', function() {
			var service = $( 'input[name="service"]' ).val(),
				template = $( 'input[name="tpl"]:checked' ).val(),
				id = $( 'input[name="id"]' ).val(),
				title = $( 'input[name="title"]' ).val(),
				replaceTitle = replaceShortcodeParameter( title ),
				$withSearchKeyword = $( 'input[name="with_search_keyword"]' ),
				params = {
					service: service,
					type: template,
					id: id,
					title: replaceTitle
				};

			if ( $withSearchKeyword.is( '*' ) ) {
				var searchKeyword = $withSearchKeyword.val(),
					replaceSearchKeyword = replaceShortcodeParameter( searchKeyword );
				if ( title !== searchKeyword ) {
					params.search = replaceSearchKeyword;
				}
			}

			insertShortcode( params );

			if ( $( this ).is( '#wpap-admin-subwindow-insert-continue' ) ) {
				closePreview();
			} else if ( $( this ).is( '#wpap-admin-subwindow-insert' ) ) {
				closeThickbox();
			}
		});

		$( document ).on( 'click', '#wpap-admin-subwindow-cancel', function() {
			closePreview();
		});

		$( document ).on( 'mouseenter', '[data-tooltip]', function() {
			var title = $( this ).attr( 'data-tooltip' );
			$( this ).append( '<span class="tooltip_hover"><span class="tooltip_hover_body">' + title + '</span></span>' );
		}).on( 'mouseleave', '[data-tooltip]', function() {
			$( this ).find( 'span.tooltip_hover' )
				.fadeOut()
				.remove();
		});

		$( document ).on( 'mouseenter', '.wpap-link-rakuten, .wpap-link-yahoo', function() {
			$( this ).closest( '.display-preview' )
				.append( '<div class="wpap-balloon"><p>' + wpapSearch.i18n.balloonRakutenAndYahooLinks + '</p></div>' );
		}).on( 'mouseleave', '.wpap-link-rakuten, .wpap-link-yahoo', function() {
			$( this ).closest( '.display-preview' ).find( '.wpap-balloon' )
				.fadeOut()
				.remove();
		});

		$( document ).keyup(function( e ) {
			// Press Esc key
			if ( e.keyCode === 27 ) {
				if ( $( '#wpap-admin-subwindow' ).is( ':visible' ) ) {
					closePreview();
				} else {
					closeThickbox();
				}
			}
		});

	});
})( jQuery, wpapSearch );