/* global objectFitImages */
(function( window ) {
	var userAgent = window.navigator.userAgent.toLowerCase(),
		isIE = ( userAgent.indexOf( 'msie' ) !== -1 ),
		isIE11 = ( userAgent.indexOf( 'trident/7' ) !== -1 ),
		isEdge = ( userAgent.indexOf( 'edge' ) !== -1 );
	if ( isIE || isIE11 || isEdge ) {
		objectFitImages();
	}
})( window );