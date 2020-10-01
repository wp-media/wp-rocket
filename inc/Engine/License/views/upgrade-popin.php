<?php
/**
 * Upgrade section template.
 *
 * @since 3.7.3
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<div class="wpr-Popin wpr-Popin-Upgrade">
	<div class="wpr-Popin-header">
		<h2 class="wpr-title1"><?php esc_html_e( 'Speed Up More Websites', 'rocket' ); ?></h2>
		<button class="wpr-Popin-close wpr-Popin-Upgrade-close wpr-icon-close"></button>
	</div>
	<div class="wpr-Popin-content">
		<p><?php esc_html_e( 'Below is a detailed view of all data WP Rocket will collect <strong>if granted permission.</strong>', 'rocket' ); ?></p>
		<div class="wpr-Popin-flex">
			<?php foreach ( $data['upgrades'] as $upgrade ) : ?>
			<div>
				<h3><?php echo $upgrade['name']; ?></h3>
				<span><?php echo $upgrade['price']; ?></span>
				<span><?php echo $upgrade['websites']; ?> websites</span>
				<a href="<?php echo $upgrade['upgrade_url']; ?>">Upgrade to <?php echo $upgrade['name']; ?></a>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
