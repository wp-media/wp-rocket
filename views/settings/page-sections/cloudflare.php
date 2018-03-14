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
			<img src="<?php echo WP_ROCKET_ASSETS_IMG_URL; ?>/logo-cloudflare.svg" width="246" height="35" alt="Logo Cloudflare">
		</h2>
	</div>
	<?php $this->render_settings_sections( $data['id'] ); ?>
	<div class="wpr-optionHeader">
		<h3 class="wpr-title2"><?php esc_html_e( 'Cloudflare Cache', 'rocket' ); ?></h3>
	</div>
	<div class="wpr-fieldsContainer">
		<div class="wpr-fieldsContainer-description">
			<?php
			printf(
				// translators: %s is a "Learn more" link.
				__( 'Purges cached resources for your website. %s', 'rocket' ),
				'<a href="' . esc_url( __( 'https://support.cloudflare.com/hc/en-us/articles/200169246', 'rocket' ) ) . '" target="_blank">' . __( 'Learn more', 'rocket' ) . '</a>'
			);
			?>
		</div><br>
		<?php
		$this->render_action_button( 'link', 'rocket_purge_cloudflare', [
			'label'      => __( 'Clear all Cloudflare cache files', 'rocket' ),
			'attributes' => [
				'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-trash',
			],
		]);
		?>
	</div>
</div>
