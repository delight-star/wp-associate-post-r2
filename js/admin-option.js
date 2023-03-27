/* global wpapOption */
(function( $, wpapOption ) {

	$( document ).ready(function() {

		$( '#cache_clear' ).click(function() {
			var $this = $( this );
			$this.before( '<div class="notice notice-warning inline" id="wpap-cache-clear-loading"><p><img src="' +
				wpapOption.loadingImgURL + '"> ' + wpapOption.i18n.clearingCache + '</p></div>' );
			$this.prop( 'disabled', true );
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: wpapOption.ajaxURL,
				data: {
					'action': 'wpap-cache-clear',
					'nonce' : wpapOption.nonce
				},
				cache: false
			}).done(function( response ) {
				if ( response.status === 'success' ) {
					$this.before( '<div class="notice notice-success inline"><p>' + response.message + '</p></div>' );
				} else {
					$this.before( '<div class="notice notice-error inline"><p>' + response.message + '</p></div>' );
					$this.prop( 'disabled', false );
				}
			}).fail(function() {
				$this.before( '<div class="notice notice-error inline"><p>' + wpapOption.i18n.communicationError + '</p></div>' );
				$this.prop( 'disabled', false );
			}).always(function() {
				$( '#wpap-cache-clear-loading' ).remove();
			});
		});

		$( '#import_form' ).submit(function() {
			var file = $( 'input[name=option_import_file]' )[0].files[0];
			if ( ! file ) {
				window.alert( wpapOption.i18n.importFileNotSelected );
				return false;
			}
			return window.confirm( wpapOption.i18n.importConfirm );
		});

	});
})( jQuery, wpapOption );