/* global gtag, ga, _gaq */
(function( $ ) {
	$( 'a[data-click-tracking]' ).click(function() {
		var trackingLabel = $( this ).attr( 'data-click-tracking' );
		if ( typeof gtag !== 'undefined' ) {
			gtag('event', 'click', {
				'event_category': 'WP Associate POST',
				'event_label': trackingLabel
			});
		} else if ( typeof ga !== 'undefined' ) {
			ga( 'send', 'event', 'WP Associate POST', 'click', trackingLabel );
		} else if ( typeof _gaq !== 'undefined' ) {
			_gaq.push([ '_trackEvent', 'WP Associate POST', 'click', trackingLabel ]);
		}
	});
})( jQuery );