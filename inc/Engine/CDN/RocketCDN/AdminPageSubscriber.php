<?php

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
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
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Beacon instance
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Constructor
	 *
	 * @param APIClient    $api_client    RocketCDN API Client instance.
	 * @param Options_Data $options       WP Rocket options instance.
	 * @param Beacon       $beacon        Beacon instance.
	 * @param string       $template_path Path to the templates.
	 */
	public function __construct( APIClient $api_client, Options_Data $options, Beacon $beacon, $template_path ) {
		parent::__construct( $template_path );

		$this->api_client = $api_client;
		$this->options    = $options;
		$this->beacon     = $beacon;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_dashboard_after_account_data' => 'display_rocketcdn_status',
			'rocket_cdn_settings_fields'          => 'rocketcdn_field',
			'admin_post_rocket_purge_rocketcdn'   => 'purge_cdn_cache',
			'rocket_settings_page_footer'         => 'add_subscription_modal',
			'http_request_args'                   => [ 'preserve_authorization_token', PHP_INT_MAX, 2 ],
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
		if ( $this->is_white_label_account() ) {
			return;
		}

		$subscription_data = $this->api_client->get_subscription_data();

		if ( 'running' === $subscription_data['subscription_status'] ) {
			$label           = __( 'Next Billing Date', 'rocket' );
			$status_class    = ' wpr-isValid';
			$container_class = '';
			$status_text     = date_i18n( get_option( 'date_format' ), strtotime( $subscription_data['subscription_next_date_update'] ) );
			$is_active       = true;
		} elseif ( 'cancelled' === $subscription_data['subscription_status'] ) {
			$label           = '';
			$status_class    = ' wpr-isInvalid';
			$container_class = ' wpr-flex--egal';
			$status_text     = __( 'No Subscription', 'rocket' );
			$is_active       = false;
		}

		$data = [
			'is_live_site'    => rocket_is_live_site(),
			'container_class' => $container_class,
			'label'           => $label,
			'status_class'    => $status_class,
			'status_text'     => $status_text,
			'is_active'       => $is_active,
		];

		echo $this->generate( 'dashboard-status', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Adds the RocketCDN fields to the CDN section
	 *
	 * @since  3.5
	 *
	 * @param array $fields CDN settings fields.
	 *
	 * @return array
	 */
	public function rocketcdn_field( $fields ) {
		if ( $this->is_white_label_account() ) {
			return $fields;
		}

		$subscription_data = $this->api_client->get_subscription_data();

		if ( 'running' !== $subscription_data['subscription_status'] ) {
			return $fields;
		}

		$helper_text = __( 'Your RocketCDN subscription is currently active.', 'rocket' );
		$cdn_cnames  = $this->options->get( 'cdn_cnames', [] );

		if ( empty( $cdn_cnames ) || $cdn_cnames[0] !== $subscription_data['cdn_url'] ) {
			$helper_text = sprintf(
				// translators: %1$s = opening <code> tag, %2$s = CDN URL, %3$s = closing </code> tag.
				__( 'To use RocketCDN, replace your CNAME with %1$s%2$s%3$s.', 'rocket' ),
				'<code>',
				$subscription_data['cdn_url'],
				'</code>'
			);
		}

		$beacon = $this->beacon->get_suggest( 'rocketcdn' );

		$more_info = sprintf(
			// translators: %1$is = opening link tag, %2$s = closing link tag.
			__( '%1$sMore Info%2$s', 'rocket' ),
			'<a href="' . esc_url( $beacon['url'] ) . '" data-beacon-article="' . esc_attr( $beacon['id'] ) . '" rel="noopener noreferrer" target="_blank">',
			'</a>'
		);

		$fields['cdn_cnames'] = [
			'type'        => 'rocket_cdn',
			'label'       => __( 'CDN CNAME(s)', 'rocket' ),
			'description' => __( 'Specify the CNAME(s) below', 'rocket' ),
			'helper'      => $helper_text . ' ' . $more_info,
			'default'     => '',
			'section'     => 'cnames_section',
			'page'        => 'page_cdn',
			'beacon'      => [
				'url' => $beacon['url'],
				'id'  => $beacon['id'],
			],
		];

		return $fields;
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

		$iframe_src = add_query_arg(
			[
				'website'  => home_url(),
				'callback' => rest_url( 'wp-rocket/v1/rocketcdn/' ),
			],
			rocket_get_constant( 'WP_ROCKET_WEB_MAIN' ) . 'cdn/iframe'
		);
		?>
		<div class="wpr-rocketcdn-modal" id="wpr-rocketcdn-modal" aria-hidden="true">
			<div class="wpr-rocketcdn-modal__overlay" tabindex="-1">
				<div class="wpr-rocketcdn-modal__container" role="dialog" aria-modal="true" aria-labelledby="wpr-rocketcdn-modal-title">
					<div id="wpr-rocketcdn-modal-content">
						<iframe id="rocketcdn-iframe" src="<?php echo esc_url( $iframe_src ); ?>" width="674" height="425"></iframe>
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
