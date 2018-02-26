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
<div class="wpr-wrap wrap">
	<div class="wpr-body">
		<header class="wpr-Header">
			<!-- <?php $heading_tag = version_compare( $GLOBALS['wp_version'], '4.3' ) >= 0 ? 'h1' : 'h2'; ?>
			<<?php echo $heading_tag; ?>><?php echo esc_html( get_admin_page_title() ); ?></<?php echo $heading_tag; ?>> -->
			<div class="wpr-logo">
				<img src="<?php echo WP_ROCKET_ASSETS_IMG_URL; ?>/logo-wprocket-light.svg" width="163" height="44" alt="Logo WP Rocket">
			</div>
			<?php $this->render_navigation(); ?>
			<?php
			// translators: %s = Plugin version number.
			printf( __( 'version %s', 'rocket' ), WP_ROCKET_VERSION );
			?>
		</header>
		<div class="wpr-Content">
			<form action="options.php" method="POST" id="<?php echo esc_attr( $data['slug'] ); ?>_options">
				<?php settings_fields( $data['slug'] ); ?>
				<?php $this->render_form_sections(); ?>
				<?php $this->render_hidden_fields(); ?>
				<input type="submit" value="<?php esc_attr_e( 'Save Changes', 'rocket' ); ?>">
			</form>
		</div>
		<div class="wpr-Sidebar">
			<?php $this->render_tools_section(); ?>
		</div>
	</div>
</div>
