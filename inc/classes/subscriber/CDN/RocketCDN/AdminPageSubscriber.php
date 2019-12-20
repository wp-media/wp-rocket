<?php
namespace WP_Rocket\Subscriber\CDN\RocketCDN;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Settings\Beacon;

/**
 * Subscriber for the RocketCDN integration in WP Rocket settings page
 *
 * @since 3.5
 * @author Remy Perona
 */
class AdminPageSubscriber extends Abstract_Render implements Subscriber_Interface {
	const ROCKETCDN_API = 'https://rocketcdn.me/api/';

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
	 * @param Options_Data $options WP Rocket options instance.
	 * @param Beacon       $beacon Beacon instance.
	 * @param string       $template_path Path to the templates.
	 */
	public function __construct( Options_Data $options, Beacon $beacon, $template_path ) {
		parent::__construct( $template_path );

		$this->options = $options;
		$this->beacon  = $beacon;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                       => [
				[ 'promote_rocketcdn_notice' ],
				[ 'purge_cache_notice' ],
			],
			'rocket_dashboard_after_account_data' => 'display_rocketcdn_status',
			'rocket_before_cdn_sections'          => 'display_rocketcdn_cta',
			'rocket_after_cdn_sections'           => 'display_manage_subscription',
			'rocket_cdn_settings_fields'          => 'rocketcdn_field',
			'wp_ajax_toggle_rocketcdn_cta'        => 'toggle_cta',
			'wp_ajax_rocketcdn_dismiss_notice'    => 'dismiss_notice',
			'admin_footer'                        => 'add_dismiss_script',
			'admin_post_rocket_purge_rocketcdn'   => 'purge_cdn_cache',
			'rocket_settings_page_footer'         => 'add_subscription_modal',
		];
	}

	/**
	 * Adds notice to promote Rocket CDN on settings page
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function promote_rocketcdn_notice() {
		if ( ! $this->should_display_notice() ) {
			return;
		}

		echo $this->generate( 'promote-notice' );
	}

	/**
	 * Adds inline script to permanently dismissing the RocketCDN promotion notice
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function add_dismiss_script() {
		if ( ! $this->should_display_notice() ) {
			return;
		}

		$nonce = wp_create_nonce( 'rocketcdn_dismiss_notice' );
		?>
		<script>
		window.addEventListener( 'load', function() {
			var dismissBtn  = document.querySelectorAll( '#rocketcdn-promote-notice .notice-dismiss, #rocketcdn-promote-notice #rocketcdn-learn-more-dismiss' );

			dismissBtn.forEach(function(element) {
				element.addEventListener( 'click', function( event ) {
					var httpRequest = new XMLHttpRequest(),
						postData    = '';

					postData += 'action=rocketcdn_dismiss_notice';
					postData += '&nonce=<?php echo esc_html( $nonce ); ?>';
					httpRequest.open( 'POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>' );
					httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
					httpRequest.send( postData );
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Checks if the promotion notice should be displayed
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	private function should_display_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return false;
		}

		if ( get_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', true ) ) {
			return false;
		}

		$subscription_data = $this->get_subscription_data();

		if ( $subscription_data['is_active'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Ajax callback to save the dismiss as a user meta
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'rocketcdn_dismiss_notice', 'nonce', true );

		if ( ! isset( $_POST['action'] ) || 'rocketcdn_dismiss_notice' !== $_POST['action'] ) {
			return;
		}

		update_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', true );
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
		$subscription_data = $this->get_subscription_data();
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
		$subscription_data = $this->get_subscription_data();

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
	 * Displays the Rocket CDN Call to Action on the CDN tab of WP Rocket settings page
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function display_rocketcdn_cta() {
		$subscription_data = $this->get_subscription_data();

		if ( $subscription_data['is_active'] ) {
			return;
		}

		$pricing            = $this->get_pricing_data();
		$current_price      = number_format_i18n( $pricing['monthly_price'], 2 );
		$regular_price      = '';
		$promotion_campaign = $pricing['discount_campaign_name'];
		$promotion_end_date = date_i18n( get_option( 'date_format' ), strtotime( $pricing['end_date'] ) );
		$nopromo_variant    = '--no-promo';
		$cta_small_class    = 'wpr-isHidden';
		$cta_big_class      = '';

		if ( get_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden', true ) ) {
			$cta_small_class = '';
			$cta_big_class   = 'wpr-isHidden';
		}

		if ( $pricing['is_discount_active'] ) {
			$regular_price   = $current_price;
			$current_price   = number_format_i18n( $pricing['discounted_price_monthly'], 2 ) . '*';
			$nopromo_variant = '';
		}

		$small_cta_data = [
			'container_class' => $cta_small_class,
		];

		$big_cta_data = [
			'container_class'    => $cta_big_class,
			'promotion_campaign' => $promotion_campaign,
			'promotion_end_date' => $promotion_end_date,
			'nopromo_variant'    => $nopromo_variant,
			'regular_price'      => $regular_price,
			'current_price'      => $current_price,
		];

		echo $this->generate( 'cta-small', $small_cta_data );
		echo $this->generate( 'cta-big', $big_cta_data );
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
		$subscription_data = $this->get_subscription_data();

		if ( ! $subscription_data['is_active'] ) {
			return;
		}

		?>
		<p class="wpr-rocketcdn-subscription"><button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal"><?php esc_html_e( 'Manage Subscription', 'rocket' ); ?></button></p>
		<?php
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
		$status = get_transient( 'rocketcdn_status' );

		if ( false !== $status ) {
			return $status;
		}

		$default = [
			'id'                            => 0,
			'is_active'                     => false,
			'cdn_url'                       => '',
			'subscription_next_date_update' => 0,
			'subscription_status'           => 'cancelled',
		];

		$response = wp_remote_get(
			self::ROCKETCDN_API . 'website/?url=' . home_url()
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			set_transient( 'rocketcdn_status', $default, WEEK_IN_SECONDS );

			return $default;
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			set_transient( 'rocketcdn_status', $default, WEEK_IN_SECONDS );

			return $default;
		}

		$data = json_decode( $data );
		$data = array_intersect_key( $data, $default );

		set_transient( 'rocketcdn_status', $data, WEEK_IN_SECONDS );

		return $data;
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
		$pricing = get_transient( 'rocketcdn_pricing' );

		if ( false !== $pricing ) {
			return $pricing;
		}

		$transient_duration = 6 * HOUR_IN_SECONDS;
		$default            = [
			'monthly_price'            => 7.99,
			'is_discount_active'       => false,
			'discounted_price_monthly' => 5.99,
			'discount_campaign_name'   => '',
			'end_date'                 => 0,
		];

		$response = wp_remote_get(
			self::ROCKETCDN_API . 'pricing'
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			set_transient( 'rocketcdn_pricing', $default, $transient_duration );

			return $default;
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			set_transient( 'rocketcdn_pricing', $default, $transient_duration );

			return $default;
		}

		$data = json_decode( $data, true );
		$data = array_intersect_key( $data, $default );

		set_transient( 'rocketcdn_pricing', $data, $transient_duration );

		return $data;
	}

	/**
	 * Toggles display of the Rocket CDN CTAs on the settings page
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function toggle_cta() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! isset( $_POST['action'], $_POST['status'] ) || 'toggle_rocketcdn_cta' !== $_POST['action'] ) {
			return;
		}

		if ( 'big' === $_POST['status'] ) {
			delete_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden' );
		} elseif ( 'small' === $_POST['status'] ) {
			update_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden', true );
		}
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

		set_transient( 'rocketcdn_purge_cache_response', $this->purge_cache_request(), HOUR_IN_SECONDS );

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		die();
	}

	/**
	 * Sends a request to purge the CDN cache
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function purge_cache_request() {
		$subscription = $this->get_subscription_data();
		$status       = 'error';

		if ( ! isset( $subscription['id'] ) || 0 === $subscription['id'] ) {
			return [
				'status'  => $status,
				'message' => __( 'RocketCDN cache purge failed: Missing identifier parameter.', 'rocket' ),
			];
		}

		$response = wp_remote_request(
			self::ROCKETCDN_API . 'website/' . $subscription['id'] . '/purge/',
			[
				'method' => 'DELETE',
			]
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return [
				'status'  => $status,
				'message' => __( 'RocketCDN cache purge failed: The API returned an unexpected response code.', 'rocket' ),
			];
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			return [
				'status'  => $status,
				'message' => __( 'RocketCDN cache purge failed: The API returned an empty response.', 'rocket' ),
			];
		}

		$data = json_decode( $data );

		if ( ! isset( $data->success ) ) {
			return [
				'status'  => $status,
				'message' => __( 'RocketCDN cache purge failed: The API returned an unexpected response.', 'rocket' ),
			];
		}

		if ( ! $data->success ) {
			return [
				'status'  => $status,
				'message' => sprintf(
					// translators: %s = message returned by the API.
					__( 'RocketCDN cache purge failed: %s.', 'rocket' ),
					$data->message
				),
			];
		}

		$status = 'success';

		return [
			'status'  => $status,
			'message' => __( 'RocketCDN cache purge successful.', 'rocket' ),
		];
	}

	/**
	 * Displays a notice after purging the RocketCDN cache.
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function purge_cache_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		$purge_response = get_transient( 'rocketcdn_purge_cache_response' );

		if ( false === $purge_response ) {
			return;
		}

		\rocket_notice_html(
			[
				'status'  => $purge_response['status'],
				'message' => $purge_response['message'],
			]
		);
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
					<iframe src="<?php echo esc_url( $iframe_src ); ?>" width="600" height="425"></iframe>
				</div>
			</div>
		</div>
		</div>
		<?php
	}
}
