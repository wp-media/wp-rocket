<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the RocketCDN notices on WP Rocket settings page
 *
 * @since 3.5
 */
class NoticesSubscriber extends Abstract_Render implements Subscriber_Interface {
	/**
	 * RocketCDN API Client instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Beacon instance
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * UserClient instance
	 *
	 * @var UserClient
	 */
	private $user_client;

	/**
	 * Tracking instance
	 *
	 * @var Tracking
	 */
	private $tracking;

	/**
	 * Constructor
	 *
	 * @param APIClient  $api_client    RocketCDN API Client instance.
	 * @param Beacon     $beacon        Beacon instance.
	 * @param UserClient $user_client   UserClient instance.
	 * @param Tracking   $tracking      Tracking instance.
	 * @param string     $template_path Path to the templates.
	 */
	public function __construct( APIClient $api_client, Beacon $beacon, UserClient $user_client, Tracking $tracking, $template_path ) {
		parent::__construct( $template_path );

		$this->api_client  = $api_client;
		$this->beacon      = $beacon;
		$this->user_client = $user_client;
		$this->tracking    = $tracking;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                    => [
				[ 'promote_rocketcdn_notice' ],
				[ 'purge_cache_notice' ],
				[ 'change_cname_notice' ],
				[ 'activation_failed_notice' ],
			],
			'rocket_before_cdn_sections'       => 'display_rocketcdn_cta',
			'wp_ajax_toggle_rocketcdn_cta'     => 'toggle_cta',
			'wp_ajax_rocketcdn_dismiss_notice' => 'dismiss_notice',
			'admin_footer'                     => 'add_dismiss_script',
		];
	}

	/**
	 * Adds notice to promote RocketCDN on settings page
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function promote_rocketcdn_notice() {
		/**
		 * Filters RocketCDN promotion notice.
		 *
		 * @param bool $promotion_notice; true to display, false otherwise.
		 */
		if ( ! apply_filters( 'rocket_promote_rocketcdn_notice', true ) ) {
			return;
		}

		if ( $this->is_white_label_account() ) {
			return;
		}

		if ( ! rocket_is_live_site() ) {
			return;
		}

		if ( ! $this->should_display_notice() ) {
			return;
		}

		echo $this->generate( 'promote-notice' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Adds inline script to permanently dismissing the RocketCDN promotion notice
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function add_dismiss_script() {
		if ( $this->is_white_label_account() ) {
			return;
		}

		if ( ! rocket_is_live_site() ) {
			return;
		}

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
					postData += '&nonce=<?php echo esc_attr( $nonce ); ?>';
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

		$subscription_data = $this->api_client->get_subscription_data();

		return 'running' !== $subscription_data['subscription_status'];
	}

	/**
	 * Ajax callback to save the dismiss as a user meta
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'rocketcdn_dismiss_notice', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_send_json_error( 'no permissions' );
		}

		update_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', true );

		wp_send_json_success();
	}

	/**
	 * Displays the RocketCDN Call to Action on the CDN tab of WP Rocket settings page
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function display_rocketcdn_cta() {
		/**
		 * Filters the display of the RocketCDN cta banner.
		 *
		 * @param bool $display_cta_banner; true to display, false otherwise.
		 */
		if ( ! apply_filters( 'rocket_display_rocketcdn_cta', true ) ) {
			return;
		}

		if ( $this->is_white_label_account() ) {
			return;
		}

		if ( ! rocket_is_live_site() ) {
			return;
		}

		$subscription_data = $this->api_client->get_subscription_data();

		if ( 'running' === $subscription_data['subscription_status'] ) {
			return;
		}

		$pricing = $this->api_client->get_pricing_data();

		$regular_price_monthly = '';
		$regular_price_annual  = '';
		$nopromo_variant       = '--no-promo';
		$cta_small_class       = 'wpr-isHidden';
		$cta_big_class         = '';

		if ( get_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden', true ) ) {
			$cta_small_class = '';
			$cta_big_class   = 'wpr-isHidden';
		}

		$small_cta_data = [
			'container_class' => $cta_small_class,
		];

		// Get button URL for one-click checkout.
		$button_url = $this->get_express_checkout_url();

		if ( is_wp_error( $pricing ) ) {
			$beacon    = $this->beacon->get_suggest( 'rocketcdn_error' );
			$more_info = sprintf(
				// translators: %1$is = opening link tag, %2$s = closing link tag.
				__( '%1$sMore Info%2$s', 'rocket' ),
				'<a href="' . esc_url( $beacon['url'] ) . '" data-beacon-article="' . esc_attr( $beacon['id'] ) . '" rel="noopener noreferrer" target="_blank">',
				'</a>'
			);

			$message = $pricing->get_error_message() . ' ' . $more_info;

			$big_cta_data = [
				'container_class' => $cta_big_class,
				'nopromo_variant' => $nopromo_variant,
				'error'           => true,
				'message'         => $message,
				'button_url'      => $button_url,
			];
		} else {
			$current_price_monthly = number_format_i18n( $pricing['monthly_price'], 2 );
			$current_price_annual  = number_format_i18n( $pricing['annual_price'] / 12, 2 );
			$promotion_campaign    = '';
			$end_date              = strtotime( $pricing['end_date'] );
			$promotion_end_date    = '';

			if (
				$pricing['is_discount_active']
				&&
				$end_date > time()
			) {
				$promotion_campaign    = $pricing['discount_campaign_name'];
				$regular_price_monthly = $current_price_monthly;
				$regular_price_annual  = $current_price_annual;
				$current_price_monthly = number_format_i18n( $pricing['discounted_price_monthly'], 2 ) . '*';
				$current_price_annual  = number_format_i18n( $pricing['discounted_price_yearly'] / 12, 2 ) . '*';
				$nopromo_variant       = '';
				$promotion_end_date    = date_i18n( get_option( 'date_format' ), $end_date );
			}

			$big_cta_data = [
				'container_class'       => $cta_big_class,
				'promotion_campaign'    => $promotion_campaign,
				'promotion_end_date'    => $promotion_end_date,
				'nopromo_variant'       => $nopromo_variant,
				'regular_price_monthly' => $regular_price_monthly,
				'regular_price_annual'  => $regular_price_annual,
				'current_price_monthly' => $current_price_monthly,
				'current_price_annual'  => $current_price_annual,
				'button_url'            => $button_url,
			];
		}

		echo $this->generate( 'cta-small', $small_cta_data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
		echo $this->generate( 'cta-big', $big_cta_data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Toggles display of the RocketCDN CTAs on the settings page
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function toggle_cta() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_send_json_error( 'no permissions' );
		}

		if ( ! isset( $_POST['status'] ) ) {
			wp_send_json_error( 'missing status' );
		}

		if ( 'big' === $_POST['status'] ) {
			delete_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden' );
		} elseif ( 'small' === $_POST['status'] ) {
			update_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden', true );
		}

		wp_send_json_success();
	}

	/**
	 * Displays a notice after purging the RocketCDN cache.
	 *
	 * @since 3.5
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

		$message = $purge_response['message'];

		if ( 'error' === $purge_response['status'] ) {
			$beacon    = $this->beacon->get_suggest( 'rocketcdn_error' );
			$more_info = sprintf(
				// translators: %1$is = opening link tag, %2$s = closing link tag.
				__( '%1$sMore Info%2$s', 'rocket' ),
				'<a href="' . esc_url( $beacon['url'] ) . '" data-beacon-article="' . esc_attr( $beacon['id'] ) . '" rel="noopener noreferrer" target="_blank">',
				'</a>'
			);

			$message .= ' ' . $more_info;
		}

		delete_transient( 'rocketcdn_purge_cache_response' );

		rocket_notice_html(
			[
				'status'  => $purge_response['status'],
				'message' => $message,
			]
		);
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

	/**
	 * Change CName admin notice contents.
	 *
	 * @return void
	 */
	public function change_cname_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( 'rocketcdn_change_cname', (array) $boxes, true ) ) {
			return;
		}

		$old_cname = get_option( 'wp_rocket_rocketcdn_old_url' );
		if ( empty( $old_cname ) ) {
			return;
		}

		$new_subscription = $this->api_client->get_subscription_data();
		if ( empty( $new_subscription['cdn_url'] ) || $old_cname === $new_subscription['cdn_url'] ) {
			return;
		}

		$support_url = rocket_get_external_url(
			'support',
			[
				'utm_source' => 'wp_plugin',
				'utm_medium' => 'wp_rocket',
			]
		);

		$message_lines = [
			// translators: %1$s = Old CName, %2$s = New CName.
			sprintf( esc_html__( 'We\'ve updated your RocketCDN CNAME from %1$s to %2$s.', 'rocket' ), $old_cname, $new_subscription['cdn_url'] ),
			// translators: %1$s = New CName.
			sprintf( esc_html__( 'The change is already applied to the plugin settings. If you were using the CNAME in your code, make sure to update it to: %1$s.', 'rocket' ), $new_subscription['cdn_url'] ),
		];

		rocket_notice_html(
			[
				'status'         => 'info',
				'message'        => implode( '<br>', $message_lines ),
				'dismiss_button' => 'rocketcdn_change_cname',
				'id'             => 'rocketcdn_change_cname_notice',
				'action'         => sprintf( '<a href="%1$s" target="_blank" rel="noopener" class="wpr-button" id="rocketcdn-change-cname-button">%2$s</a>', $support_url, esc_html__( 'contact support', 'rocket' ) ),
			]
		);
	}

	/**
	 * Displays an admin notice when RocketCDN activation failed.
	 *
	 * Shows a notice with express checkout URL when:
	 * - is_active is false
	 * - cdn_url is empty
	 *
	 * @return void
	 */
	public function activation_failed_notice(): void {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		if ( $this->is_white_label_account() ) {
			return;
		}

		if ( ! $this->should_display_activation_failed_notice() ) {
			return;
		}

		$express_checkout_url = $this->get_express_checkout_url();

		if ( empty( $express_checkout_url ) ) {
			return;
		}

		// Track banner view.
		$this->tracking->track_rocketcdn_activation_failed_banner_viewed();

		$message = sprintf(
			'<strong>%1$s</strong><br><br>%2$s<br>%3$s',
			esc_html__( 'RocketCDN activation incomplete', 'rocket' ),
			esc_html__( 'RocketCDN isn’t active on this website yet.', 'rocket' ),
			esc_html__( 'Click below to complete the activation. You’ll be redirected to checkout to confirm your subscription.', 'rocket' )
		);

		rocket_notice_html(
			[
				'status'      => 'error',
				'message'     => $message,
				'dismissible' => false,
				'id'          => 'rocketcdn_activation_failed_notice',
				'action'      => sprintf(
					'<a href="%1$s" target="_blank" rel="noopener" class="wpr-button" id="wpr-rocketcdn-activation-cta">%2$s</a>',
					esc_url( $express_checkout_url ),
					esc_html__( 'Complete activation', 'rocket' )
				),
			]
		);
	}

	/**
	 * Checks if the activation failed notice should be displayed.
	 *
	 * @return bool True if notice should be displayed, false otherwise.
	 */
	private function should_display_activation_failed_notice(): bool {
		// Do not show the notice if there is no RocketCDN user token saved:
		// this usually means the user never went through the checkout/activation flow.
		if ( empty( get_option( 'rocketcdn_user_token', '' ) ) ) {
			return false;
		}

		$subscription_data = $this->api_client->get_subscription_data();

		// Show notice when is_active is false AND cdn_url is empty.
		return ! $subscription_data['is_active'] && empty( $subscription_data['cdn_url'] );
	}

	/**
	 * Gets the express checkout URL for RocketCDN.
	 *
	 * @return string Express checkout URL or empty string if not available.
	 */
	private function get_express_checkout_url(): string {
		$user_data = $this->user_client->get_user_data();

		if ( false === $user_data || ! isset( $user_data->rocketcdn->button->url ) || empty( $user_data->rocketcdn->button->url ) ) {
			return '';
		}

		return add_query_arg(
			[
				'dashboard_url' => rawurlencode(
					add_query_arg(
						[
							'page'               => WP_ROCKET_PLUGIN_SLUG,
							'rocketcdn_checkout' => 'true',
						],
						admin_url( 'options-general.php' )
					)
				),
			],
			esc_url_raw( $user_data->rocketcdn->button->url )
		);
	}
}
