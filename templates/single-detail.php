<?php if ( ! defined( 'WPAP_ENABLED' ) ) { exit; } ?>
<div class="wpap-tpl wpap-tpl-detail wpap-tpl-<?php echo $item['Service']; ?><?php if ( '' !== $item['Class'] ) { echo ' ' . $item['Class']; } ?>">
	<a href="<?php echo $item['URL']; ?>" rel="nofollow" class="wpap-link" target="_blank" data-click-tracking="<?php echo $item['ServiceName'] . ' ' . $item['ID'] . ' ' . $item['Title']; ?>">
		<div class="wpap-image"><img src="<?php echo $item['Image']; ?>" alt="<?php echo $item['Title']; ?>" /></div>
		<p class="wpap-title"><?php echo $item['Title']; ?></p>
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
		<div class="wpap-service"><?php echo $item['ServiceName']; ?></div>
	</a>
</div>