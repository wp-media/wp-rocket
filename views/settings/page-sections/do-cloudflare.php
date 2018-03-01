<?php
/**
 * Cloudflare section template.
 *
 * @since 3.0
 *
 * @param array {
 *     Section arguments.
 *
 *     @type string $id    Page section identifier.
 *     @type string $title Page section title.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1">
			<img src="<?php echo WP_ROCKET_ASSETS_IMG_URL; ?>/logo-cloudflare.svg" width="153" height="51" alt="Logo WP Rocket">
		</h2>
	</div>
	<?php $this->render_settings_sections( $data['id'] ); ?>
	<div class="wpr-optionHeader">
		<h3 class="wpr-title2"><?php esc_html_e( 'Cloudflare Cache', 'rocket' ); ?></h3>
	</div>
	<p><?php esc_html_e( 'Purges cached resources for your website. Learn more', 'rocket' ); ?></p>
	<?php
	$this->render_action_button( 'link', 'purge_cloudflare', [
		'label'      => __( 'Clear all Cloudflare cache files', 'rocket' ),
		'attributes' => [
			'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-trash',
		],
	]);
	?>
</div>
