( function( tinymce, $ ) {
    tinymce.PluginManager.add( 'wpap', function( editor ) {
        editor.on( 'BeforeSetContent', function( event ) {
            if ( event.content ) {
                if ( event.content.indexOf( '[wpap ' ) !== -1 ) {
                    event.content = event.content.replace( /\[wpap([^\]]*)\]/g, function( shortcode, attr ){
                    	attr = attr.replace( /(<span\s(?:[^>]*?)data-mce-type="bookmark"(?:[^>]*?)>(?:.*?)<\/span>)/gi, '' );
                    	
                    	var params,
							paramsStr = '',
                    		service = attr.match( /service="(.*?)"/ ),
                        	type = attr.match( /type="(.*?)"/ ),
                        	id = attr.match( /id="(.*?)"/ ),
                        	title = attr.match( /title="(.*?)"/ ),
							search = attr.match( /search="(.*?)"/ ),
							tplClass = attr.match( /class="(.*?)"/ );

                        if ( service === null || type === null || id === null || title === null ) {
                        	return shortcode;
						}

                        params = {
                            service: service[1],
                            type: type[1],
                            id: id[1],
                            title: title[1]
                        };
						if ( search !== null ) {
							params.search = search[1];
						}
						if ( tplClass !== null ) {
							// Because of reserved words.
							params['class'] = tplClass[1];
						}

                        for ( var key in params ) {
                            paramsStr += 'data-wpap-' + key + '="' + encodeURIComponent( params [ key ] ) + '"';
                        }

                        if ( params.type !== 'text' ) {
                            return '<img src="' + tinymce.Env.transparentSrc + '" ' + paramsStr + ' class="mce-wp-associate-post-r2-tag" title="WP Associate Post Tag" data-mce-resize="false" data-mce-placeholder="1" />';
                        } else {
                            return '<span ' + paramsStr + '>' + params.title + '</span>';
						}
                    });
                }
            }
        });

		editor.on( 'PostProcess', function( event ) {
			if ( event.content ) {
				event.content = event.content.replace( /<img[^>]+>|<span (.*?)>(.*?)<\/span>/g, function( html ) {
					var $html = $(html),
						service = $html.attr('data-wpap-service'),
						type = $html.attr('data-wpap-type'),
						id = $html.attr('data-wpap-id'),
						title = $html.attr('data-wpap-title');

					if ( typeof(service) === 'undefined' || typeof(type) === 'undefined' || typeof(id) === 'undefined' || typeof(title) === 'undefined' ) {
						return html;
					}

					var params, shortcode,
						tag = $html.prop('tagName').toLowerCase(),
						tplClass = $html.attr('data-wpap-class'),
						search = $html.attr('data-wpap-search');

					if ( tag === 'span' ) {
						title = $html.text();
					}

					params = {
						service: service,
						type: type,
						id: id,
						title: title
					};
					if ( typeof(search) !== 'undefined' ) {
						params.search = search;
					}
					if ( typeof(tplClass) !== 'undefined' ) {
						// Because of reserved words.
						params['class'] = tplClass;
					}

					shortcode = '[wpap';
					for ( var key in params ) {
						shortcode += ' ' + key + '="' + decodeURIComponent( params [ key ] ) + '"';
					}
					shortcode += ']';

					return shortcode;
				});
			}
		});
    });

}( window.tinymce, window.jQuery ));
