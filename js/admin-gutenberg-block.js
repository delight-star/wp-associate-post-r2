(function( wp, config ) {
    var blocks = wp.blocks,
        element = wp.element,
        components = wp.components,
        editor = wp.editor,
        i18n = wp.i18n;

    var TextControl = components.TextControl,
        el = element.createElement;

    var blockIcon = el(
        'svg',
        { viewBox: '0 0 54.62 54.61' },
        el( 'path', { d: 'M0,27.31A27.31,27.31,0,1,1,27.32,54.62h0A27.31,27.31,0,0,1,0,27.31Z', fill: '#777c81', 'fill-rule': 'evenodd' }),
        el( 'path', { d: 'M25.47,34.23a1.43,1.43,0,0,1,.21,1.31l-1,3.74H20.83l1-3.74a2.67,2.67,0,0,1,2.43-1.86A1.42,1.42,0,0,1,25.47,34.23Zm.44,14.39,3.6-13.07a2.9,2.9,0,0,0-.4-2.63,2.81,2.81,0,0,0-2.4-1.1H22.86a4.78,4.78,0,0,0-3,1.1A5,5,0,0,0,18,35.55L14.41,48.62h3.83l2-7.47h3.85l-2,7.47Z', fill: '#fff', 'fill-rule': 'evenodd' }),
        el( 'path', { d: 'M38.73,37.41a2.51,2.51,0,0,1-.94,1.32,2.41,2.41,0,0,1-1.5.55H34.36l1.54-5.6h1.93A1.41,1.41,0,0,1,39.32,35a1.47,1.47,0,0,1-.08.55Zm-4.88,3.73H37.7a5.4,5.4,0,0,0,4.86-3.73l.51-1.87a2.9,2.9,0,0,0-.4-2.63,2.81,2.81,0,0,0-2.4-1.1H32.59L28,48.62h3.83Z', fill: '#fff', 'fill-rule': 'evenodd' }),
        el( 'path', { d: 'M26.42,24.31a8.45,8.45,0,0,0,3-4.43L32.07,9.62H28.46L25.09,22.51a2.43,2.43,0,0,1-.86,1.29,2.18,2.18,0,0,1-1.4.54H21L24.88,9.62H21.29L17.92,22.51a2.47,2.47,0,0,1-.86,1.29,2.26,2.26,0,0,1-1.42.54h-1.8l3.53-13.49.32-1.23H6.52c-.34.4-.67.81-1,1.23h8.25l-4,15.34h4.64a6,6,0,0,0,2.7-.59l-.15.59H21.6A7.59,7.59,0,0,0,26.42,24.31ZM40.61,12a1.42,1.42,0,0,1,.19,1.29l-.48,1.84a2.42,2.42,0,0,1-.87,1.3A2.17,2.17,0,0,1,38,17h-1.8l1.44-5.52h1.8A1.32,1.32,0,0,1,40.61,12Zm3.3,3.13.48-1.84A3,3,0,0,0,44,10.69,2.55,2.55,0,0,0,41.77,9.6H34.58L29.24,30H54.48c0-.41.08-.82.1-1.23H33.15l2.61-10h3.6a4.41,4.41,0,0,0,2.81-1.08A4.84,4.84,0,0,0,43.91,15.12Z', fill: '#fff', 'fill-rule': 'evenodd' })
    );
    var initTab = config.initTab;

    blocks.registerBlockType( 'wp-associate-post-r2/product', {
        title: i18n.__( 'Product Link', 'wp-associate-post-r2' ),
        icon: blockIcon,
        category: 'common',
        attributes: {
            service: { type: 'string' },
            id: { type: 'string' },
            type: { type: 'string' },
            title: { type: 'string' },
            css_class: { type: 'string' },
            search: { type: 'string' }
        },
        supports: {
            customClassName: false,
            className: false,
            html: false,
            alignWide: false
        },
        edit: function( props ) {
            var service = props.attributes.service,
                id = props.attributes.id,
                type = props.attributes.type,
                title = props.attributes.title,
                search = props.attributes.search,
                css_class = props.attributes.css_class;

            function getCurrentPostId() {
                return wp.data.select( 'core/editor' ).getCurrentPostId();
            }

            function updateService( content ) {
                props.setAttributes( { service: content } );
            }

            function updateId( content ) {
                props.setAttributes( { id: content } );
            }

            function updateType( content ) {
                props.setAttributes( { type: content } );
            }

            function updateTitle( content ) {
                props.setAttributes( { title: content } );
            }

            function updateSearch( content ) {
                props.setAttributes( { search: content } );
            }

            function updateCssClass( content ) {
                props.setAttributes( { css_class: content } );
            }

            function clickEditButton() {
                tb_show(
                    i18n.__( 'Product Link', 'wp-associate-post-r2' ),
                    wp.url.addQueryArgs(
                        'media-upload.php',
                        {
                            post_id: getCurrentPostId(),
                            type: initTab,
                            tab: initTab,
                            TB_iframe: true
                        }
                    )
                );
            }

            var toolbarButton, selectButton, previewRender;
            if ( service && id && type ) {
                toolbarButton = el(
                    editor.BlockControls,
                    null,
                    el(
                        components.Toolbar,
                        null,
                        el(
                            components.IconButton,
                            {
                                label: i18n.__( 'Change Product', 'wp-associate-post-r2' ),
                                icon: 'edit',
                                className: 'components-icon-button components-toolbar__control',
                                onClick: clickEditButton
                            }
                        )
                    )
                );
                selectButton = null;
                previewRender = el(
                    components.ServerSideRender,
                    {
                        block: 'wp-associate-post-r2/product',
                        attributes: props.attributes
                    }
                );
            } else {
                toolbarButton = null;
                selectButton = el(
                    components.Button, {
                        isDefault: true,
                        className: 'thickbox',
                        href: wp.url.addQueryArgs( 'media-upload.php', {
                            post_id: getCurrentPostId(),
                            type: initTab,
                            tab: initTab,
                            TB_iframe: true
                        } ),
                        title: i18n.__( 'WP Associate Post R2', 'wp-associate-post-r2' )
                    },
                    i18n.__( 'Select Product', 'wp-associate-post-r2' )
                );
                previewRender = null;
            }

            return el(
                element.Fragment,
                null,
                toolbarButton,
                selectButton,
                previewRender,
                el(
                    TextControl,
                    {
                        type: 'hidden',
                        value: service,
                        onChange: updateService
                    }
                ),
                el(
                    TextControl,
                    {
                        type: 'hidden',
                        value: id,
                        onChange: updateId
                    }
                ),
                el(
                    TextControl,
                    {
                        type: 'hidden',
                        value: type,
                        onChange: updateType
                    }
                ),
                el(
					editor.InspectorControls,
                    {},
                    el(
                        components.PanelBody,
                        { title: i18n.__( 'Display Settings', 'wp-associate-post-r2' ) },
                        el(
                            TextControl,
                            {
                                label: i18n.__( 'Title', 'wp-associate-post-r2' ),
                                value: title !== undefined ? title : '',
                                onChange: updateTitle
                            }
                        ),
                        el(
                            TextControl,
                            {
                                label: i18n.__( 'Search Keywords', 'wp-associate-post-r2' ),
                                value: search !== undefined ? search : '',
                                help: i18n.__( 'The search keywords for Rakuten and Yahoo Shopping.', 'wp-associate-post-r2' ),
                                onChange: updateSearch
                            }
                        ),
                        el(
                            TextControl,
                            {
                                label: i18n.__( 'CSS Classes', 'wp-associate-post-r2' ),
                                value: css_class !== undefined ? css_class : '',
                                onChange: updateCssClass
                            }
                        ),
                        el(
                            'p',
                            null,
                            el(
                                'span',
                                { 'class': 'dashicons dashicons-editor-help' }
                            ),
                            el(
                                'a',
                                {
                                    href: 'https://wp-ap.net/help/gutenberg-panel/',
                                    target: '_blank'
                                },
                                i18n.__( 'Display Settings Help', 'wp-associate-post-r2' )
                            )
                        )
                    )
                )
            );
        },
        save: function() {
            return null;
        },
        transforms: {
            from: [
                {
                    type: 'shortcode',
                    tag: 'wpap',
                    attributes: {
                        service: {
                            type: 'string',
                            shortcode: function( attributes ) {
                                return attributes.named.service || '';
                            }
                        },
                        id: {
                            type: 'string',
                            shortcode: function( attributes ) {
                                return attributes.named.id || '';
                            }
                        },
                        type: {
                            type: 'string',
                            shortcode: function( attributes ) {
                                return attributes.named.type || '';
                            }
                        },
                        title: {
                            type: 'string',
                            shortcode: function( attributes ) {
                                return attributes.named.title || '';
                            }
                        },
                        css_class: {
                            type: 'string',
                            shortcode: function( attributes ) {
                                return attributes.named.css_class || '';
                            }
                        },
                        search: {
                            type: 'string',
                            shortcode: function( attributes ) {
                                return attributes.named.search || '';
                            }
                        }
                    }
                }
            ]
        }
    } );
})( window.wp, window.wpapBlockConfig );