<?php
namespace WP_Rocket\Subscriber\CDN;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for RocketCDN integration
 *
 * @since 3.5
 * @author Remy Perona
 */
class RocketCDNSubscriber implements Subscriber_Interface {
	/**
	 * WP Options API instance
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options      $options_api WP Options API instance.
	 * @param Options_Data $options     WP Rocket Options instance.
	 */
	public function __construct( Options $options_api, Options_Data $options ) {
		$this->options_api = $options_api;
		$this->options     = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init'                       => [
				[ 'register_enable_route' ],
				[ 'register_disable_route' ],
			],
			'admin_notices'                       => 'promote_rocket_cdn_notice',
			'rocket_dashboard_after_account_data' => 'dashboard_section',
			'rocket_before_cdn_sections'          => 'rocket_cdn_cta',
			'rocket_cdn_settings_fields'          => 'rocket_cdn_field',
		];
	}

	/**
	 * Register Enable route in the WP REST API
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register_enable_route() {
		register_rest_route(
			'wp-rocket/v1',
			'rocketcdn/enable',
			[
				'methods'  => 'PUT',
				'callback' => [ $this, 'enable' ],
				'args'     => [
					'email' => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_email' ],
					],
					'key'   => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_key' ],
					],
					'url'   => [
						'required'          => true,
						'validate_callback' => function( $param, $request, $key ) {
							$url = esc_url_raw( $param );

							return ! empty( $url );
						},
						'sanitize_callback' => function( $param, $request, $key ) {
							return esc_url_raw( $param );
						},
					],
				],
			]
		);
	}

	/**
	 * Register Disable route in the WP REST API
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register_disable_route() {
		register_rest_route(
			'wp-rocket/v1',
			'rocketcdn/disable',
			[
				'methods'  => 'PUT',
				'callback' => [ $this, 'disable' ],
				'args'     => [
					'email' => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_email' ],
					],
					'key'   => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_key' ],
					],
				],
			]
		);
	}

	/**
	 * Enable CDN and add RocketCDN URL to WP Rocket options
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param \WP_REST_Request $request the WP REST Request object.
	 * @return string
	 */
	public function enable( \WP_REST_Request $request ) {
		$params = $request->get_body_params();

		$cnames   = [];
		$cnames[] = $params['url'];

		$this->options->set( 'cdn', 1 );
		$this->options->set( 'cdn_cnames', $cnames );
		$this->options->set( 'cdn_zone', [ 'all' ] );

		$this->options_api->set( 'settings', $this->options->get_options() );
		$this->options_api->set( 'rocketcdn_active', 1 );

		return rest_ensure_response( __( 'RocketCDN Enabled', 'rocket' ) );
	}

	/**
	 * Disable the CDN and remove the RocketCDN URL from WP Rocket options
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param \WP_REST_Request $request the WP Rest Request object.
	 * @return string
	 */
	public function disable( \WP_REST_Request $request ) {
		$this->options->set( 'cdn', 0 );
		$this->options->set( 'cdn_cnames', [] );
		$this->options->set( 'cdn_zone', [] );

		$this->options_api->set( 'settings', $this->options->get_options() );
		$this->options_api->set( 'rocketcdn_active', 0 );

		return rest_ensure_response( __( 'RocketCDN disabled', 'rocket' ) );
	}

	/**
	 * Checks that the email sent along the request corresponds to the one saved in the DB
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param string           $param Parameter value to validate.
	 * @param \WP_REST_Request $request WP REST Request object.
	 * @param string           $key Parameter key.
	 * @return bool
	 */
	public function validate_email( $param, $request, $key ) {
		return $param === $this->options->get( 'consumer_email' );
	}

	/**
	 * Checks that the key sent along the request corresponds to the one saved in the DB
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param string           $param Parameter value to validate.
	 * @param \WP_REST_Request $request WP REST Request object.
	 * @param string           $key Parameter key.
	 * @return bool
	 */
	public function validate_key( $param, $request, $key ) {
		return $param === $this->options->get( 'consumer_key' );
	}

	/**
	 * Adds notice to promote Rocket CDN on settings page
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function promote_rocket_cdn_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		$subscription_data = $this->get_subscription_data();

		if ( $subscription_data['active'] ) {
			return;
		}

		?>
		<div class="notice notice-alt notice-warning is-dismissible">
			<h2 class="notice-title"><?php esc_html_e( 'New!', 'rocket' ); ?></h2>
			<p><?php esc_html_e( 'Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network!', 'rocket' ); ?></p>
			<p><a href="#page_cdn" class="wpr-button"><?php esc_html_e( 'Learn More', 'rocket' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * Displays the Rocket CDN section on the dashboard tab
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function dashboard_section() {
		$subscription_data = $this->get_subscription_data();
		$title             = 'Rocket CDN';
		$next_renewal      = __( 'No Subscription', 'rocket' );
		$class             = 'wpr-isInvalid';
		$container_class   = 'wpr-flex--egal';

		if ( $subscription_data['active'] ) {
			$title          .= ' ' . __( 'Next Billing Date', 'rocket' );
			$next_renewal    = date_i18n( get_option( 'date_format' ), strtotime( $subscription_data['next_renewal'] ) );
			$class           = 'wpr-isValid';
			$container_class = '';
		}

		?>
		<div class="wpr-optionHeader">
			<h3 class="wpr-title2">Rocket CDN</h3>
		</div>
		<div class="wpr-field wpr-field-account">
			<div class="wpr-flex <?php echo esc_attr( $container_class ); ?>">
				<div>
					<span class="wpr-title3"><?php echo esc_html( $title ); ?></span>
					<span class="wpr-infoAccount <?php echo esc_attr( $class ); ?>"><?php echo esc_html( $next_renewal ); ?></span>
				</div>
				<?php if ( ! $subscription_data['active'] ) : ?>
				<div>
					<a href="#page_cdn" class="wpr-button"><?php esc_html_e( 'Get Rocket CDN', 'rocket' ); ?></a>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Displays the Rocket CDN Call to Action on the CDN tab of WP Rocket settings page
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function rocket_cdn_cta() {
		$subscription_data = $this->get_subscription_data();

		if ( $subscription_data['active'] ) {
			return;
		}

		$pricing            = $this->get_pricing_data();
		$current_price      = $pricing['regular_price'];
		$regular_price      = '';
		$promotion_campaign = $pricing['promotion']['campaign'];
		$promotion_date     = date_i18n( get_option( 'date_format' ), strtotime( $pricing['promotion']['end_date'] ) );
		$nopromo_variant    = empty( $pricing['promotion']['campaign'] ) ? '--no-promo' : '';

		if ( ! empty( $pricing['promotion']['price'] ) ) {
			$regular_price = $current_price;
			$current_price = $pricing['promotion']['price'];
		}
		?>
		<div class="wpr-rocketcdn-cta-small notice-alt notice-warning">
			<div class="wpr-flex">
				<section>
					<h3 class="notice-title"><?php esc_html_e( 'Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network.', 'rocket' ); ?></strong></h3>
				</section>
				<div>
					<button class="wpr-button"><?php esc_html_e( 'Learn More', 'rocket' ); ?></button>
				</div>
			</div>
		</div>
		<div class="wpr-rocketcdn-cta">
			<?php if ( ! empty( $promotion_campaign ) ) : ?>
			<div class="wpr-flex wpr-rocketcdn-promo">
				<h3 class="wpr-title1"><?php echo esc_html( $promotion_campaign ); ?></h3>
				<p class="wpr-title2 wpr-rocketcdn-promo-date">
					<?php
					printf(
						// Translators: %s = date formatted using date_i18n() and get_option( 'date_format' ).
						esc_html__( 'Valid until %s only!', 'rocket' ),
						esc_html( $promotion_date )
					);
					?>
				</p>
			</div>
			<?php endif; ?>
			<section class="wpr-rocketcdn-cta-content<?php echo esc_attr( $nopromo_variant ); ?>">
				<h3 class="wpr-title2">Rocket CDN</h3>
				<p class="wpr-rocketcdn-cta-subtitle"><?php esc_html_e( 'Speed up your website thanks to:', 'rocket' ); ?></p>
				<div class="wpr-flex">
					<ul class="wpr-rocketcdn-features">
						<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
							<?php
							// translators: %1$s = opening strong tag, %2$s = closing strong tag.
							printf( esc_html__( 'High performance Content Delivery Network (CDN) with %1$sunlimited bandwith%2$s', 'rocket' ), '<strong>', '</strong>' );
							?>
						</li>
						<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
							<?php
							// translators: %1$s = opening strong tag, %2$s = closing strong tag.
							printf( esc_html__( 'Easy configuration: the %1$sbest CDN settings%2$s are automatically applied', 'rocket' ), '<strong>', '</strong>' );
							?>
						</li>
						<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
							<?php
							// translators: %1$s = opening strong tag, %2$s = closing strong tag.
							printf( esc_html__( 'WP Rocket integration: the CDN option is %1$sautomatically configured%2$s in our plugin', 'rocket' ), '<strong>', '</strong>' );
							?>
						</li>
					</ul>
					<div class="wpr-rocketcdn-pricing">
						<?php if ( ! empty( $regular_price ) ) : ?>
						<h4 class="wpr-title2 wpr-rocketcdn-pricing-regular"><del><?php echo esc_html( $regular_price ); ?></del></h4>
						<?php endif; ?>
						<h4 class="wpr-rocketcdn-pricing-current">
						<?php
						printf(
							// translators: %s = price of RocketCDN subscription.
							esc_html__( '%s / month', 'rocket' ),
							'<span class="wpr-title1">' . esc_html( $current_price ) . '</span>'
						);
						?>
						</h4>
						<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal"><?php esc_html_e( 'Get Started', 'rocket' ); ?></button>
					</div>
				</div>
			</section>
			<div class="wpr-rocketcdn-cta-footer">
				<a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn more about Rocket CDN', 'rocket' ); ?></a>
			</div>
			<button class="wpr-rocketcdn-cta-close<?php echo esc_attr( $nopromo_variant ); ?>"><span class="screen-reader-text"><?php esc_html_e( 'Reduce this banner', 'rocket' ); ?></span></button>
		</div>
		<?php
	}

	/**
	 * Adds the Rocket CDN fields to the CDN section
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param array $fields CDN settings fields.
	 * @return array
	 */
	public function rocket_cdn_field( $fields ) {
		$subscription_data = $this->get_subscription_data();

		if ( ! $subscription_data['active'] ) {
			return $fields;
		}

		$fields['cdn_cnames'] = [
			'type'        => 'rocket_cdn',
			'label'       => __( 'CDN CNAME(s)', 'rocket' ),
			'description' => __( 'Specify the CNAME(s) below', 'rocket' ),
			'helper'      => __( 'Rocket CDN is currently active.', 'rocket' ) . ' <button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">' . __( 'Unsubscribe', 'rocket' ) . '</button>',
			'default'     => '',
			'section'     => 'cnames_section',
			'page'        => 'page_cdn',
		];

		return $fields;
	}

	/**
	 * Gets current RocketCDN subscription data
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return array
	 */
	private function get_subscription_data() {
		return [
			'active'       => false,
			'next_renewal' => '2020-04-10T13:59:42.356081Z',
		];
	}

	/**
	 * Gets pricing & promotion data for RocketCDN
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return array
	 */
	private function get_pricing_data() {
		return [
			'regular_price' => '$7.99',
			'promotion'     => [
				'campaign' => '',
				'price'    => '',
				'end_date' => '',
			],
		];
	}
}
