<?php

namespace WP_Associate_Post_R2;

use WP_Error;
use DateTime;

class Amazon {

	const AWS_REGION_NAME    = 'us-west-2';
	const AWS_HOST           = 'webservices.amazon.co.jp';
	const AWS_SERVICE_NAME   = 'ProductAdvertisingAPI';
	const AWS_HMAC_ALGORITHM = 'AWS4-HMAC-SHA256';
	const REQUEST_TIMEOUT    = 10;

	const REQUEST_TYPE_GET_ITEMS    = 'GetItems';
	const REQUEST_TYPE_SEARCH_ITEMS = 'SearchItems';

	const REQUEST_TYPE = array(
		self::REQUEST_TYPE_GET_ITEMS,
		self::REQUEST_TYPE_SEARCH_ITEMS
	);

	const SEARCH_INDEX = array(
		'AmazonVideo'             => 'Prime Video',
		'Apparel'                 => '服＆ファッション小物',
		'Appliances'              => '大型家電',
		'Automotive'              => '車＆バイク',
		'Baby'                    => 'ベビー&マタニティ',
		'Beauty'                  => 'ビューティー',
		'Books'                   => '本',
		'Classical'               => 'クラシックミュージック',
		'Computers'               => 'パソコン・周辺機器',
		'DigitalMusic'            => 'Amazon Music',
		'Electronics'             => '家電＆カメラ',
		'Fashion'                 => 'ファッション',
		'FashionBaby'             => 'ファッション（キッズ&ベビー）',
		'FashionMen'              => 'ファッション（メンズ）',
		'FashionWomen'            => 'ファッション（レディース）',
		'ForeignBooks'            => '洋書',
		'GiftCards'               => 'ギフト券',
		'GroceryAndGourmetFood'   => 'お酒・食品・飲料',
		'HealthPersonalCare'      => 'ヘルス＆ビューティー',
		'Hobbies'                 => 'ホビー',
		'HomeAndKitchen'          => 'ホーム&キッチン',
		'Industrial'              => '産業・研究開発用品',
		'Jewelry'                 => 'ジュエリー',
		'KindleStore'             => 'Kindleストア',
		'MobileApps'              => 'アプリストア',
		'MoviesAndTV'             => 'DVD',
		'Music'                   => 'ミュージック',
		'MusicalInstruments'      => '楽器・音響機器',
		'OfficeProducts'          => '文房具・オフィス用品',
		'PetSupplies'             => 'ペット用品',
		'Shoes'                   => 'シューズ&バッグ',
		'Software'                => 'PCソフト',
		'SportsAndOutdoors'       => 'スポーツ',
		'ToolsAndHomeImprovement' => 'DIY・工具・ガーデン',
		'Toys'                    => 'おもちゃ',
		'VideoGames'              => 'ゲーム',
		'Watches'                 => '時計',
	);

	private $access_key_id     = null;
	private $secret_access_key = null;
	private $associate_tag     = null;

	public function __construct( $access_id, $secret_key, $as_tag ) {
		$this->access_key_id     = $access_id;
		$this->secret_access_key = $secret_key;
		$this->associate_tag     = $as_tag;
	}

	public function item_lookup( $asin ) {
		$params            = array();
		$params['ItemIds'] = array( $asin );
		return $this->send_request( self::REQUEST_TYPE_GET_ITEMS, $params );
	}

	public function item_search( $keyword, $page = null, $category = null ) {
		$params                = array();
		$params['ItemPage']    = ! is_null( $page ) ? $page : 1;
		$params['Keywords']    = $keyword;
		$params['SearchIndex'] = ! is_null( $category ) ? $category : 'All';
		return $this->send_request( self::REQUEST_TYPE_SEARCH_ITEMS, $params );
	}

