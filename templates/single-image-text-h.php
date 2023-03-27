<?php if ( ! defined( 'WPAP_ENABLED' ) ) { exit; } ?>
<div class="wpap-tpl wpap-tpl-image-text wpap-tpl-image-text-h wpap-tpl-<?php echo $item['Service']; ?><?php if ( '' !== $item['Class'] ) { echo ' ' . $item['Class']; } ?>">
	<p class="wpap-image">
		<a href="<?php echo $item['URL']; ?>" rel="nofollow" class="wpap-link" target="_blank" data-click-tracking="<?php echo $item['ServiceName'] . ' ' . $item['ID'] . ' ' . $item['Title']; ?>">
			<img src="<?php echo $item['Image']; ?>" alt="<?php echo $item['Title']; ?>" class="object-fit" />
		</a>
	</p>
	<p class="wpap-title">
		<a href="<?php echo $item['URL']; ?>" rel="nofollow" class="wpap-link" target="_blank" data-click-tracking="<?php echo $item['ServiceName'] . ' ' . $item['ID'] . ' ' . $item['Title']; ?>">
			<?php echo $item['Title']; ?>
		</a>
	</p>
</div>
