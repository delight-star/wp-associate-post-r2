<?php

namespace WP_Associate_Post_R2;

use WP_Error;
use DateTime;

class Rakuten {

	const ENDPOINT_ICHIBA = 'https://app.rakuten.co.jp/services/api/IchibaItem/Search/20170706';
	const ENDPOINT_BOOKS  = 'https://app.rakuten.co.jp/services/api/BooksTotal/Search/20170404';
	const REQUEST_TIMEOUT = 10;

	private $application_id  = null;
	private $affiliate_id    = null;

	public function __construct( $application_id, $affiliate_id = null ) {
		$this->application_id = $application_id;
		$this->affiliate_id   = $affiliate_id;
	}

	public function ichiba_item_lookup( $item_code ) {
		$params['itemCode'] = $item_code;
		return $this->send_request( 'ichiba', $params );
	}

	public function books_item_lookup( $jan_code ) {
		$params['isbnjan'] = $jan_code;
		return $this->send_request( 'books', $params );
	}

	public function ichiba_item_search( $keyword, $page = null, $sort = null ) {
		$params['keyword'] = $keyword;
		$params['page']    = ! is_null( $page ) ? $page : 1;
		$params['sort']    = ! is_null( $sort ) ? $sort : 'standard';
		$params['hits']    = 10;
		return $this->send_request( 'ichiba', $params );
	}

	public function books_item_search( $keyword, $page = null, $sort = null ) {
		$params['keyword'] = $keyword;
		$params['page']    = ! is_null( $page ) ? $page : 1;
		$params['sort']    = ! is_null( $sort ) ? $sort : 'standard';
		$params['hits']    = 10;
		return $this->send_request( 'books', $params );
	}

	private function send_request( $service = 'ichiba', $params = array() ) {
		if ( isset( $params['keyword'] ) && strlen( $params['keyword'] ) === 1 ) {
			return new WP_Error( 'error', __( 'The search keywords must be entered with at least 2 half-width characters or at least 1 full-width character.', 'wp-associate-post-r2' ) );
		}

		$params['applicationId'] = $this->application_id;
		$params['formatVersion'] = 2;
		if ( ! empty( $this->affiliate_id ) ) {
			$params['affiliateId'] = $this->affiliate_id;
		}
		if ( 'books' === $service ) {
			$params['outOfStockFlag'] = 1;
		}

		$pairs = array();
		foreach ( $params as $key => $value ) {
			array_push( $pairs, rawurlencode( $key ) . '=' . rawurlencode( $value ) );
		}
		$canonical_query_string = join( '&', $pairs );

		if ( 'ichiba' === $service ) {
			$endpoint = self::ENDPOINT_ICHIBA;
		} elseif ( 'books' === $service ) {
			$endpoint = self::ENDPOINT_BOOKS;
		}
		$request_url = $endpoint . '?' . $canonical_query_string;

		$response = wp_remote_get( $request_url, array(
			'httpversion' => '1.1',
			'timeout'     => self::REQUEST_TIMEOUT,
			'sslverify'   => false,
		) );
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'error', $response->get_error_message() );
		}
		$response_code = wp_remote_retrieve_response_code( $response );

		$json   = wp_remote_retrieve_body( $response );
		$object = json_decode( $json );

		switch ( $response_code ) {
			case 400:
				if ( 'specify valid applicationId' == $object->error_description ) {
					return new WP_Error( 'error', __( 'Your Application ID is wrong. Please check your settings.', 'wp-associate-post-r2' ) );
				}
				return new WP_Error( 'error', sprintf( __( 'Parameter Error: %s' ), $object->error_description ) );
				break;
			case 404:
				return new WP_Error( 'error_zero' );
				break;
			case 429:
				return new WP_Error( 'error_retry' );
				break;
			case 500:
				return new WP_Error( 'error', __( 'API system error occurred.', 'wp-associate-post-r2' ) );
				break;
			case 503:
				return new WP_Error( 'error', __( 'API is under maintenance.', 'wp-associate-post-r2' ) );
				break;
		}

		if ( 0 === $object->count ) {
			return new WP_Error( 'error_zero' );
		}

		$type     = isset( $params['keyword'] ) ? 'ItemSearch' : 'ItemLookup';
		$datetime = new DateTime();
		$data     = array();
		foreach ( $object->Items as $item ) {
			$temp          = array();
			$temp['Price'] = $item->itemPrice;
			$temp['URL']   = $item->affiliateUrl;
			$temp['Date']  = $datetime->format( 'Y-m-d H:i:s' );
			if ( 'ichiba' === $service ) {
				$temp['Service']     = 'rakuten-ichiba';
				$temp['ServiceName'] = __( 'Rakuten Ichiba', 'wp-associate-post-r2' );
				$temp['ID']          = $item->itemCode;
				$temp['Title']       = $item->itemName;
				$temp['Shop']        = $item->shopName;
				if ( 1 == $item->imageFlag ) {
					$temp['Image'] = $item->mediumImageUrls[0];
				}
				if ( 'ItemSearch' == $type ) {
					if ( 1 == $item->imageFlag ) {
						$temp['Thumbnail'] = $item->mediumImageUrls[0];
					}
					$temp['Rate'] = (int) $item->affiliateRate;
				}
			} elseif ( 'books' === $service ) {
				$temp['Service']     = 'rakuten-books';
				$temp['ServiceName'] = __( 'Rakuten Books', 'wp-associate-post-r2' );
				$temp['ID']          = ( '' != $item->isbn ) ? $item->isbn : $item->jan;
				$temp['Title']       = $item->title;
				if ( 6 === (int) $item->availability ) {
					$temp['Price'] = null;
				}
				if ( '' != $item->author ) {
					$temp['Author'] = explode( '/', $item->author );
				} elseif ( '' != $item->artistName ) {
					$temp['Artist'] = explode( '/', $item->artistName );
				}
				$temp['Publisher'] = ( '' != $item->publisherName ) ? $item->publisherName : null;
				$temp['Image']     = $item->largeImageUrl;
				if ( 'ItemSearch' == $type ) {
					$temp['Thumbnail'] = $item->mediumImageUrl;
					$temp['Rate']      = 1;
				}
			} // End if().
			$data[] = $temp;
		} // End foreach().

		if ( 'ItemLookup' == $type ) {
			return $data[0];
		}

		return array(
			'item_total' => (int) $object->count,
			'page_now'   => (int) $object->page,
			'page_total' => (int) $object->pageCount,
			'data'       => $data,
		);
	}

	public function get_search_url( $keyword = '', $affiliate = true ) {
		$search_url = sprintf( 'https://search.rakuten.co.jp/search/mall/%s/', rawurlencode( $keyword ) );
		if ( ! $affiliate ) {
		    return $search_url;
        }
		$encode_search_url = rawurlencode( $search_url );
		return sprintf( 'https://hb.afl.rakuten.co.jp/hgc/%s/?pc=%s&m=%s', $this->affiliate_id, $encode_search_url, $encode_search_url );
	}

}
