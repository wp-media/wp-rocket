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
	<h1 class="screen-reader-text"><?php _e( 'WP Rocket Settings', 'rocket' ); ?></h1>
	<div class="wpr-body">

		<header class="wpr-Header">
			<div class="wpr-Header-logo">
				<img src="<?php echo WP_ROCKET_ASSETS_IMG_URL; ?>logo-wprocket-dark.svg" width="163" height="44" alt="Logo WP Rocket" class="wpr-Header-logo-desktop">
				<img src="<?php echo WP_ROCKET_ASSETS_IMG_URL; ?>picto-wprocket-dark.svg" width="28" height="50" alt="Logo WP Rocket" class="wpr-Header-logo-mobile">
			</div>
			<div class="wpr-Header-nav">
				<?php $this->render_navigation(); ?>
			</div>
			<div class="wpr-Header-footer">
				<?php
				// translators: %s = Plugin version number.
				printf( __( 'version %s', 'rocket' ), WP_ROCKET_VERSION );
				?>
			</div>
		</header>

		<section class="wpr-Content">
			<form action="options.php" method="POST" id="<?php echo esc_attr( $data['slug'] ); ?>_options">
				<?php settings_fields( $data['slug'] ); ?>
				<?php $this->render_form_sections(); ?>
				<?php $this->render_hidden_fields(); ?>
				<input type="submit" class="wpr-button" id="wpr-options-submit" value="<?php esc_attr_e( 'Save Changes', 'rocket' ); ?>">
			</form>
			<?php
			if ( rocket_valid_key() ) {
				if ( ! \Imagify_Partner::has_imagify_api_key() ) {
					$this->render_imagify_section();
				}

				$this->render_tools_section();
			?>
			<div class="wpr-Content-tips">
				<div class="wpr-radio wpr-radio--reverse wpr-radio--tips">
					<input type="checkbox" class="wpr-js-tips" id="wpr-js-tips" value="1" checked>
					<label for="wpr-js-tips">
						<span data-l10n-active="<?php echo esc_attr_x( 'On', 'Active state of checkbox', 'rocket' ); ?>"
  data-l10n-inactive="<?php echo esc_attr_x( 'Off', 'Inactive state of checkbox', 'rocket' ); ?>" class="wpr-radio-ui"></span>
						<?php _e( 'Show Sidebar', 'rocket' ); ?></label>
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

	<div class="wpr-Popin wpr-Popin-Beta">
		<div class="wpr-Popin-header">
			<h2 class="wpr-title1"><?php esc_html_e( 'Rocket Tester', 'rocket' ); ?></h2>
			<button class="wpr-Popin-close wpr-Popin-Beta-close wpr-icon-close"></button>
		</div>
		<div class="wpr-Popin-content">
			<p><?php esc_html_e( 'Thanks for choosing to participate in the WP Rocket beta program!', 'rocket' ); ?></p>
			<p><?php esc_html_e( 'A beta version is usually one that has new features and improvements, but we want to test it a little more before full launch.', 'rocket' ); ?></p>
			<p><?php esc_html_e( 'We’d love it if you took our beta versions for a ride, but please keep in mind that it might be less stable than our other releases. Don’t worry, you can switch back to a full release version at any time.', 'rocket' ); ?></p>
			<p><?php esc_html_e( 'Your mission: please send all feedback about our beta versions, including bug reports, to support@wp-rocket.me', 'rocket' ); ?></p>
			<div class="wpr-Popin-flex">
				<p><?php esc_html_e( 'If you don’t want to join the beta program, simply close this window.', 'rocket' ); ?></p>
				<div>
					<button class="wpr-button wpr-button--small wpr-button--icon wpr-icon-check wpr-button--blue"><?php esc_html_e( 'Activate Rocket Tester', 'rocket' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="wpr-Popin wpr-Popin-Analytics">
		<div class="wpr-Popin-header">
			<h2 class="wpr-title1"><?php esc_html_e( 'Rocket Analytics', 'rocket' ); ?></h2>
			<button class="wpr-Popin-close wpr-Popin-Analytics-close wpr-icon-close"></button>
		</div>
		<div class="wpr-Popin-content">
			<p><?php esc_html_e( 'Below is a detailed view of all data WP Rocket will collect <strong>if granted permission.</strong>', 'rocket' ); ?></p>
			<?php echo rocket_data_collection_preview_table(); ?>
			<div class="wpr-Popin-flex">
				<p><?php _e( 'WP Rocket will never transmit any domain names or email addresses (except for license validation), IP addresses, or third-party API keys.', 'rocket' ); ?></p>
				<div>
					<button class="wpr-button wpr-button--small wpr-button--icon wpr-icon-check wpr-button--blue"><?php esc_html_e( 'Activate Rocket analytics', 'rocket' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<div class="wpr-Popin-overlay"></div>
</div>
