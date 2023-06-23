/* global wpapOption */
( function ( $, wpapOption ) {
	$( document ).ready( function () {
		var __ = wp.i18n.__;

		$( '#cache_clear' ).click( function () {
			var $this = $( this );
			$this.before(
				'<div class="notice notice-warning inline" id="wpap-cache-clear-loading"><p>' +
					'<img src="' +
					wpapOption.loadingImgURL +
					'"> ' +
					__(
						'Clearing the cache... Please wait a moment.',
						'wp-associate-post-r2'
					) +
					'</p></div>'
			);
			$this.prop( 'disabled', true );
			$.ajax( {
				type: 'POST',
				dataType: 'json',
				url: wpapOption.ajaxURL,
				data: {
					action: 'wpap-cache-clear',
					nonce: wpapOption.nonce,
				},
				cache: false,
			} )
				.done( function ( response ) {
					if ( response.status === 'success' ) {
						$this.before(
							'<div class="notice notice-success inline"><p>' +
								response.message +
								'</p></div>'
						);
					} else {
						$this.before(
							'<div class="notice notice-error inline"><p>' +
								response.message +
								'</p></div>'
						);
						$this.prop( 'disabled', false );
					}
				} )
				.fail( function () {
					$this.before(
						'<div class="notice notice-error inline"><p>' +
							__(
								'A communication error occurred. Please try again in a moment.',
								'wp-associate-post-r2'
							) +
							'</p></div>'
					);
					$this.prop( 'disabled', false );
				} )
				.always( function () {
					$( '#wpap-cache-clear-loading' ).remove();
				} );
		} );

		$( '#import_form' ).submit( function () {
			var file = $( 'input[name=option_import_file]' )[ 0 ].files[ 0 ];
			if ( ! file ) {
				window.alert(
					__( 'File not selected.', 'wp-associate-post-r2' )
				);
				return false;
			}
			return window.confirm(
				__(
					'The setting will be overridden. If you select the wrong file, the setting may be corrupt or disappear, so please verify you have selected the correct file. Are you sure you want to import?',
					'wp-associate-post-r2'
				)
			);
		} );
	} );
} )( jQuery, wpapOption );
