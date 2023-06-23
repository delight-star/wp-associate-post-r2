<?php

/*
Plugin Name: WP Associate Post R2
Description: Affiliate easy installation plugin. Works with Amazon Associates, Rakuten Affiliate, and ValueCommerce Affiliate for Yahoo Shopping. Contributing to the monetization of your blog.
Plugin URI:  https://wp-ap.net/
Version:     5.0.0
Author:      Delight Star Inc.
Author URI:  https://delight-star.co.jp/
License:     GPLv2
*/

namespace WP_Associate_Post_R2;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( defined( 'WPAP_ENABLED' ) ) {
	exit( 'WP Associate Post R2 is already activated.' );
}

$plugin_meta = get_file_data( __FILE__, array(
	'version' => 'Version',
), 'plugin' );

define( 'WPAP_ENABLED', true );
define( 'WPAP_ID', 'wp-associate-post-r2' );
define( 'WPAP_ID_ABBR', 'wpap' );
define( 'WPAP_VERSION', $plugin_meta['version'] );
define( 'WPAP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPAP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register( function ( $class_name ) {
	if ( 0 !== strpos( $class_name, __NAMESPACE__ . '\\' ) ) {
		return false;
	}
	$file_name = str_replace( __NAMESPACE__ . '\\', '', $class_name );
	$file      = WPAP_PLUGIN_PATH . 'classes/class-' . strtolower( $file_name ) . '.php';
	if ( file_exists( $file ) ) {
		require_once( $file );
	}
	return false;
} );

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Setup', 'activation' ) );
register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Setup', 'uninstall' ) );
add_action( 'wpmu_new_blog', array( __NAMESPACE__ . '\Setup', 'multisite_new_blog' ) );
add_action( 'delete_blog', array( __NAMESPACE__ . '\Setup', 'multisite_delete_blog' ) );

$main = new Main();
add_action( 'init', array( $main, 'init' ) );
