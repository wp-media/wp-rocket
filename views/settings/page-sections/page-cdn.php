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
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-cdn"><?php echo esc_html( $data['title'] ); ?></h2>
	</div>
	<?php
	/**
	 * Fires before displaying CDN sections on WP Rocket settings page
	 *
	 * @since 3.5
	 */
	do_action( 'rocket_before_cdn_sections' );
	?>
	<?php $this->render_settings_sections( $data['id'] ); ?>
	<?php
	/**
	 * Fires after displaying CDN sections on WP Rocket settings page
	 *
	 * @since 3.5
	 */
	do_action( 'rocket_after_cdn_sections' );
	?>
</div>
