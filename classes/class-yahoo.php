<?php

namespace WP_Associate_Post_R2;

class Yahoo {

	private $vc_pid = null;
	private $vc_sid = null;

	public function __construct( $vc_sid, $vc_pid ) {
		$this->vc_sid = $vc_sid;
		$this->vc_pid = $vc_pid;
	}

	public function get_search_url( $keyword = '', $affiliate = true ) {
		$search_url = sprintf( 'https://shopping.yahoo.co.jp/search?p=%s&view=list', rawurlencode( $keyword ) );
        if ( ! $affiliate ) {
            return $search_url;
        }
		$encode_search_url = rawurlencode( $search_url );
		$affiliate_url     = sprintf( 'https://ck.jp.ap.valuecommerce.com/servlet/referral?sid=%s&pid=%s&vc_url=%s', $this->vc_sid, $this->vc_pid, $encode_search_url );
		return $affiliate_url;
	}

}
