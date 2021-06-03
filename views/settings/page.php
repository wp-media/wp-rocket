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

defined( 'ABSPATH' ) || exit;

settings_errors( $data['slug'] ); ?>
<div class="wpr-wrap wrap">
	<h1 class="screen-reader-text"><?php esc_html_e( 'WP Rocket Settings', 'rocket' ); ?></h1>
	<div class="wpr-body">

		<header class="wpr-Header">
			<div class="wpr-Header-logo">
				<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'logo-wprocket-dark.svg' ); ?>" width="163" height="44" alt="Logo WP Rocket" class="wpr-Header-logo-desktop">
				<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'picto-wprocket-dark.svg' ); ?>" width="28" height="50" alt="Logo WP Rocket" class="wpr-Header-logo-mobile">
			</div>
			<div class="wpr-Header-nav">
				<?php $this->render_navigation(); ?>
			</div>
			<div class="wpr-Header-footer">
				<?php
				// translators: %s = Plugin version number.
				echo esc_html( sprintf( __( 'version %s', 'rocket' ), rocket_get_constant( 'WP_ROCKET_VERSION' ) ) );
				?>
			</div>
		</header>

		<section class="wpr-Content">
			<form action="options.php" method="POST" id="<?php echo esc_attr( $data['slug'] ); ?>_options">
				<?php settings_fields( $data['slug'] ); ?>
				<?php $this->render_form_sections(); ?>
				<?php $this->render_hidden_fields(); ?>
				<input type="submit" class="wpr-button" id="wpr-options-submit" value="<?php echo esc_attr( $data['btn_submit_text'] ); ?>">
			</form>
			<?php
			if ( rocket_valid_key() ) {
				if (
					! \Imagify_Partner::has_imagify_api_key()
					&&
					! rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' )
				) {
					$this->render_imagify_section();
				}
				$this->render_tools_section();
				$this->render_tutorials_section();
				?>
			<div class="wpr-Content-tips">
				<div class="wpr-radio wpr-radio--reverse wpr-radio--tips">
					<input type="checkbox" class="wpr-js-tips" id="wpr-js-tips" value="1" checked>
					<label for="wpr-js-tips">
						<span data-l10n-active="On"
							data-l10n-inactive="Off" class="wpr-radio-ui"></span>
						<?php esc_html_e( 'Show Sidebar', 'rocket' ); ?></label>
				</div>
			</div>
				<?php
			}
			?>
		</section>

		<aside class="wpr-Sidebar">
			<?php $this->render_part( 'sidebar' ); ?>
		</aside>
	</div>

	<div class="wpr-Popin wpr-Popin-Analytics">
		<div class="wpr-Popin-header">
			<h2 class="wpr-title1"><?php esc_html_e( 'Rocket Analytics', 'rocket' ); ?></h2>
			<button class="wpr-Popin-close wpr-Popin-Analytics-close wpr-icon-close"></button>
		</div>
		<div class="wpr-Popin-content">
			<p>
			<?php
				// translators: %1$s = <strong>, %2$s = </strong>.
				printf( esc_html__( 'Below is a detailed view of all data WP Rocket will collect %1$sif granted permission.%2$s', 'rocket' ), '<strong>', '</strong>' );
			?>
			</p>
			<?php echo rocket_data_collection_preview_table(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
			<div class="wpr-Popin-flex">
				<p><?php esc_html_e( 'WP Rocket will never transmit any domain names or email addresses (except for license validation), IP addresses, or third-party API keys.', 'rocket' ); ?></p>
				<div>
					<button class="wpr-button wpr-button--small wpr-button--icon wpr-icon-check wpr-button--blue"><?php esc_html_e( 'Activate Rocket analytics', 'rocket' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="wpr-Popin-overlay"></div>
	<?php
	/**
	 * Fires after the Settings page content
	 *
	 * @since 3.5
	 * @author Remy Perona
	 */
	do_action( 'rocket_settings_page_footer' );
	?>
</div>
