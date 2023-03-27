<?php

namespace WP_Associate_Post_R2;

use WP_Error;

class Bitly {

	const ENDPOINT = 'https://api-ssl.bitly.com/v4/shorten';
	const REQUEST_TIMEOUT = 10;

	private $access_token = null;

	public function __construct( $token ) {
		$this->access_token = $token;
	}

	public function shorten( $long_url ) {
		$response = wp_remote_post(
			self::ENDPOINT,
			array(
				'timeout'   => self::REQUEST_TIMEOUT,
				'sslverify' => false,
				'headers'   => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $this->access_token
				),
				'body' => json_encode( array( 'long_url' => $long_url ) )
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_json = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! in_array( $response_code, array( 200, 201 ), true ) ) {
			$error_message = isset( $response_json['message'] ) ? $response_json['message'] : '';
			return new WP_Error( 'api_error', $response_code . ': ' . $error_message );
		}

		return $response_json['link'];
	}

}
