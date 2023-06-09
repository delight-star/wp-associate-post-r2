import {
	Button,
	Dashicon,
	PanelBody,
	TextControl,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';
import {
	BlockControls,
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import { select } from '@wordpress/data';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';
import { edit as editIcon } from '@wordpress/icons';

function getProductSelectionURL() {
	const currentPostId = select( 'core/editor' ).getCurrentPostId();
	const initTab = window.wpapBlockConfig.initTab;
	return addQueryArgs( 'media-upload.php', {
		post_id: currentPostId,
		type: initTab,
		tab: initTab,
		TB_iframe: true,
	} );
}

function Toolbar( { isSetProduct } ) {
	if ( isSetProduct ) {
		const onClickForEditButton = () =>
			window.tb_show(
				__( 'Product Link', 'wp-associate-post-r2' ),
				getProductSelectionURL()
			);
		return (
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon={ editIcon }
						label={ __( 'Change Product', 'wp-associate-post-r2' ) }
						onClick={ onClickForEditButton }
					/>
				</ToolbarGroup>
			</BlockControls>
		);
	}
	return null;
}

function AddProductButton( { isSetProduct } ) {
	if ( ! isSetProduct ) {
		return (
			<Button
				href={ getProductSelectionURL() }
				variant="primary"
				className="thickbox"
				title={ __( 'WP Associate Post R2', 'wp-associate-post-r2' ) }
			>
				{ __( 'Select Product', 'wp-associate-post-r2' ) }
			</Button>
		);
	}
	return null;
}

function PreviewRender( { isSetProduct, attributes } ) {
	if ( isSetProduct ) {
		return (
			<ServerSideRender
				block={ metadata.name }
				attributes={ attributes }
			/>
		);
	}
	return null;
}

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const isSetProduct = !! (
		attributes.service &&
		attributes.id &&
		attributes.type
	);

	return (
		<div { ...blockProps }>
			<Toolbar isSetProduct={ isSetProduct } />
			<AddProductButton isSetProduct={ isSetProduct } />
			<PreviewRender
				isSetProduct={ isSetProduct }
				attributes={ attributes }
			/>
			<TextControl
				type="hidden"
				value="service"
				onChange={ ( value ) => setAttributes( { service: value } ) }
			/>
			<TextControl
				type="hidden"
				value="id"
				onChange={ ( value ) => setAttributes( { id: value } ) }
			/>
			<TextControl
				type="hidden"
				value="type"
				onChange={ ( value ) => setAttributes( { type: value } ) }
			/>
			<InspectorControls>
				<PanelBody
					title={ __( 'Display Settings', 'wp-associate-post-r2' ) }
				>
					<TextControl
						label={ __( 'Title', 'wp-associate-post-r2' ) }
						value={ attributes.title ?? '' }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
					/>
					<TextControl
						label={ __(
							'Search Keywords',
							'wp-associate-post-r2'
						) }
						value={ attributes.search ?? '' }
						help={ __(
							'The search keywords for Rakuten and Yahoo Shopping.',
							'wp-associate-post-r2'
						) }
						onChange={ ( value ) =>
							setAttributes( { search: value } )
						}
					/>
					<TextControl
						label={ __( 'CSS Classes', 'wp-associate-post-r2' ) }
						value={ attributes.css_class ?? '' }
						onChange={ ( value ) =>
							setAttributes( { css_class: value } )
						}
					/>
					<p>
						<Dashicon icon="help" />
						<a
							href="https://wp-ap.net/help/gutenberg-panel/"
							target="_blank"
						>
							{ __(
								'Display Settings Help',
								'wp-associate-post-r2'
							) }
						</a>
					</p>
				</PanelBody>
			</InspectorControls>
		</div>
	);
}
