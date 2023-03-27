<?php

namespace WP_Associate_Post_R2;

class Main {

	public $option;

	public $cache    = null;
	public $template = null;
	public $amazon   = null;
	private $bitly   = null;
	public $rakuten  = null;
	public $yahoo    = null;
	public $moshimo  = null;

	public $service = array();

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		$this->option = get_option( WPAP_ID, array() );
		$this->cache  = new Cache();

		if ( ! empty( $this->option['amazon_enable'] ) && ! empty( $this->option['amazon_access_key_id'] )
			&& ! empty( $this->option['amazon_secret_access_key'] ) && ! empty( $this->option['amazon_tracking_id'] )
		) {
			$this->amazon            = new Amazon( $this->option['amazon_access_key_id'], $this->option['amazon_secret_access_key'], $this->option['amazon_tracking_id'] );
			$this->service['Amazon'] = 'amazon';

			if ( ! empty( $this->option['bitly_enable'] ) && ! empty( $this->option['bitly_access_token'] ) ) {
				$this->bitly = new Bitly( $this->option['bitly_access_token'] );
			}
		}

		if ( ! empty( $this->option['rakuten_enable'] ) && ! empty( $this->option['rakuten_application_id'] ) && ! empty( $this->option['rakuten_affiliate_id'] ) ) {
			$this->rakuten                  = new Rakuten( $this->option['rakuten_application_id'], $this->option['rakuten_affiliate_id'] );
			$this->service['RakutenIchiba'] = 'rakuten-ichiba';
			$this->service['RakutenBooks']  = 'rakuten-books';
		}

		$rakuten_enable = isset( $this->service['RakutenIchiba'], $this->service['RakutenBooks'] );
		$yahoo_enable = ( ! empty( $this->option['yahoo_enable'] ) && ! empty( $this->option['yahoo_vc_sid'] ) && ! empty( $this->option['yahoo_vc_pid'] ) );
		if ( isset( $this->service['Amazon'] ) && ( $rakuten_enable || $yahoo_enable ) ) {
			$this->service['With'] = 'with';
			if ( $yahoo_enable ) {
				$this->yahoo = new Yahoo( $this->option['yahoo_vc_sid'], $this->option['yahoo_vc_pid'] );
			}
		}