	private function send_request( $type, $params ) {
		if ( ! in_array( $type, self::REQUEST_TYPE, true ) ) {
			return array();
		}

		$params['PartnerType'] = 'Associates';
		$params['PartnerTag']  = $this->associate_tag;
		$params['Resources']   = array(
			'Images.Primary.Medium',
			'Images.Primary.Large',
			'ItemInfo.ByLineInfo',
			'ItemInfo.Classifications',
			'ItemInfo.ContentInfo',
			'ItemInfo.ExternalIds',
			'ItemInfo.Title',
			'Offers.Summaries.LowestPrice',
		);

		$request_url = 'https://' . self::AWS_HOST . '/paapi5/' . strtolower( $type );
		$payload     = json_encode( $params );
		$headers     = $this->get_headers( $type, $payload );

		$response = wp_remote_post( $request_url, array(
			'httpversion' => '1.1',
			'timeout'     => self::REQUEST_TIMEOUT,
			'sslverify'   => false,
			'headers'     => $headers,
			'body'        => $payload,
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'error', $response->get_error_message() );
		}
		if ( wp_remote_retrieve_response_code( $response ) === 500 ) {
			return new WP_Error( 'error', __( 'API system error occurred.', 'wp-associate-post-r2' ) );
		}
		if ( wp_remote_retrieve_response_code( $response ) === 429 ) {
			return new WP_Error( 'error_retry' );
		}

		$json   = wp_remote_retrieve_body( $response );
		$object = json_decode( $json );

		if ( isset( $object->Errors ) ) {
			$error_code    = $object->Errors[0]->Code;
			$error_message = $object->Errors[0]->Message;
			switch ( $error_code ) {
				case 'NoResults':
					return new WP_Error( 'error_zero' );
					break;
			}
			return new WP_Error( 'error', "{$error_message} ({$error_code})" );
		}

		$datetime = new DateTime();
		$data     = array();
		$items    = $type === self::REQUEST_TYPE_GET_ITEMS ? $object->ItemsResult->Items : $object->SearchResult->Items;

		foreach ( $items as $item ) {
			$temp                 = array();
			$temp['Service']      = 'amazon';
			$temp['ServiceName']  = 'Amazon';
			$temp['URL']          = $item->DetailPageURL;
			$temp['Date']         = $datetime->format( 'Y-m-d H:i:s' );
			$temp['ID']           = $item->ASIN;
			$temp['Title']        = $item->ItemInfo->Title->DisplayValue;
			$temp['ProductGroup'] = $item->ItemInfo->Classifications->ProductGroup->DisplayValue;

			$temp['Price'] = null;
			$isset_new_price = false;
			$isset_used_price = false;
			if ( $item->Offers->Summaries && ! in_array( $temp['ProductGroup'], array( 'Video On Demand' ) ) ) {
				foreach ( $item->Offers->Summaries as $summary ) {
					if ( $summary->Condition->Value === 'New' ) {
						$temp['Price']   = $summary->LowestPrice->Amount;
						$isset_new_price = true;
					} else if ( $summary->Condition->Value === 'Used' ) {
						$isset_used_price = true;
					}
				}
				if ( ! $isset_new_price && $isset_used_price ) {
					$temp['UsedOnly'] = true;
				}
			}

			if ( isset( $item->Images->Primary->Large ) ) {
				$temp['Image'] = $item->Images->Primary->Large->URL;
			} else {
				$temp['Image'] = WPAP_PLUGIN_URL . 'images/noimage_500px.png';
			}

			if ( isset( $item->ItemInfo->ExternalIds->EANs->DisplayValues[0] ) ) {
				$temp['ISBNJAN'] = $item->ItemInfo->ExternalIds->EANs->DisplayValues[0];
			}

			if ( isset( $item->ItemInfo->ByLineInfo->Contributors ) ) {
				$contributors = $item->ItemInfo->ByLineInfo->Contributors;
				foreach( $contributors as $contributor ) {
					$contributor_name = $contributor->Name;
					switch ( $contributor->Role ) {
						case '著':
							$temp['Author'][] = $contributor_name;
							break;
						case 'アーティスト':
						case '出演':
							$temp['Artist'][] = $contributor_name;
							break;
					}
				}
			}

			if ( in_array( $temp['ProductGroup'], array( 'Book', 'Digital Ebook Purchas' ) ) ) {
				if ( isset( $item->ItemInfo->ContentInfo->PublicationDate ) ) {
					$release_str     = $item->ItemInfo->ContentInfo->PublicationDate->DisplayValue;
					$release_date    = new DateTime( $release_str );
					$temp['Release'] = $release_date->format( 'Y/m/d' );
				}
			}

			if ( self::REQUEST_TYPE_SEARCH_ITEMS === $type ) {
				if ( isset( $item->Images->Primary->Medium ) ) {
					$temp['Thumbnail'] = $item->Images->Primary->Medium->URL;
				} else {
					$temp['Thumbnail'] = WPAP_PLUGIN_URL . 'images/noimage_160px.png';
				}
			}

			$data[] = $temp;
		} // End foreach().

		if ( self::REQUEST_TYPE_GET_ITEMS === $type ) {
			return $data[0];
		}

		$item_total        = $object->SearchResult->TotalResultCount;
		$page_total_actual = ceil( $item_total / 10 );
		$page_total        = ( $page_total_actual < 10 ) ? $page_total_actual : 10;
		return array(
			'item_total' => $item_total,
			'page_now'   => $params['ItemPage'],
			'page_total' => $page_total,
			'data'       => $data,
		);
	}

