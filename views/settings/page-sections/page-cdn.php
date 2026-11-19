<?php
/**
 * CDN section template.
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

defined( 'ABSPATH' ) || exit;

?>

<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-Page">
	<div class="wpr-sectionHeader wpr-sectionHeader--cdn">
		<h2 class="wpr-title1 wpr-icon-cdn"><?php echo esc_html( $data['title'] ); ?></h2>
		<?php
		/**
		 * Fires in the CDN section header to render the CDN driver tabs.
		 *
		 * @since 3.21.2
		 */
		do_action( 'rocket_cdn_driver_tabs' );
		?>
	</div>
	<div class="wpr-sectionHeader-description wpr-without-padding">
		<?php esc_html_e( 'Content Delivery speeds up loading time and improves Time to First Byte (TTFB) by serving your website’s files faster.', 'rocket' ); ?>
	</div>
	<?php
	/**
	 * Fires before displaying CDN sections on WP Rocket settings page
	 *
	 * @since 3.5
	 */
	do_action( 'rocket_before_cdn_sections' );

	$this->render_settings_sections( $data['id'] );
	/**
	 * Fires after displaying CDN sections on WP Rocket settings page
	 *
	 * @since 3.5
	 */
	do_action( 'rocket_after_cdn_sections' );
	?>
</div>
