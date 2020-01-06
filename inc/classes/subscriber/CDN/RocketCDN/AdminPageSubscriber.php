<?php
namespace WP_Rocket\Subscriber\CDN\RocketCDN;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Settings\Beacon;
use WP_Rocket\CDN\RocketCDN\APIClient;

/**
 * Subscriber for the RocketCDN integration in WP Rocket settings page
 *
 * @since 3.5
 * @author Remy Perona
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
	 * @param APIClient    $api_client RocketCDN API Client instance.
	 * @param Options_Data $options WP Rocket options instance.
	 * @param Beacon       $beacon Beacon instance.
	 * @param string       $template_path Path to the templates.
	 */
	public function __construct( APIClient $api_client, Options_Data $options, Beacon $beacon, $template_path ) {
		parent::__construct( $template_path );

		$this->api_client = $api_client;
		$this->options    = $options;
		$this->beacon     = $beacon;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_dashboard_after_account_data' => 'display_rocketcdn_status',
			'rocket_after_cdn_sections'           => 'display_manage_subscription',
			'rocket_cdn_settings_fields'          => 'rocketcdn_field',
			'admin_post_rocket_purge_rocketcdn'   => 'purge_cdn_cache',
			'rocket_settings_page_footer'         => 'add_subscription_modal',
			'admin_ajax_save_rocketcdn_token'     => 'update_user_token',
		];
	}

	/**
	 * Displays the Rocket CDN section on the dashboard tab
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function display_rocketcdn_status() {
		$subscription_data = $this->api_client->get_subscription_data();
		$label             = '';
		$status_text       = __( 'No Subscription', 'rocket' );
		$status_class      = 'wpr-isInvalid';
		$container_class   = 'wpr-flex--egal';

		if ( $subscription_data['is_active'] ) {
			$label           = __( 'Next Billing Date', 'rocket' );
			$status_class    = 'wpr-isValid';
			$container_class = '';
		}

		if ( 'cancelled' !== $subscription_data['subscription_status'] ) {
			$status_text = date_i18n( get_option( 'date_format' ), strtotime( $subscription_data['subscription_next_date_update'] ) );
		}

		$data = [
			'container_class' => $container_class,
			'label'           => $label,
			'status_class'    => $status_class,
			'status_text'     => $status_text,
			'is_active'       => $subscription_data['is_active'],
		];

		echo $this->generate( 'dashboard-status', $data );
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
	public function rocketcdn_field( $fields ) {
		$subscription_data = $this->api_client->get_subscription_data();

		if ( ! $subscription_data['is_active'] ) {
			return $fields;
		}

		$helper_text = __( 'Rocket CDN is currently active.', 'rocket' );
		$cdn_cnames  = $this->options->get( 'cdn_cnames', [] );

		if ( ! empty( $cdn_cnames ) && $cdn_cnames[0] !== $subscription_data['cdn_url'] ) {
			$helper_text = sprintf(
				// translators: %1$s = opening <code> tag, %2$s = CDN URL, %3$s = closing </code> tag.
				__( 'To use Rocket CDN, replace your CNAME with %1$s%2$s%3$s.', 'rocket' ),
				'<code>',
				$subscription_data['cdn_url'],
				'</code>'
			);
		}

		$more_info = sprintf(
			// translators: %1$is = opening link tag, %2$s = closing link tag.
			__( '%1$sMore Info%2$s', 'rocket' ),
			'<a href="" data-beacon-article="" rel="noopener noreferrer" target="_blank">',
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
		];

		return $fields;
	}

	/**
	 * Displays the button to open the subscription modal
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function display_manage_subscription() {
		$subscription_data = $this->api_client->get_subscription_data();

		if ( ! $subscription_data['is_active'] ) {
			return;
		}

		?>
		<p class="wpr-rocketcdn-subscription"><button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal"><?php esc_html_e( 'Manage Subscription', 'rocket' ); ?></button></p>
		<?php
	}

	/**
	 * Purges the CDN cache and store the response in a transient.
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function purge_cdn_cache() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_purge_rocketcdn' ) ) {
			wp_nonce_ays( '' );
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_die();
		}

		set_transient( 'rocketcdn_purge_cache_response', $this->api_client->purge_cache_request(), HOUR_IN_SECONDS );

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		die();
	}

	/**
	 * Adds the subscription modal on the WP Rocket settings page
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function add_subscription_modal() {
		$iframe_src = add_query_arg(
			[
				'website'  => home_url(),
				'callback' => rest_url( 'wp-rocket/v1/rocketcdn/' ),
			],
			'https://dave.wp-rocket.me/cdn/iframe'
		);
		?>
		<div class="wpr-rocketcdn-modal" id="wpr-rocketcdn-modal" aria-hidden="true">
		<div class="wpr-rocketcdn-modal__overlay" tabindex="-1" data-micromodal-close>
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
	 * Updates the RocketCDN user token value
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function update_user_token() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! isset( $_POST['action'] ) || 'save_rocketcdn_token' !== $_POST['action'] ) {
			return;
		}

		if ( empty( $_POST['value'] ) ) {
			delete_option( 'rocketcdn_user_token' );

			return;
		}

		$token = sanitize_key( $_POST['value'] );

		if ( 40 !== strlen( $token ) ) {
			return;
		}

		update_option( 'rocketcdn_user_token', $token );
	}
}
