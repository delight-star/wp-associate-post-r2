<?php

namespace WP_Associate_Post_R2;

class Setup {

	public static function activation( $network_wide ) {
		self::do_setup( $network_wide, array( __CLASS__, 'activation_processing' ) );
	}

	public static function uninstall() {
		self::do_setup( true, array( __CLASS__, 'uninstall_processing' ) );
	}

	public static function multisite_new_blog( $blog_id ) {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active_for_network( WPAP_PLUGIN_BASENAME ) ) {
			switch_to_blog( $blog_id );
			self::activation_processing();
			restore_current_blog();
		}
	}

	public static function multisite_delete_blog( $blog_id ) {
		switch_to_blog( $blog_id );
		self::uninstall_processing();
		restore_current_blog();
	}

	private static function do_setup( $network_wide, $callback ) {
		if ( is_multisite() && $network_wide ) {
			global $wpdb;
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				call_user_func( $callback );
				restore_current_blog();
			}
		} else {
			call_user_func( $callback );
		}
	}

	private static function activation_processing() {
		$cache = new Cache();
		$cache->create_table();
		$option_default = array( 'skin_css' => 'default-1' );
		add_option( WPAP_ID, $option_default );
	}

	private static function uninstall_processing() {
		$cache = new Cache();
		$cache->drop_table();
		delete_option( WPAP_ID );
	}

}
