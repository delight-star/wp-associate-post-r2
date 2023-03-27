<?php if ( ! defined( 'WPAP_ENABLED' ) ) { exit; } ?>
<div class="wpap-tpl wpap-tpl-with-detail<?php if ( '' !== $item['Class'] ) { echo ' ' . $item['Class']; } ?>">
	<div class="wpap-image">
		<a href="<?php echo $item['URL']; ?>" rel="nofollow" target="_blank" data-click-tracking="<?php printf( __( 'Amazon %1$s %2$s', 'wp-associate-post-r2' ), $item['ID'], $item['Title'] ); ?>">
			<img src="<?php echo $item['Image']; ?>" alt="<?php echo $item['Title']; ?>" />
		</a>
	</div>
	<p class="wpap-title">
		<a href="<?php echo $item['URL']; ?>" rel="nofollow" target="_blank" data-click-tracking="<?php printf( __( 'Amazon %1$s %2$s', 'wp-associate-post-r2' ), $item['ID'], $item['Title'] ); ?>">
			<?php echo $item['Title']; ?>
		</a>
	</p>
	<?php if ( isset( $item['Author'] ) ) : ?><div class="wpap-creator"><?php echo implode( self::GLUE_STRING, $item['Author'] ); ?></div><?php endif; ?>
	<?php if ( isset( $item['Artist'] ) ) : ?><div class="wpap-creator"><?php echo implode( self::GLUE_STRING, $item['Artist'] ); ?></div><?php endif; ?>
	<?php if ( isset( $item['Price'] ) ) : ?>
		<div class="wpap-price">
			<?php echo $item['Price']; ?>
			<span class="wpap-date"><?php printf( __( '(as of %s)', 'wp-associate-post-r2' ), $item['Date'] ); ?></span>
		</div>
	<?php endif; ?>
	<?php if ( isset( $item['Release'] ) ) : ?>
		<div class="wpap-release"><?php printf( __( 'Release date: %s', 'wp-associate-post-r2' ), $item['Release'] ); ?></div>
	<?php endif; ?>
	<p class="wpap-link">
		<a href="<?php echo $item['URL']; ?>" rel="nofollow" class="wpap-link-amazon" target="_blank" data-click-tracking="<?php printf( __( 'Amazon %1$s %2$s', 'wp-associate-post-r2' ), $item['ID'], $item['Title'] ); ?>">
			<span><?php echo $with_label['amazon']; ?></span>
		</a>
		<?php if ( isset( $item['RakutenURL'] ) ) : ?>
			<?php if ( 'ichiba' === $item['RakutenType'] ) : ?>
				<a href="<?php echo $item['RakutenURL']; ?>" rel="nofollow" class="wpap-link-rakuten" target="_blank" data-click-tracking="<?php printf( __( 'Rakuten Ichiba Search: %s', 'wp-associate-post-r2' ), $item['Search'] ); ?>">
					<span><?php echo $with_label['rakuten-ichiba']; ?></span>
					</a>
				<?php else : ?>
				<a href="<?php echo $item['RakutenURL']; ?>" rel="nofollow" class="wpap-link-rakuten" target="_blank" data-click-tracking="<?php printf( __( 'Rakuten Books %1$s %2$s', 'wp-associate-post-r2' ), $item['ISBNJAN'], $item['Title'] ); ?>">
					<span><?php echo $with_label['rakuten-books']; ?></span>
				</a>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( isset( $item['YahooURL'] ) ) : ?>
			<a href="<?php echo $item['YahooURL']; ?>" rel="nofollow" class="wpap-link-yahoo" target="_blank" data-click-tracking="<?php printf( __( 'Yahoo Shopping Search: %s', 'wp-associate-post-r2' ), $item['Search'] ); ?>">
				<span><?php echo $with_label['yahoo']; ?></span>
			</a>
		<?php endif; ?>
	</p>
	<div class="wpap-note"><?php esc_html_e( 'Amazon product information', 'wp-associate-post-r2' ); ?></div>
</div>