<?php

namespace WP_Associate_Post_R2;

class Template {

	const GLUE_STRING = ', ';

	private $template_type = array();

	public function __construct() {
		$this->template_type = array(
			'single' => array(
				'detail'       => __( 'Detail', 'wp-associate-post-r2' ),
				'image-text-h' => __( 'Image & Text (H)', 'wp-associate-post-r2' ),
				'image-text-v' => __( 'Image & Text (V)', 'wp-associate-post-r2' ),
				'image'        => __( 'Image', 'wp-associate-post-r2' ),
				'text'         => __( 'Text', 'wp-associate-post-r2' ),
			),
			'with'   => array(
				'detail'       => __( 'Detail', 'wp-associate-post-r2' ),
				'image-text-h' => __( 'Image & Text (H)', 'wp-associate-post-r2' ),
				'image-text-v' => __( 'Image & Text (V)', 'wp-associate-post-r2' ),
			),
		);
	}

	public function get_type_array( $with_mode = false ) {
		$mode = $with_mode ? 'with' : 'single';
		$type = array();
		foreach ( $this->template_type[ $mode ] as $name => $value ) {
			$type[ $name ] = $value;
		}
		return $type;
	}

	public function format( $data, $search_mode = false ) {
		if ( ! empty( $data['Title'] ) && $search_mode ) {
			$data['Title'] = mb_strimwidth( $data['Title'], 0, 103, '...' );
		}

		if ( isset( $data['Date'] ) ) {
			$data['Date'] = get_date_from_gmt( $data['Date'], 'm/d H:i' );
		}

		if ( isset( $data['Price'] ) ) {
			$data['Price'] = sprintf( __( '¥%s', 'wp-associate-post-r2' ), number_format( $data['Price'] ) );
		}

		return $data;
	}

	public function output( $item, $type, $with_mode = false ) {
		$mode = $with_mode ? 'with' : 'single';
		if ( ! isset( $this->template_type[ $mode ][ $type ] ) ) {
			return '';
		}

		$with_label = apply_filters( 'wpap_service_link_label', array(
			'amazon'         => esc_html__( 'Amazon', 'wp-associate-post-r2' ),
			'rakuten-ichiba' => esc_html__( 'Rakuten Ichiba', 'wp-associate-post-r2' ),
			'rakuten-books'  => esc_html__( 'Rakuten Books', 'wp-associate-post-r2' ),
			'yahoo'          => esc_html__( 'Yahoo', 'wp-associate-post-r2' ),
		) );

		ob_start();
		include( WPAP_PLUGIN_PATH . 'templates/' . $mode . '-' . $type . '.php' );
		$html = ob_get_contents();
		ob_end_clean();
		return $this->trim( $html );
	}

	public function get_preview_html( $item_data, $with_params = array() ) {
		$is_with             = ( isset( $with_params['is_with'] ) && true === $with_params['is_with'] );
		$with_rakuten_enable = ( isset( $with_params['with_rakuten_enable'] ) && true === $with_params['with_rakuten_enable'] );
		$with_yahoo_enable   = ( isset( $with_params['with_yahoo_enable'] ) && true === $with_params['with_yahoo_enable'] );

		$item_data['Class'] = '';

		if ( $is_with ) {
			if ( $with_rakuten_enable ) {
				if ( isset( $item_data['ISBNJAN'] ) && Amazon::is_books( $item_data['ProductGroup'] ) ) {
					$item_data['RakutenType'] = 'books';
					$item_data['RakutenURL']  = 'https://books.rakuten.co.jp/';
				} else {
					$item_data['RakutenType'] = 'ichiba';
					$item_data['RakutenURL']  = 'https://www.rakuten.co.jp/';
					$item_data['Search']      = '';
				}
			}
			if ( $with_yahoo_enable ) {
				$item_data['YahooURL'] = 'https://shopping.yahoo.co.jp/';
				$item_data['Search']   = '';
			}
		}

		$preview_html = array();
		$mode         = $is_with ? 'with' : 'single';
		foreach ( $this->template_type[ $mode ] as $name => $value ) {
			$preview_html[ $name ] = $this->output( $item_data, $name, $is_with );
		}
		return $preview_html;
	}

	private function trim( $str ) {
		return str_replace( array( "\r\n", "\n", "\r", "\t" ), '', $str );
	}

}

