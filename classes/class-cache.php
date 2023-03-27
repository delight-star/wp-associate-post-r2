<?php

namespace WP_Associate_Post_R2;

use DateTime, DateTimeZone, DateInterval;

class Cache {

	const TABLE_VERSION = '1.0';

	/** @var wpdb $wpdb */
	private $wpdb;
	private $table;

	public function __construct() {
		global $wpdb;
		$this->wpdb  = $wpdb;
		$this->table = $this->wpdb->prefix . WPAP_ID_ABBR . '_cache';
	}

	public function create_table() {
		$sql = "CREATE TABLE $this->table (
                   cache_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		           cache_key varchar(191) NOT NULL,
		           cache_value longtext,
		           created_date_gmt datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		           expire_date_gmt datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		           PRIMARY KEY  (cache_id),
		           UNIQUE KEY cache_key (cache_key)
		        );";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public function drop_table() {
		$this->wpdb->query( "DROP TABLE IF EXISTS $this->table;" ); // WPCS: unprepared SQL OK
	}

	public function get( $key ) {
		$sql            = $this->wpdb->prepare( "SELECT cache_value FROM $this->table WHERE cache_key = %s;", $key ); // WPCS: unprepared SQL OK
		$serialize_data = $this->wpdb->get_var( $sql ); // WPCS: unprepared SQL OK
		if ( is_null( $serialize_data ) ) {
			return false;
		}
		$data = json_decode( $serialize_data, true );
		return $data;
	}

	public function save( $key, $data, $life_time = '1 day' ) {
		// 作成日時・有効期限を設定
		$datetime         = new DateTime( null, new DateTimeZone( 'UTC' ) );
		$created_datetime = $datetime->format( 'Y-m-d H:i:s' );
		$expire_datetime  = $datetime->add( DateInterval::createFromDateString( $life_time ) )->format( 'Y-m-d H:i:s' );
		$serialize_data   = json_encode( $data );
		// データベース挿入
		$sql = $this->wpdb->prepare(
			"INSERT IGNORE INTO $this->table ( cache_key, cache_value, created_date_gmt, expire_date_gmt ) VALUES ( %s, %s, %s, %s );",
			$key,
			$serialize_data,
			$created_datetime,
			$expire_datetime
		); // WPCS: unprepared SQL OK
		$this->wpdb->query( $sql ); // WPCS: unprepared SQL OK
	}

	public function clean() {
		$datetime     = new DateTime( null, new DateTimeZone( 'UTC' ) );
		$now_datetime = $datetime->format( 'Y-m-d H:i:s' );
		$sql          = $this->wpdb->prepare( "DELETE FROM $this->table WHERE expire_date_gmt < %s;", $now_datetime ); // WPCS: unprepared SQL OK
		$this->wpdb->query( $sql ); // WPCS: unprepared SQL OK
	}

	public function clear() {
		$sql = $this->wpdb->prepare( "DELETE FROM $this->table WHERE cache_key NOT LIKE %s;", 'bitly_%' ); // WPCS: unprepared SQL OK
		$this->wpdb->query( $sql ); // WPCS: unprepared SQL OK
	}

}