		if ( ! empty( $this->option['moshimo_enable'] ) ) {
			$this->moshimo = new Moshimo();
			if ( ! is_null( $this->amazon ) && ! empty( $this->option['moshimo_amazon_aid'] ) ) {
				$this->moshimo->set_aid( 'amazon', $this->option['moshimo_amazon_aid'] );
			}
			if ( ! is_null( $this->rakuten ) && ! empty( $this->option['moshimo_rakuten_aid'] ) && isset( $this->service['With'] ) ) {
				$this->moshimo->set_aid( 'rakuten', $this->option['moshimo_rakuten_aid'] );
			}
 			if ( ! is_null( $this->yahoo ) && ! empty( $this->option['moshimo_yahoo_aid'] ) && isset( $this->service['With'] ) ) {
				$this->moshimo->set_aid( 'yahoo', $this->option['moshimo_yahoo_aid'] );
			}
		}

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}

		if ( function_exists( 'register_block_type' ) && ! is_null( $this->get_search_tab_id() ) ) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets') );
			register_block_type('wp-associate-post-r2/product', array(
				'attributes' => array(
					'service' => array('type' => 'string'),
					'id' => array('type' => 'string'),
					'type' => array('type' => 'string'),
					'title' => array('type' => 'string'),
					'search' => array('type' => 'string'),
					'css_class' => array('type' => 'string')
				),
				'render_callback' => array($this, 'gutenberg_callback'),
			));
		}
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'wp-associate-post-r2' );
	}

	public function init() {
		$this->template = new Template();
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_shortcode( WPAP_ID_ABBR, array( $this, 'shortcode' ) );
		$this->cache->clean();
	}

	public function admin_init() {
		add_action( 'wp_ajax_wpap-cache-clear', array( $this, 'ajax_cache_clear' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'media_buttons', array( $this, 'media_buttons' ), 11 );
		add_action( 'media_upload_' . WPAP_ID_ABBR . '_amazon', array( $this, 'media_upload_item_search' ) );
		add_action( 'media_upload_' . WPAP_ID_ABBR . '_rakuten_ichiba', array( $this, 'media_upload_item_search' ) );
		add_action( 'media_upload_' . WPAP_ID_ABBR . '_rakuten_books', array( $this, 'media_upload_item_search' ) );
		add_action( 'media_upload_' . WPAP_ID_ABBR . '_with', array( $this, 'media_upload_item_search' ) );
		add_filter( 'media_upload_tabs', array( $this, 'media_upload_tabs' ) );

		if ( apply_filters( 'wpap_shortcode_preview', true ) ) {
			add_filter( 'mce_css', array( $this, 'add_tinymce_style' ) );
			if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
				add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
			}
		}

		register_setting( WPAP_ID, WPAP_ID, array( $this, 'validate_option' ) );
	}

	public function enqueue() {
		$option_skin = isset( $this->option['skin_css'] ) ? $this->option['skin_css'] : '';
		if ( preg_match( '/^(wpap-)[a-zA-Z0-9-]+(.css)$/', $option_skin ) && locate_template( (array) $option_skin ) !== '' ) {
			wp_enqueue_style( WPAP_ID, get_stylesheet_directory_uri() . '/' . $option_skin, array(), null );
		} else {
			switch ( $option_skin ) {
				case 'default-2':
					$default_skin_name = 'square';
					break;
				case 'default-3':
					$default_skin_name = 'circle';
					break;
				case 'default-4':
					$default_skin_name = 'weave';
					break;
				case 'default-5':
					$default_skin_name = 'shadow';
					break;
				default:
					$default_skin_name = 'standard';
					break;
			}
			wp_enqueue_style( WPAP_ID, WPAP_PLUGIN_URL . 'css/skin-' . $default_skin_name . '.css', array(), WPAP_VERSION );
		}

		$option_click_tracking = isset( $this->option['analytics_click_tracking'] ) ? $this->option['analytics_click_tracking'] : '';
		if ( 1 == $option_click_tracking ) {
			wp_enqueue_script( 'wpap-click-tracking', WPAP_PLUGIN_URL . 'js/click-tracking.js', array( 'jquery' ), null, true );
		}
		wp_enqueue_script( 'object-fit-images', WPAP_PLUGIN_URL . 'js/ofi.min.js', array(), null, true );
		wp_enqueue_script( 'wpap-common', WPAP_PLUGIN_URL . 'js/common.js', array( 'object-fit-images' ), null, true );

		if ( is_user_logged_in() && current_user_can( 'publish_posts' ) ) {
			wp_enqueue_style( 'wpap-admin-front', WPAP_PLUGIN_URL . 'css/admin-front.css', array(), WPAP_VERSION );
		}
	}

	public function admin_enqueue( $hook ) {
		if ( 'settings_page_' . WPAP_ID === $hook ) {
			wp_enqueue_style( 'wpap-admin-option', WPAP_PLUGIN_URL . 'css/admin-option.css', array(), WPAP_VERSION );
			wp_enqueue_script( 'wpap-admin-option', WPAP_PLUGIN_URL . 'js/admin-option.js', array( 'jquery' ), WPAP_VERSION, true );
			wp_localize_script( 'wpap-admin-option', 'wpapOption', array(
				'ajaxURL'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'wpap_cache_clear' ),
				'loadingImgURL' => WPAP_PLUGIN_URL . 'images/loading_mini.gif',
				'i18n'          => array(
					'clearingCache'         => __( 'Clearing the cache... Please wait a moment.', 'wp-associate-post-r2' ),
					'communicationError'    => __( 'A communication error occurred. Please try again in a moment.', 'wp-associate-post-r2' ),
					'importFileNotSelected' => __( 'File not selected.', 'wp-associate-post-r2' ),
					'importConfirm'         => __( 'The setting will be overridden. If you select the wrong file, the setting may be corrupt or disappear, so please verify you have selected the correct file. Are you sure you want to import?', 'wp-associate-post-r2' ),
				),
			) );
		} elseif ( 'media-upload-popup' === $hook && isset( $_GET['tab'] ) && preg_match( '/^(' . WPAP_ID_ABBR . '_)/', $_GET['tab'] ) ) {
			wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css', array(), null );
			wp_enqueue_style( 'wpap-admin-search', WPAP_PLUGIN_URL . 'css/admin-search.css', array( 'font-awesome' ), WPAP_VERSION );
			wp_enqueue_script( 'jquery-pjax', WPAP_PLUGIN_URL . 'js/jquery.pjax.min.js', array( 'jquery' ), null, true );
			wp_enqueue_script( 'wpap-admin-search', WPAP_PLUGIN_URL . 'js/admin-search.js', array( 'jquery', 'jquery-pjax' ), WPAP_VERSION, true );
			wp_localize_script( 'wpap-admin-search', 'wpapSearch', array(
				'isGutenberg' => $this->is_gutenberg_active(),
				'i18n' => array(
					'balloonRakutenAndYahooLinks' => __( 'In order to improve operating speed, Rakuten and Yahoo links can not be clicked from the preview screen.', 'wp-associate-post-r2' ),
					),
				) );
			$this->enqueue();
		} elseif ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_style( 'wpap-admin-post', WPAP_PLUGIN_URL . 'css/admin-post.css', array(), WPAP_VERSION );
		}
	}

	public function enqueue_block_editor_assets() {
		add_thickbox();
		$this->enqueue();
		wp_enqueue_script( 'wpap-admin-gutenberg-block', WPAP_PLUGIN_URL . 'js/admin-gutenberg-block.js', array( 'wp-editor', 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components' ) );
		wp_localize_script( 'wpap-admin-gutenberg-block', 'wpapBlockConfig', array(
			'initTab' => $this->get_search_tab_id(),
		) );
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'wpap-admin-gutenberg-block', 'wp-associate-post-r2' );
		}
	}

	public function gutenberg_callback($attributes) {
		if( isset($attributes['css_class']) ) {
			$attributes['class'] = $attributes['css_class'];
		}
		unset($attributes['css_class']);
		$result = $this->shortcode($attributes);
		if($result !== ''){
			return $result;
		}
		return ' ';
	}

	public function shortcode( $atts = array() ) {
		$atts = shortcode_atts( array(
			'service' => 'with',
			'id'      => null,
			'type'    => null,
			'title'   => null,
			'class'   => null,
			'search'  => null,
		), $atts );

		if ( ! in_array( $atts['service'], $this->service, true ) || empty( $atts['id'] ) ) {
			return '';
		}

		$is_with = ( isset( $this->service['With'] ) && $atts['service'] === $this->service['With'] );
		if ( $is_with ) {
			$atts['service'] = 'amazon';
		}
		$is_amazon         = ( isset( $this->service['Amazon'] ) && $atts['service'] === $this->service['Amazon'] );
		$is_rakuten_ichiba = ( isset( $this->service['RakutenIchiba'] ) && $atts['service'] === $this->service['RakutenIchiba'] );
		$is_rakuten_books  = ( isset( $this->service['RakutenBooks'] ) && $atts['service'] === $this->service['RakutenBooks'] );

		$amazon_tracking_id = str_replace( '-22', '', $this->option['amazon_tracking_id'] );
		$cache_key = $is_amazon ? 'amazon_' . $amazon_tracking_id . '_' . $atts['id'] : $atts['service'] . '_' . $atts['id'];
		$cached_data = $this->cache->get( $cache_key );
		$data       = null;
		if ( false !== $cached_data ) {
			$data = $cached_data;
		} else {
			if ( $is_amazon ) {
				$data = $this->amazon->item_lookup( $atts['id'] );
			} elseif ( $is_rakuten_ichiba ) {
				$data = $this->rakuten->ichiba_item_lookup( $atts['id'] );
			} elseif ( $is_rakuten_books ) {
				$data = $this->rakuten->books_item_lookup( $atts['id'] );
			}

			if ( isset( $data ) && ! is_wp_error( $data ) ) {
				$this->cache->save( $cache_key, $data );
			} else {
				return '';
			}
		}

		$moshimo_amazon_enable = ! is_null( $this->moshimo ) && $this->moshimo->isset_aid( 'amazon' );
		if ( 'amazon' == $data['Service'] && $moshimo_amazon_enable ) {
			$data['URL'] = $this->moshimo->get_url( 'amazon', $this->moshimo->removal_get_parameters( $data['URL'] ) );
		}

		if ( $is_with ) {
			$with_rakuten_enable = isset( $this->service['RakutenIchiba'], $this->service['RakutenBooks'] );
			$with_yahoo_enable   = ! is_null( $this->yahoo );
			$with_search_keyword = ( is_null( $atts['search'] ) || '' === $atts['search'] ) ? $data['Title'] : $atts['search'];

			if ( $with_rakuten_enable ) {
			    $moshimo_rakuten_enable = ! is_null( $this->moshimo ) && $this->moshimo->isset_aid( 'rakuten' );
				if ( ! $moshimo_rakuten_enable && Amazon::is_books( $data['ProductGroup'] ) && isset( $data['ISBNJAN'] ) ) {
					$with_rakuten_data = $this->cache->get( $this->service['RakutenBooks'] . '_' . $data['ISBNJAN'] );
					if ( false === $with_rakuten_data ) {
						$with_rakuten_data = $this->rakuten->books_item_lookup( $data['ISBNJAN'] );
						if ( ! is_wp_error( $with_rakuten_data ) ) {
							$this->cache->save( $this->service['RakutenBooks'] . '_' . $data['ISBNJAN'], $with_rakuten_data );
						}
					}
				}
				if ( isset( $with_rakuten_data ) && ! is_wp_error( $with_rakuten_data ) ) {
					$data['RakutenType'] = 'books';
					$data['RakutenURL']  = $with_rakuten_data['URL'];
				} else {
					$data['RakutenType'] = 'ichiba';
					$data['RakutenURL']  = $this->rakuten->get_search_url( $with_search_keyword );
					$data['Search']      = $with_search_keyword;
					if ( $moshimo_rakuten_enable ) {
						$rakuten_search_url = $this->rakuten->get_search_url( $with_search_keyword, false );
						$data['RakutenURL'] = $this->moshimo->get_url( 'rakuten', $rakuten_search_url );
					}
				}
			}
			if ( $with_yahoo_enable ) {
				$data['YahooURL'] = $this->yahoo->get_search_url( $with_search_keyword );
				$data['Search']   = $with_search_keyword;
				if ( ! is_null( $this->moshimo ) && $this->moshimo->isset_aid( 'yahoo' ) ) {
					$yahoo_search_url = $this->yahoo->get_search_url( $with_search_keyword, false );
					$data['YahooURL'] = $this->moshimo->get_url( 'yahoo', $yahoo_search_url );
				}
			}
		}

		if ( ! is_null( $atts['title'] ) && '' !== $atts['title'] ) {
			$data['Title'] = $atts['title'];
		}
		if ( ! is_null( $atts['class'] ) && '' !== $atts['class'] ) {
			$data['Class'] = $atts['class'];
		} else {
			$data['Class'] = '';
		}

		if ( isset( $data['Service'], $data['ID'], $data['URL'] ) && 'amazon' == $data['Service'] && ! is_null( $this->bitly ) && ! $moshimo_amazon_enable ) {
			$url_cache = $this->cache->get( 'bitly_' . $data['Service'] . '_' . $amazon_tracking_id . '_' . $data['ID'] );
			if ( false !== $url_cache ) {
				$data['URL'] = $url_cache;
			} else {
				$shorten_result = $this->bitly->shorten( $data['URL'] );
				if ( ! is_wp_error( $shorten_result ) ) {
					$data['URL'] = $shorten_result;
					$this->cache->save( 'bitly_' . $data['Service'] . '_' . $amazon_tracking_id . '_' . $data['ID'], $shorten_result, '1 year' );
				}
			}
		}

		$formatted_data = $this->template->format( $data );
		return $this->template->output( $formatted_data, $atts['type'], $is_with );
	}

	public function admin_menu() {
		$title       = __( 'WP Associate Post R2', 'wp-associate-post-r2' );
		$hook_suffix = add_options_page( $title, $title, 'manage_options', WPAP_ID, array( $this, 'option_page' ) );
		add_action( 'load-' . $hook_suffix, array( $this, 'load_option_page' ) );
	}

	public function validate_option( $input ) {
		if ( isset( $input['amazon_tracking_id'] ) ) {
			if ( '' !== $input['amazon_tracking_id'] && ! preg_match( '/^[a-zA-Z0-9-_]+(-22)$/', $input['amazon_tracking_id'] ) ) {
				add_settings_error( WPAP_ID, 'message', __( 'The Amazon Tracking ID is incorrect.', 'wp-associate-post-r2' ) );
				$input['amazon_tracking_id'] = $this->option['amazon_tracking_id'];
			}
		}
		return $input;
	}

	public function load_option_page() {
		if ( isset( $_POST['option_export_submit'] ) && check_admin_referer( 'wpap_option_export' ) ) {
			while ( ob_get_level() > 0 ) {
				ob_end_clean();
			}
			$filename = 'wpap_' . date( 'Ymd' ) . '.wpapconf';
			header( 'Accept-Ranges: none' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			header( 'Content-Type: application/octet-stream' );
			echo maybe_serialize( $this->option );
			exit();
		}
		if ( isset( $_POST['option_import_submit'] ) && check_admin_referer( 'wpap_option_import' ) ) {
			if ( is_uploaded_file( $_FILES['option_import_file']['tmp_name'] ) ) {
				ob_start();
				readfile( $_FILES['option_import_file']['tmp_name'] );
				$import_option = ob_get_contents();
				ob_end_clean();
				$import_option = maybe_unserialize( trim( $import_option ) );
				update_option( WPAP_ID, $import_option );
				$this->option = $import_option;
			}
		}
	}

	public function option_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$option = $this->option;
		include( WPAP_PLUGIN_PATH . 'includes/admin-page.php' );
	}

	public function plugin_action_links( $links, $file ) {
		if ( WPAP_PLUGIN_BASENAME != $file ) {
			return $links;
		}
		$settings_link = '<a href="' . menu_page_url( WPAP_ID, false ) . '">' . esc_html__( 'Settings', 'wp-associate-post-r2' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function plugin_row_meta( $links, $file ) {
		if ( WPAP_PLUGIN_BASENAME == $file ) {
			$links[] = '<a href="https://wp-ap.net/help/">' . esc_html__( 'Help', 'wp-associate-post-r2' ) . '</a>';
		}
		return $links;
	}

	public function ajax_cache_clear() {
		$result_json = array(
			'status'  => 'error',
			'message' => esc_html__( 'Some error occurred, please try again later.', 'wp-associate-post-r2' ),
		);
		if ( check_ajax_referer( 'wpap_cache_clear', 'nonce', false ) ) {
			$this->cache->clear();
			$result_json = array(
				'status'  => 'success',
				'message' => esc_html__( 'Cache cleared.', 'wp-associate-post-r2' ),
			);
		}
		return wp_send_json( $result_json );
	}

	public function media_upload_tabs( $tabs ) {
		global $pagenow;
		if ( 'media-upload.php' === $pagenow && isset( $_GET['tab'] ) && preg_match( '/^(' . WPAP_ID_ABBR . '_)/', $_GET['tab'] ) ) {
			$tabs = array();
			if ( isset( $this->service['With'] ) ) {
				$tabs[ WPAP_ID_ABBR . '_with' ] = __( 'Amazon with All', 'wp-associate-post-r2' );
			}
			if ( isset( $this->service['Amazon'] ) ) {
				$tabs[ WPAP_ID_ABBR . '_amazon' ] = __( 'Amazon', 'wp-associate-post-r2' );
			}
			if ( isset( $this->service['RakutenIchiba'] ) ) {
				$tabs[ WPAP_ID_ABBR . '_rakuten_ichiba' ] = __( 'Rakuten Ichiba', 'wp-associate-post-r2' );
			}
			if ( isset( $this->service['RakutenBooks'] ) ) {
				$tabs[ WPAP_ID_ABBR . '_rakuten_books' ] = __( 'Rakuten Books', 'wp-associate-post-r2' );
			}
		}
		return $tabs;
	}

	public function media_upload_item_search() {
		wp_iframe( array( $this, 'media_upload_item_search_content' ) );
	}

	public function media_upload_item_search_content() {
		if ( ! empty( $_GET['tab'] ) ) {
			if ( isset( $this->service['Amazon'] ) && WPAP_ID_ABBR . '_amazon' === $_GET['tab'] ) {
				$this->item_search( $this->service['Amazon'] );
			} elseif ( isset( $this->service['RakutenIchiba'] ) && WPAP_ID_ABBR . '_rakuten_ichiba' === $_GET['tab'] ) {
				$this->item_search( $this->service['RakutenIchiba'] );
			} elseif ( isset( $this->service['RakutenBooks'] ) && WPAP_ID_ABBR . '_rakuten_books' === $_GET['tab'] ) {
				$this->item_search( $this->service['RakutenBooks'] );
			} elseif ( WPAP_ID_ABBR . '_with' === $_GET['tab'] ) {
				$this->item_search( $this->service['With'] );
			}
		}
	}

	private function media_upload_header() {
		ob_start();
		media_upload_header();
		$html = ob_get_contents();
		ob_end_clean();

		$parameter = $_GET;
		unset( $parameter['type'] );
		unset( $parameter['tab'] );
		unset( $parameter['post_id'] );
		unset( $parameter['q_kw'] );

		foreach ( $parameter as $key => $value ) {
			$html = str_replace( urlencode( $key ) . '=' . urlencode( $value ), '', $html );
		}

		$html = preg_replace( array( '/(&#038;)+/', "/(&#038;')/" ), array( '&#038;', "'" ), $html );

		echo $html;
	}

	private function item_search( $service ) {
		if ( ! in_array( $service, $this->service, true ) || empty( $_GET['post_id'] ) ) {
			return false;
		}

		$is_amazon           = ( isset( $this->service['Amazon'] ) && $service === $this->service['Amazon'] );
		$is_rakuten_ichiba   = ( isset( $this->service['RakutenIchiba'] ) && $service === $this->service['RakutenIchiba'] );
		$is_rakuten_books    = ( isset( $this->service['RakutenBooks'] ) && $service === $this->service['RakutenBooks'] );
		$is_rakuten          = $is_rakuten_ichiba || $is_rakuten_books;
		$is_with             = ( isset( $this->service['With'] ) && $service === $this->service['With'] );
		$with_rakuten_enable = isset( $this->service['RakutenIchiba'], $this->service['RakutenBooks'] );
		$with_yahoo_enable   = ! is_null( $this->yahoo );
		$query_keyword       = isset( $_GET['q_kw'] ) ? rawurldecode( $_GET['q_kw'] ) : '';
		$query_category      = ! empty( $_GET['q_cat'] ) ? $_GET['q_cat'] : 'All'; //Amazon
		$input_keyword       = ! empty( $_GET['i_kw'] ) ? rawurldecode( $_GET['i_kw'] ) : $query_keyword;
		$item_table_html     = '';
		$preview_html        = '';

		if ( '' !== $query_keyword ) {
			$query_page = ! empty( $_GET['q_page'] ) ? (int) $_GET['q_page'] : 1;
			$query_sort = null;

			if ( $is_amazon || $is_with ) {
				$response = $this->amazon->item_search( $query_keyword, $query_page, $query_category );
			} elseif ( $is_rakuten_ichiba ) {
				$response = $this->rakuten->ichiba_item_search( $query_keyword, $query_page, $query_sort );
			} elseif ( $is_rakuten_books ) {
				$response = $this->rakuten->books_item_search( $query_keyword, $query_page, $query_sort );
			}

			if ( isset( $response ) && is_wp_error( $response ) ) {
				$error['type']          = $response->get_error_code();
				$response_error_message = $response->get_error_message();
				if ( ! empty( $response_error_message ) ) {
					$error['message'] = $response->get_error_message();
				}
			} else {
				$item_total = $response['item_total'];
				$page_total = $response['page_total'];

				foreach ( $response['data'] as $data ) {
					$data = $this->template->format( $data, true );

					$item_table_html .= '<tr id="item_' . esc_attr( $data['ID'] ) . '" data-title="' . esc_attr( $data['Title'] ) . '">';
					$item_table_html .= '<td class="item-info">';
					$item_table_html .= '<div class="item-image"><img src="' . $data['Thumbnail'] . '"></div>';
					$item_table_html .= '<span class="item-title">' . $data['Title'] . '</span><br />';
					if ( $is_with || $is_amazon || $is_rakuten_books ) {
						if ( ! empty( $data['Author'] ) ) {
							$item_table_html .= '<span class="item-creator">' . implode( ', ', $data['Author'] ) . '</span><br />';
						} elseif ( ! empty( $data['Artist'] ) ) {
							$item_table_html .= '<span class="item-creator">' . implode( ', ', $data['Artist'] ) . '</span><br />';
						}
					} elseif ( $is_rakuten_ichiba ) {
						$item_table_html .= '<span class="item-shop">' . $data['Shop'] . '</span><br />';
					}
					if ( ( $is_amazon || $is_with ) && 'Video On Demand' === $data['ProductGroup'] ) {
						$item_table_html .= '<span class="item-price-null">';
						$item_table_html .= __( 'Amazon video (<span class="tooltip" data-tooltip="It\'s not possible to get the price of Amazon videos due to API specifications.">Not possible to get the price</span>)', 'wp-associate-post-r2' );
						$item_table_html .= '</span>';
					} elseif ( ( $is_amazon || $is_with ) && ! empty( $data['UsedOnly'] ) ) {
						$item_table_html .= '<span class="item-price-null">';
						$item_table_html .= __( '<span class="tooltip" data-tooltip="The price for products sold only as a used item is not shown due to possibility of large fluctuations in price.">Used only</span>', 'wp-associate-post-r2' );
						$item_table_html .= '</span>';
					} elseif ( is_null( $data['Price'] ) ) {
						$item_table_html .= '<span class="item-price-null">';
						$item_table_html .= __( '<span class="tooltip" data-tooltip="Not presently in stock due to discontinued product or canceled release, etc. by manufacturer or publisher.">Not in stock</span>', 'wp-associate-post-r2' );
						$item_table_html .= '</span>';
					} else {
						$item_table_html .= '<span class="item-price">' . $data['Price'] . '</span>';
					}
					$item_table_html .= '</td>';
					if ( $is_rakuten ) {
						$item_table_html .= '<td class="item-rate">';
						$item_table_html .= $data['Rate'] . '%';
						$item_table_html .= '</td>';
					}
					$item_table_html .= '</tr>';

					$with_params = compact( 'is_with', 'with_rakuten_enable', 'with_yahoo_enable' );
					foreach ( $this->template->get_preview_html( $data, $with_params ) as $template_name => $preview_single_html ) {
						$preview_html .= '<div class="display-preview-html ' . $data['ID'] . ' ' . $data['ID'] . '_' . $template_name . '">' . $preview_single_html . '</div>';
					}
				} // End foreach().
			} // End if().
		}  // End if().

		$this->media_upload_header();
		?>
		<div class="wpap-search">
			<form action="<?php echo get_admin_url( null, 'media-upload.php' ); ?>" class="search-form">
				<div class="search-form-right">
					<?php if ( $is_amazon || $is_with ) : ?>
						<select name="q_cat" id="wpap-search-category">
							<?php
							$search_category['All'] = __( 'All Categories', 'wp-associate-post-r2' );
							$search_category       += Amazon::SEARCH_INDEX;
							foreach ( $search_category as $key => $value ) :
								if ( $key === $query_category ) {
									$selected = ' selected="selected"';
								} else {
									$selected = '';
								}
								?>
								<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $value; ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
					<input type="submit" value="<?php esc_attr_e( 'Search', 'wp-associate-post-r2' ); ?>" class="button">
				</div>
				<div class="search-form-left">
					<input type="text" name="q_kw" value="<?php echo esc_attr( $input_keyword ); ?>" id="wpap-search-keyword" placeholder="<?php echo esc_attr_e( 'Keyword', 'wp-associate-post-r2' ); ?>">
					<input type="hidden" name="type" value="<?php echo esc_attr( $_GET['type'] ); ?>">
					<input type="hidden" name="tab" value="<?php echo esc_attr( $_GET['tab'] ); ?>">
					<input type="hidden" name="post_id" value="<?php echo esc_attr( $_GET['post_id'] ); ?>">
					<input type="hidden" name="q_page" value="1">
				</div>
			</form>
			<?php if ( '' !== $query_keyword ) : ?>
				<div class="info">
					<?php if ( empty( $error ) && $is_with ) : ?>
						<p class="info-display"><?php esc_html_e( 'We display product information from Amazon.', 'wp-associate-post-r2' ); ?></p>
					<?php endif; ?>
					<p class="info-donation">
						<a href="https://wp-ap.net/donation/" target="_blank">
							<?php esc_html_e( 'I like this plugin, so I\'ll be supporter.', 'wp-associate-post-r2' ); ?>
						</a>
					</p>
				</div>
				<?php if ( empty( $error ) || ( ! empty( $error ) && in_array( $error['type'], array( 'error_zero', 'error_retry' ), true ) ) ) : ?>
					<h1 class="headline">
						<?php if ( isset( $item_total ) ) {
							printf(
								_n(
									'%1$s result for "%2$s"',
									'%1$s results for "%2$s"',
									$item_total,
									'wp-associate-post-r2'
								),
								number_format_i18n( $item_total ),
								mb_strimwidth( $query_keyword, 0, 60, '...' )
							);
						} else {
							printf(
								__( 'Result for "%s"', 'wp-associate-post-r2' ),
								mb_strimwidth( $query_keyword, 0, 60, '...' )
							);
						} ?>
					</h1>
				<?php endif; ?>
				<?php if ( ! empty( $error ) ) : ?>
					<?php if ( 'error_zero' === $error['type'] ) : ?>
						<div class="error error-zero">
							<h2 class="error-headline"><?php esc_html_e( 'Product not found.', 'wp-associate-post-r2' ); ?></h2>
							<p class="error-message"><?php esc_html_e( 'Make another search after changing the designated conditions.', 'wp-associate-post-r2' ); ?></p>
						</div>
					<?php elseif ( 'error_retry' === $error['type'] ) : ?>
						<div class="error">
							<h2 class="error-headline"><?php esc_html_e( 'The upper limit of API requests has been passed.', 'wp-associate-post-r2' ); ?></h2>
							<p class="error-message">
								<?php esc_html_e( 'Please wait some time before making another search.', 'wp-associate-post-r2' ); ?>
								<a href="<?php echo esc_url( add_query_arg( array( 'reload' => 'true' ) ) ); // @codingStandardsIgnoreLine ?>" class="button button-small">
									<?php esc_html_e( 'Reload', 'wp-associate-post-r2' ); ?>
								</a>
							</p>
						</div>
					<?php else : ?>
						<div class="error">
							<h2 class="error-headline"><?php esc_html_e( 'An error occurred.', 'wp-associate-post-r2' ); ?></h2>
							<p class="error-message"><?php echo esc_html( $error['message'] ); ?></p>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<?php if ( $is_rakuten ) : ?>
						<div class="rates">
							<span class="tooltip" data-tooltip="<?php esc_attr_e( 'Commission Rates are a rough indicator and can differ to actual.', 'wp-associate-post-r2' ); ?>">
								<?php esc_html_e( 'Commission Rates', 'wp-associate-post-r2' ); ?>
							</span>
						</div>
					<?php endif; ?>
					<table class="result-item">
						<?php echo $item_table_html; ?>
					</table>
					<ul class="pagination">
						<?php if ( $query_page > 1 ) : // @codingStandardsIgnoreStart ?>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'q_page' => $query_page - 1 ) ) ); ?>" class="button"><?php echo esc_attr_e( '&lt; Prev', 'wp-associate-post-r2' ); ?></a></li>
						<?php endif; if ( $query_page < $page_total ) : ?>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'q_page' => $query_page + 1 ) ) ); ?>" class="button"><?php echo esc_attr_e( 'Next &gt;', 'wp-associate-post-r2' ); ?></a></li>
						<?php endif; // @codingStandardsIgnoreEnd ?>
					</ul>
					<!-- Display preview (sub window) start -->
					<div class="wpap-overlay" id="wpap-overlay-subwindow"></div>
					<div class="subwindow" id="wpap-admin-subwindow">
						<input type="hidden" name="service" value="<?php echo $service; ?>">
						<input type="hidden" name="id" value="">
						<input type="hidden" name="title" value="">
						<ul class="template-type">
							<?php foreach ( $this->template->get_type_array( $is_with ) as $key => $value ) : ?>
								<li><label><input type="radio" name="tpl" value="<?php echo $key; ?>"> <?php echo $value; ?></label></li>
							<?php endforeach; ?>
						</ul>
						<div class="display-preview"><?php echo $preview_html; ?></div>
						<?php if ( ( $is_amazon || $is_with ) && ! empty( $this->option['bitly_enable'] ) ) : ?>
							<p class="shoten-note"><?php esc_html_e( '* To reduce the operating speed, the Moshimo Affiliates URL and the Bitly URL shortening does not reflect.', 'wp-associate-post-r2' ); ?></p>
						<?php endif; ?>
						<?php if ( $is_with ) : ?>
							<div class="wpap-search-keyword">
								<p class="wpap-search-keyword-title">
									<span class="tooltip" data-tooltip="<?php _e( 'The link address for Rakuten and Yahoo Shopping is the keywords search result page.<br />With the search button below, check if each corresponding product is displayed. If it is not displayed, please modify the search keyword.', 'wp-associate-post-r2' ); ?>">
										<strong><?php esc_html_e( 'Search Keyword Setting', 'wp-associate-post-r2' ); ?></strong>
									</span>
								</p>
								<div class="wpap-search-keyword-right">
									<?php if ( $with_rakuten_enable ) : ?>
										<input type="button" value="<?php esc_attr_e( 'Search on Rakuten', 'wp-associate-post-r2' ); ?>" class="button" id="wpap-admin-rakuten-ichiba-search">
									<?php endif; if ( $with_yahoo_enable ) : ?>
										<input type="button" value="<?php esc_attr_e( 'Search on Yahoo Shopping', 'wp-associate-post-r2' ); ?>" class="button" id="wpap-admin-yahoo-search">
									<?php endif; ?>
								</div>
								<div class="wpap-search-keyword-left">
									<input type="text" name="with_search_keyword" value="" id="with_search_keyword">
									<?php if ( $with_rakuten_enable ) : ?>
										<input type="hidden" name="rakuten_affiliate_id" value="<?php echo $this->option['rakuten_affiliate_id']; ?>">
									<?php endif; if ( $with_yahoo_enable ) : ?>
										<input type="hidden" name="yahoo_vc_sid" value="<?php echo $this->option['yahoo_vc_sid']; ?>">
										<input type="hidden" name="yahoo_vc_pid" value="<?php echo $this->option['yahoo_vc_pid']; ?>">
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="wpap-button-group">
							<div class="wpap-button-group-left">
								<input type="button" value="<?php esc_attr_e( 'Back to Index', 'wp-associate-post-r2' ); ?>" class="button" id="wpap-admin-subwindow-cancel">
							</div>
							<div class="wpap-button-group-right">
								<?php if ( ! $this->is_gutenberg_active() ) : ?>
									<input type="button" value="<?php esc_attr_e( 'Insert', 'wp-associate-post-r2' ); ?>" class="button button-primary wpap-insert-button" id="wpap-admin-subwindow-insert-continue">
								<?php endif; ?>
								<input type="button" value="<?php esc_attr_e( 'Insert and Close', 'wp-associate-post-r2' ); ?>" class="button button-primary wpap-insert-button" id="wpap-admin-subwindow-insert">
							</div>
						</div>
					</div>
					<!-- Display preview (sub window) end -->
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<div class="wpap-overlay" id="wpap-overlay-loading">
			<div class="loading-image"><img src="<?php echo WPAP_PLUGIN_URL . 'images/loading.gif'; ?>"></div>
		</div>
		<?php
	}

	public function media_buttons() {
		add_thickbox();
		// @codingStandardsIgnoreStart
		global $post_ID, $temp_ID;
		$id = (int) ( 0 == $post_ID ? $temp_ID : $post_ID );
		// @codingStandardsIgnoreEnd
		$tab = $this->get_search_tab_id();
		if ( ! is_null( $tab ) ) {
			$title = __( 'WP Associate Post R2', 'wp-associate-post-r2' );
			$label = __( 'Add Product Links', 'wp-associate-post-r2' );
			$url   = get_admin_url( null, 'media-upload.php?post_id=' . $id . '&amp;type=' . $tab . '&amp;tab=' . $tab . '&amp;TB_iframe=true' );
			printf( '<a href="%s" class="button thickbox" id="wpap-insert-item-button" title="%s">%s</a>', $url, $title, $label );
		}
	}

	public function add_tinymce_plugin( $plugin_array ) {
		$plugin_array[ WPAP_ID_ABBR ] = WPAP_PLUGIN_URL . 'js/tinymce-plugin.js';
		return $plugin_array;
	}

	public function add_tinymce_style( $mce_css ) {
		if ( ! empty( $mce_css ) ) {
			$mce_css .= ',';
		}
		$mce_css .= WPAP_PLUGIN_URL . 'css/tinymce.css';
		return $mce_css;
	}

	private function get_search_tab_id() {
		if ( isset( $this->service['With'] ) ) {
			return WPAP_ID_ABBR . '_with';
		} elseif ( isset( $this->service['Amazon'] ) ) {
			return WPAP_ID_ABBR . '_amazon';
		} elseif ( isset( $this->service['RakutenIchiba'] ) ) {
			return WPAP_ID_ABBR . '_rakuten_ichiba';
		} elseif ( isset( $this->service['RakutenBooks'] ) ) {
			return WPAP_ID_ABBR . '_rakuten_books';
		}
		return null;
	}

	private function is_gutenberg_active() {
		$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );
		$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

		if ( ! $gutenberg && ! $block_editor ) {
			return false;
		}

		if ( $this->is_classic_editor_active() ) {
			$editor_option = get_option( 'classic-editor-replace' );
			$block_editor_active = array( 'no-replace', 'block' );
			return in_array( $editor_option, $block_editor_active, true );
		}

		return true;
	}

	private function is_classic_editor_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			return true;
		}

		return false;
	}

}
