<?php

namespace WP_Associate_Post_R2;

class Moshimo {

	const SERVICE_AMAZON  = 'amazon';
	const SERVICE_RAKUTEN = 'rakuten';
	const SERVICE_YAHOO   = 'yahoo';

	private $parameters = array(
		self::SERVICE_AMAZON => array(
			'p_id'  => 170,
			'pc_id' => 185,
			'pl_id' => 4062
		),
		self::SERVICE_RAKUTEN => array(
			'p_id'  => 54,
			'pc_id' => 54,
			'pl_id' => 616
		),
		self::SERVICE_YAHOO => array(
			'p_id'  => 1225,
			'pc_id' => 1925,
			'pl_id' => 18502
		)
	);

	private function check_service( $service ) {
		return array_key_exists( $service, $this->parameters );
	}

	public function isset_aid( $service ) {
		return array_key_exists( 'a_id', $this->parameters[ $service ] );
	}

	public function set_aid( $service, $a_id ) {
		if ( $this->check_service( $service ) && ! empty( $a_id ) ) {
			$this->parameters[ $service ] = array( 'a_id' => $a_id ) + $this->parameters[ $service ];
		}
		return false;
	}

	public function get_url( $service, $link ) {
		if ( ! $this->check_service( $service ) ) {
			return $link;
		}

		$params = $this->parameters[ $service ] + array( 'url' => $link );
		$pairs  = array();
		foreach ( $params as $key => $value ) {
			array_push( $pairs, $key . '=' . rawurlencode( $value ) );
		}
		$query_string = join( '&', $pairs );

		return 'https://af.moshimo.com/af/c/click?' . $query_string;
	}

	public function removal_get_parameters( $url ) {
        $removal_str = '?' . parse_url( $url, PHP_URL_QUERY );
        return str_replace( $removal_str, '', $url );
    }

}