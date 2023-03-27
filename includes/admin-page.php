<?php if ( ! defined( 'WPAP_ENABLED' ) ) { exit; } ?>
<div class="wrap wpap-option">
	<div class="wpap-option-edit">
		<h1><?php esc_html_e( 'WP Associate Post R2 Settings', 'wp-associate-post-r2' ); ?></h1>
		<form action="options.php" method="post">
			<?php settings_fields( WPAP_ID ); ?>
			<h2><?php esc_html_e( 'Amazon Associates / Product Advertising API', 'wp-associate-post-r2' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"></th>
					<td>
						<?php
						if ( ! isset( $option['amazon_enable'] ) ) {
							$option['amazon_enable'] = 0;
						}
						?>
						<input type="hidden" name="wp-associate-post-r2[amazon_enable]" value="0"/>
						<input type="checkbox" name="wp-associate-post-r2[amazon_enable]" value="1" id="wp-associate-post-r2[amazon_enable]" <?php checked( $option['amazon_enable'], '1' ); ?> />
						<label for="wp-associate-post-r2[amazon_enable]"><?php esc_html_e( 'Enable Amazon Associates links', 'wp-associate-post-r2' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Access Key ID', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['amazon_access_key_id'] ) ) {
							$option['amazon_access_key_id'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[amazon_access_key_id]" value="<?php echo esc_attr( $option['amazon_access_key_id'] ); ?>" size="50"/>
						<p><?php printf( __( '<a href="%s" target="_blank">Check here</a> for information on how to acquire an Access Key ID and a Secret Access Key.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/amazon-key-id/' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Secret Access Key', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['amazon_secret_access_key'] ) ) {
							$option['amazon_secret_access_key'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[amazon_secret_access_key]" value="<?php echo esc_attr( $option['amazon_secret_access_key'] ); ?>" size="50"/>
						<p><?php printf( __( '<a href="%s" target="_blank">Check here</a> for information on how to acquire an Access Key ID and a Secret Access Key.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/amazon-key-id/' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Tracking ID', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['amazon_tracking_id'] ) ) {
							$option['amazon_tracking_id'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[amazon_tracking_id]" value="<?php echo esc_attr( $option['amazon_tracking_id'] ); ?>" size="50"/>
						<p><?php printf( __( 'Enter your Tracking ID in the <code>*****-22</code> format. <a href="%s" target="_blank">Check here</a> for information on how to acquire a Tracking ID.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/amazon-affiliate-signup/' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"></th>
					<td>
						<?php
						if ( ! isset( $option['bitly_enable'] ) ) {
							$option['bitly_enable'] = 0;
						}
						?>
						<input type="hidden" name="wp-associate-post-r2[bitly_enable]" value="0"/>
						<input type="checkbox" name="wp-associate-post-r2[bitly_enable]" value="1" id="wp-associate-post-r2[bitly_enable]" <?php checked( $option['bitly_enable'], '1' ); ?> />
						<label for="wp-associate-post-r2[bitly_enable]"><?php esc_html_e( 'Shorten the Amazon Associates links using Bitly', 'wp-associate-post-r2' ); ?></label>
						<p><?php _e( 'The link will be shortened to the <code>https://amzn.to/*******</code> format.', 'wp-associate-post-r2' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Bitly Access Token', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['bitly_access_token'] ) ) {
							$option['bitly_access_token'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[bitly_access_token]" value="<?php echo esc_attr( $option['bitly_access_token'] ); ?>" size="50"/>
						<p><?php printf( __( 'An Access Token is required to shorten the URL using Bitly. <a href="%s" target="_blank">Check here</a> for information on how to acquire a Bitly Access Token.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/bitly-api-access-token/' ); ?></p>
						<p></p>
					</td>
				</tr>
			</table>
			<h2><?php esc_html_e( 'Rakuten Affiliate', 'wp-associate-post-r2' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"></th>
					<td>
						<?php
						if ( ! isset( $option['rakuten_enable'] ) ) {
							$option['rakuten_enable'] = 0;
						}
						?>
						<input type="hidden" name="wp-associate-post-r2[rakuten_enable]" value="0"/>
						<input type="checkbox" name="wp-associate-post-r2[rakuten_enable]" value="1" id="wp-associate-post-r2[rakuten_enable]" <?php checked( $option['rakuten_enable'], '1' ); ?> />
						<label for="wp-associate-post-r2[rakuten_enable]"><?php esc_html_e( 'Enable Rakuten Affiliate links', 'wp-associate-post-r2' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Application ID', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['rakuten_application_id'] ) ) {
							$option['rakuten_application_id'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[rakuten_application_id]" value="<?php echo esc_attr( $option['rakuten_application_id'] ); ?>" size="40"/>
						<p><?php printf( __( '<a href="%s" target="_blank">Check here</a> for information on how to acquire an Application ID and an Affiliate ID.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/rakuten-appli-id-affili-id/' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Affiliate ID', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['rakuten_affiliate_id'] ) ) {
							$option['rakuten_affiliate_id'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[rakuten_affiliate_id]" value="<?php echo esc_attr( $option['rakuten_affiliate_id'] ); ?>" size="40"/>
						<p><?php printf( __( '<a href="%s" target="_blank">Check here</a> for information on how to acquire an Application ID and an Affiliate ID.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/rakuten-appli-id-affili-id/' ); ?></p>
					</td>
				</tr>
			</table>
			<div class="wpap-credit">
				<!-- Rakuten Web Services Attribution Snippet FROM HERE -->
				<a href="https://webservice.rakuten.co.jp/" target="_blank">Supported by Rakuten Developers</a>
				<!-- Rakuten Web Services Attribution Snippet TO HERE -->
			</div>
			<h2><?php esc_html_e( 'Yahoo Shopping / ValueCommerce Affiliate', 'wp-associate-post-r2' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"></th>
					<td>
						<?php
						if ( ! isset( $option['yahoo_enable'] ) ) {
							$option['yahoo_enable'] = 0;
						}
						?>
						<input type="hidden" name="wp-associate-post-r2[yahoo_enable]" value="0"/>
						<input type="checkbox" name="wp-associate-post-r2[yahoo_enable]" value="1" id="wp-associate-post-r2[yahoo_enable]" <?php checked( $option['yahoo_enable'], '1' ); ?> />
						<label for="wp-associate-post-r2[yahoo_enable]"><?php esc_html_e( 'Enable Yahoo Shopping links', 'wp-associate-post-r2' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'sid', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['yahoo_vc_sid'] ) ) {
							$option['yahoo_vc_sid'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[yahoo_vc_sid]" value="<?php echo esc_attr( $option['yahoo_vc_sid'] ); ?>" size="20"/>
						<p><?php printf( __( '<a href="%s" target="_blank">Check here</a> for information on how to acquire a sid and a pid.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/yahoo-sid-pid/' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'pid', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['yahoo_vc_pid'] ) ) {
							$option['yahoo_vc_pid'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[yahoo_vc_pid]" value="<?php echo esc_attr( $option['yahoo_vc_pid'] ); ?>" size="20"/>
						<p><?php printf( __( '<a href="%s" target="_blank">Check here</a> for information on how to acquire a sid and a pid.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/yahoo-sid-pid/' ); ?></p>
					</td>
				</tr>
			</table>
                <h2><?php esc_html_e( 'Moshimo Affiliate', 'wp-associate-post-r2' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"></th>
					<td>
						<?php
						if ( ! isset( $option['moshimo_enable'] ) ) {
							$option['moshimo_enable'] = 0;
						}
						?>
						<input type="hidden" name="wp-associate-post-r2[moshimo_enable]" value="0"/>
						<input type="checkbox" name="wp-associate-post-r2[moshimo_enable]" value="1" id="wp-associate-post-r2[moshimo_enable]" <?php checked( $option['moshimo_enable'], '1' ); ?> />
						<label for="wp-associate-post-r2[moshimo_enable]"><?php esc_html_e( 'Use Moshimo Affiliate links', 'wp-associate-post-r2' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Amazon a_id', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['moshimo_amazon_aid'] ) ) {
							$option['moshimo_amazon_aid'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[moshimo_amazon_aid]" value="<?php echo esc_attr( $option['moshimo_amazon_aid'] ); ?>" size="20"/>
						<p><?php printf( __( '<a href="%s" target="_blank">Check here</a> for information on how to acquire an a_id.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/moshimo-setting/' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Rakuten Ichiba a_id', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['moshimo_rakuten_aid'] ) ) {
							$option['moshimo_rakuten_aid'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[moshimo_rakuten_aid]" value="<?php echo esc_attr( $option['moshimo_rakuten_aid'] ); ?>" size="20"/>
						<p><?php printf( __( '<span class="wpap-orange">Moshimo affiliates would only use links to Rakuten Ichiba through "Amazon with All" rather than links to individual products on Rakuten Ichiba or Rakuten Books. (In order to comply with the terms of service for Rakuten Web Service)</span><br /><a href="%s" target="_blank">Check here</a> for information on how to acquire detailed specifications and an a_id.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/moshimo-setting/' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Yahoo Shopping a_id', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['moshimo_yahoo_aid'] ) ) {
							$option['moshimo_yahoo_aid'] = '';
						}
						?>
						<input type="text" name="wp-associate-post-r2[moshimo_yahoo_aid]" value="<?php echo esc_attr( $option['moshimo_yahoo_aid'] ); ?>" size="20"/>
						<p><?php printf( __( '<a href="%s" target="_blank">Check here</a> for information on how to acquire an a_id.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/moshimo-setting/' ); ?></p>
					</td>
				</tr>
			</table>
			<h2><?php esc_html_e( 'Template', 'wp-associate-post-r2' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Skin CSS', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						$skin_css_option = array(
							'default-1' => __( 'Standard (skin-standard.css)', 'wp-associate-post-r2' ),
							'default-2' => __( 'Square (skin-square.css)', 'wp-associate-post-r2' ),
							'default-3' => __( 'Circle (skin-circle.css)', 'wp-associate-post-r2' ),
							'default-4' => __( 'Weave (skin-weave.css)', 'wp-associate-post-r2' ),
							'default-5' => __( 'Shadow (skin-shadow.css)', 'wp-associate-post-r2' ),
						);

						$skin_file = preg_grep( '/^(wpap-)[a-zA-Z0-9-]+(.css)$/', scandir( get_stylesheet_directory() ) );

						if ( ! empty( $skin_file ) ) {
							$skin_css_option += array_combine( $skin_file, $skin_file );
						}

						if ( ! isset( $option['skin_css'] ) ) {
							$option['skin_css'] = 'default-1';
						}
						?>
						<select name="wp-associate-post-r2[skin_css]">
							<?php foreach ( $skin_css_option as $key => $value ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $option['skin_css'] ); ?>><?php echo esc_html( $value ); ?></option>
							<?php endforeach; ?>
						</select>
						<p><?php printf( __( 'Users may also use a customized CSS file. When doing so, save the CSS file in the existing theme directory using the naming format <code>wpap-*****.css</code>.<br /><a href="%s" target="_blank">Check here</a> for more information.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/css-skin/' ); ?></p>
					</td>
				</tr>
			</table>
			<h2><?php esc_html_e( 'Other', 'wp-associate-post-r2' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Google Analytics', 'wp-associate-post-r2' ); ?></th>
					<td>
						<?php
						if ( ! isset( $option['analytics_click_tracking'] ) ) {
							$option['analytics_click_tracking'] = 0;
						}
						?>
						<input type="hidden" name="wp-associate-post-r2[analytics_click_tracking]" value="0"/>
						<input type="checkbox" name="wp-associate-post-r2[analytics_click_tracking]" value="1" id="wp-associate-post-r2[analytics_click_tracking]" <?php checked( $option['analytics_click_tracking'], '1' ); ?> />
						<label for="wp-associate-post-r2[analytics_click_tracking]"><?php esc_html_e( 'Track clicks with Google Analytics', 'wp-associate-post-r2' ); ?></label>
						<p><?php printf( __( 'Requires Google Analytics on your blog. <a href="%s" target="_blank">Check here</a> for information on how to view clicks report.', 'wp-associate-post-r2' ), 'https://wp-ap.net/help/google-analytics-click-event/' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<div class="wpap-option-tool">
		<h2><?php esc_html_e( 'Clear Cache', 'wp-associate-post-r2' ); ?></h2>
		<div class="wpap-option-tool-inner">
			<input type="button" value="<?php esc_attr_e( 'Clear all product data cache', 'wp-associate-post-r2' ); ?>" class="button button-secondary" id="cache_clear">
			<p><?php esc_html_e( 'Product data will be cached for 24 hours. Use this when you want to force update data.', 'wp-associate-post-r2' ); ?></p>
		</div>
	</div>
	<div class="wpap-option-tool">
		<h2><?php esc_html_e( 'Import / Export Settings', 'wp-associate-post-r2' ); ?></h2>
		<form method="post" class="wpap-option-tool-inner">
			<?php wp_nonce_field( 'wpap_option_export' ); ?>
			<input type="submit" name="option_export_submit" class="button button-secondary" value="<?php esc_attr_e( 'Export', 'wp-associate-post-r2' ); ?>">
		</form>
		<form method="post" enctype="multipart/form-data" class="wpap-option-tool-inner" id="import_form">
			<?php wp_nonce_field( 'wpap_option_import' ); ?>
			<input type="file" name="option_import_file" />
			<input type="submit" name="option_import_submit" class="button button-secondary" value="<?php esc_attr_e( 'Import', 'wp-associate-post-r2' ); ?>">
		</form>
	</div>
</div>