<?php
/**
 * Settings page template.
 *
 * @since 3.0
 *
 * @param array $data {
 *      @type string $slug WP Rocket slug.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

settings_errors( $data['slug'] ); ?>
<div class="wrap">
	<?php $heading_tag = version_compare( $GLOBALS['wp_version'], '4.3' ) >= 0 ? 'h1' : 'h2'; ?>
	<<?php echo $heading_tag; ?>><?php echo esc_html( get_admin_page_title() ); ?></<?php echo $heading_tag; ?>>
	<?php $this->render_navigation(); ?>
	<?php
		// translators: %s = Plugin version number.
		printf( __( 'version %s', 'rocket' ), WP_ROCKET_VERSION );
	?>
	<form action="options.php" method="POST" id="<?php echo esc_attr( $data['slug'] ); ?>_options">
		<?php settings_fields( $data['slug'] ); ?>
		<?php $this->render_form_sections(); ?>
		<?php $this->render_hidden_fields(); ?>
		<input type="submit" value="<?php esc_attr_e( 'Save Options', 'rocket' ); ?>">
	</form>
	<?php $this->render_tools_section(); ?>
</div>