	private function get_time_stamp() {
		return gmdate( 'Ymd\THis\Z' );
	}

	private function get_date() {
		return gmdate( 'Ymd' );
	}

	private function get_headers( $type, $payload ) {
		$x_amz_date   = $this->get_time_stamp();
		$current_date = $this->get_date();

		$headers['content-encoding'] = 'amz-1.0';
		$headers['content-type']     = 'application/json; charset=utf-8';
		$headers['host']             = self::AWS_HOST;
		$headers['x-amz-target']     = 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.' . $type;
		$headers['x-amz-date']       = $x_amz_date;

		ksort( $headers );

		$signed_header  = $this->prepare_signed_header( $headers );
		$canonical_url  = $this->prepare_canonical_request( $type, $headers, $signed_header, $payload );
		$string_to_sign = $this->prepare_string_to_sign( $x_amz_date, $current_date, $canonical_url );
		$signature      = $this->calculate_signature( $current_date, $string_to_sign );
		if ( $signature ) {
			$headers['Authorization'] =
				self::AWS_HMAC_ALGORITHM . ' Credential=' .
				$this->access_key_id . '/' . $this->get_date() . '/' . self::AWS_REGION_NAME . '/' . self::AWS_SERVICE_NAME .
				'/aws4_request/,SignedHeaders=' . $signed_header . ',Signature=' . $signature;
			return $headers;
		}
	}

	private function prepare_signed_header( $headers ) {
		$signed_headers = '';
		foreach ( $headers as $key => $value ) {
			$signed_headers .= $key . ';';
		}
		return substr( $signed_headers, 0, - 1 );
	}

	private function prepare_canonical_request( $type, $headers, $signed_header, $payload ) {
		$canonical_url  = "POST\n";
		$canonical_url .= '/paapi5/' . strtolower( $type ) . "\n\n";
		foreach ( $headers as $key => $value ) {
			$canonical_url .= $key . ':' . $value . "\n";
		}
		$canonical_url .= "\n";
		$canonical_url .= $signed_header . "\n";
		$canonical_url .= $this->generate_hex( $payload );
		return $canonical_url;
	}

	private function prepare_string_to_sign( $x_amz_date, $current_date, $canonical_url ) {
		$string_to_sign  = self::AWS_HMAC_ALGORITHM . "\n";
		$string_to_sign .= $x_amz_date . "\n";
		$string_to_sign .= $current_date . '/' . self::AWS_REGION_NAME . '/' . self::AWS_SERVICE_NAME . "/aws4_request\n";
		$string_to_sign .= $this->generate_hex( $canonical_url );
		return $string_to_sign;
	}

	private function get_signature_key( $current_date ) {
		$k_secret  = 'AWS4' . $this->secret_access_key;
		$k_date    = hash_hmac( 'sha256', $current_date, $k_secret, true );
		$k_region  = hash_hmac( 'sha256', self::AWS_REGION_NAME, $k_date, true );
		$k_service = hash_hmac( 'sha256', self::AWS_SERVICE_NAME, $k_region, true );
		return hash_hmac( 'sha256', 'aws4_request', $k_service, true );
	}

	private function calculate_signature( $current_date, $string_to_sign ) {
		$signature_key = $this->get_signature_key( $current_date );
		$signature     = hash_hmac( 'sha256', $string_to_sign, $signature_key, true );
		return strtolower( bin2hex( $signature ) );
	}

	private function generate_hex( $data ) {
		return strtolower( bin2hex( hash( 'sha256', $data, true ) ) );
	}

	public static function is_books( $product_group ) {
		$books = array( 'Book', 'DVD', 'Music', 'Software', 'Video Games' );
		return in_array( $product_group, $books );
	}

}
