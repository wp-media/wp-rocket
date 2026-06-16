<?php

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the RocketCDN integration in WP Rocket settings page
 *
 * @since  3.5
 */
class AdminPageSubscriber extends Abstract_Render implements Subscriber_Interface {
	/**
	 * RocketCDN API Client instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * UserClient instance
	 *
	 * @var UserClient
	 */
	private $user_client;

	/**
	 * Constructor
	 *
	 * @param APIClient  $api_client    RocketCDN API Client instance.
	 * @param UserClient $user_client   UserClient instance.
	 * @param string     $template_path Path to the templates.
	 */
	public function __construct( APIClient $api_client, $user_client, $template_path ) {
		parent::__construct( $template_path );

		$this->api_client  = $api_client;
		$this->user_client = $user_client;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_dashboard_after_account_data'        => 'display_rocketcdn_status',
			'admin_post_rocket_purge_rocketcdn'          => 'purge_cdn_cache',
			'rocket_settings_page_footer'                => 'add_subscription_modal',
			'http_request_args'                          => [ 'preserve_authorization_token', PHP_INT_MAX, 2 ],
			'rocket_insights_api_recommendations_params' => 'maybe_add_rocketcdn_to_recommendations_api_params',
		];
	}

	/**
	 * Displays the RocketCDN section on the dashboard tab
	 *
	 * @since  3.5
	 *
	 * @return void
	 */
	public function display_rocketcdn_status() {
		/**
		 * Filters the display of the RocketCDN status.
		 *
		 * @param bool $display_rocketcdn_status; true to display, false otherwise.
		 */
		if ( ! apply_filters( 'rocket_display_rocketcdn_status', true ) ) {
			return;
		}

		if ( $this->is_white_label_account() ) {
			return;
		}

		$subscription_data = $this->api_client->get_subscription_data();

		$container_class = '';
		$is_active       = false;
		$items           = [];

		if ( 'running' === $subscription_data['subscription_status'] && 'paid' === $subscription_data['plan_type'] ) {
			$items[] = [
				'label' => __( 'Plan', 'rocket' ),
				'value' => __( 'RocketCDN Pro', 'rocket' ),
				'class' => ' wpr-isValid wpr-no-icon',
			];
			$items[] = [
				'label' => __( 'Next Billing Date', 'rocket' ),
				'value' => date_i18n( get_option( 'date_format' ), strtotime( $subscription_data['subscription_next_date_update'] ) ),
				'class' => ' wpr-isValid',
			];

			$is_active = true;
		} else {
			$items[]         = [
				'label' => '',
				'value' => __( 'No RocketCDN Pro Subscription', 'rocket' ),
				'class' => ' wpr-isInvalid',
			];
			$container_class = ' wpr-flex--egal';
		}

		$data = [
			'is_live_site'    => rocket_is_live_site(),
			'container_class' => $container_class,
			'is_active'       => $is_active,
			'items'           => $items,
		];

		echo $this->generate( 'dashboard-status', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Purges the CDN cache and store the response in a transient.
	 *
	 * @since  3.5
	 *
	 * @return void
	 */
	public function purge_cdn_cache() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_purge_rocketcdn' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			wp_nonce_ays( '' );
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_die();
		}

		set_transient( 'rocketcdn_purge_cache_response', $this->api_client->purge_cache_request(), HOUR_IN_SECONDS );

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}

	/**
	 * Adds the subscription modal on the WP Rocket settings page
	 *
	 * @since  3.5
	 *
	 * @return void
	 */
	public function add_subscription_modal() {
		if ( $this->is_white_label_account() ) {
			return;
		}

		if ( ! rocket_is_live_site() ) {
			return;
		}

		// Check if user data has button URL.
		$button_url = '';
		$user_data  = $this->user_client->get_user_data();

		if ( false !== $user_data && isset( $user_data->rocketcdn->button->url ) && ! empty( $user_data->rocketcdn->button->url ) ) {
			$button_url = add_query_arg(
				'dashboard_url',
				rawurlencode(
					add_query_arg(
						[
							'page'               => WP_ROCKET_PLUGIN_SLUG,
							'rocketcdn_checkout' => 'true',
						],
						admin_url( 'options-general.php' )
					)
				),
				esc_url_raw( $user_data->rocketcdn->button->url )
			);
		}

		$iframe_src = add_query_arg(
			[
				'website'  => home_url(),
				'callback' => rest_url( 'wp-rocket/v1/rocketcdn/' ),
				'source'   => 'plugin',
			],
			'https://api.wp-rocket.me/cdn/iframe'
		);
		?>
		<script type="text/javascript">
			window.rocketcdnButtonUrl = '<?php echo $button_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>';
		</script>
		<div class="wpr-rocketcdn-modal" id="wpr-rocketcdn-modal" aria-hidden="true">
			<div class="wpr-rocketcdn-modal__overlay" tabindex="-1" data-micromodal-close>
				<div class="wpr-loader" id="wpr-rocketcdn-modal-loader"></div>
				<div class="wpr-rocketcdn-modal__container" role="dialog" aria-modal="true" aria-labelledby="wpr-rocketcdn-modal-title">
					<div id="wpr-rocketcdn-modal-content">
						<iframe id="rocketcdn-iframe" data-src="<?php echo esc_url( $iframe_src ); ?>" loading="lazy" width="674" height="425"></iframe>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Filter the arguments used in an HTTP request, to make sure our user token has not been overwritten
	 * by some other plugin.
	 *
	 * @since  3.5
	 *
	 * @param  array  $args An array of HTTP request arguments.
	 * @param  string $url  The request URL.
	 * @return array
	 */
	public function preserve_authorization_token( $args, $url ) {
		return $this->api_client->preserve_authorization_token( $args, $url );
	}

	/**
	 * Adds the 'plugin_rocketcdn' option to the recommendations API parameters if certain conditions are met.
	 *
	 * @param array $params The existing API parameters.
	 * @return array The modified API parameters with 'plugin_rocketcdn' added if applicable.
	 */
	public function maybe_add_rocketcdn_to_recommendations_api_params( array $params ): array {
		if ( ! $this->should_add_to_recommendations_api_params( $params ) ) {
			return $params;
		}

		$params['enabled_options'][] = 'plugin_rocketcdn';

		return $params;
	}

	/**
	 * Determines whether to add the 'plugin_rocketcdn' option to the recommendations API parameters.
	 *
	 * This method checks multiple conditions to decide if the user should be included:
	 * - Returns true if the account is a white label account.
	 * - Returns true if the RocketCDN standalone is active.
	 * - Returns true if CDN option in WP Rocket is enabled.
	 * - Returns false otherwise.
	 *
	 * @param array $params API params.
	 * @return bool True if the user should be added to the recommendations API parameters, false otherwise.
	 */
	private function should_add_to_recommendations_api_params( $params ): bool {
		// Return true if white label is true.
		if ( $this->is_white_label_account() ) {
			return true;
		}

		if ( ! empty( rocket_get_constant( 'ROCKETCDN_VERSION' ) ) ) {
			return true;
		}

		if ( in_array( 'cdn', $params['enabled_options'], true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if white label is enabled
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	private function is_white_label_account() {
		return (bool) rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' );
	}
}
