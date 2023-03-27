<?php if ( ! defined( 'WPAP_ENABLED' ) ) { exit; } ?>
<span class="wpap-tpl wpap-tpl-text wpap-tpl-<?php echo $item['Service']; ?><?php if ( '' !== $item['Class'] ) { echo ' ' . $item['Class']; } ?>">
	<a href="<?php echo $item['URL']; ?>" rel="nofollow" class="wpap-link" target="_blank" data-click-tracking="<?php echo $item['ServiceName'] . ' ' . $item['ID'] . ' ' . $item['Title']; ?>">
		<?php echo $item['Title']; ?>
	</a>
</span>
