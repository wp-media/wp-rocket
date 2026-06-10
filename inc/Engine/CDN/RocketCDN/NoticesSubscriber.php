<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Tracking\TrackingTrait;

/**
 * Subscriber for the RocketCDN notices on WP Rocket settings page
 *
 * @since 3.5
 */
class NoticesSubscriber extends Abstract_Render implements Subscriber_Interface {
	use TrackingTrait;

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
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Subscription controller instance.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * Constructor
	 *
	 * @param APIClient              $api_client    RocketCDN API Client instance.
	 * @param Beacon                 $beacon        Beacon instance.
	 * @param UserClient             $user_client   UserClient instance.
	 * @param Tracking               $tracking      Tracking instance.
	 * @param string                 $template_path Path to the templates.
	 * @param Options_Data           $options WP Rocket options instance.
	 * @param SubscriptionController $subscription_controller Subscription controller instance.
	 */
	public function __construct(
		APIClient $api_client,
		Beacon $beacon,
		UserClient $user_client,
		Tracking $tracking,
		$template_path,
		Options_Data $options,
		SubscriptionController $subscription_controller
	) {
		parent::__construct( $template_path );

		$this->api_client              = $api_client;
		$this->beacon                  = $beacon;
		$this->user_client             = $user_client;
		$this->tracking                = $tracking;
			$this->options             = $options;
		$this->subscription_controller = $subscription_controller;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                           => [
				[ 'purge_cache_notice' ],
				[ 'change_cname_notice' ],
				[ 'activation_failed_notice' ],
				[ 'maybe_display_rocketcdn_notice' ],
			],
			'rocket_cdn_free_before_status_indicator' => 'display_rocketcdn_cta',
			'admin_post_rocket_ignore'                => [
				[ 'track_notice_homepage_cta_click', 5 ],
				[ 'track_rocketcdn_notice_dismissed', 5 ],
			],
			'wp_ajax_rocket_ignore'                   => [ [ 'track_rocketcdn_notice_dismissed', 5 ] ],
		];
	}

	/**
	 * Displays the RocketCDN Call to Action on the CDN tab of WP Rocket settings page
	 *
	 * @since 3.5
	 *
	 * @param array $cta_data CTA data.
	 *
	 * @return void
	 */
	public function display_rocketcdn_cta( array $cta_data ) {
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

		if ( $this->subscription_controller->has_active_subscription() && $this->subscription_controller->is_paid() ) {
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

			global $wp_locale;
			$current_price_array = explode( $wp_locale->number_format['decimal_point'], $current_price_monthly );

			if ( $cta_data['limit_reached'] ) {
				$cta_big_class .= 'wpr-rocketcdn-cta---max-limit';
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
				'regular_price'         => $regular_price_monthly,
				'current_price'         => $current_price_monthly,
				'current_price_array'   => [
					'major' => ! empty( $current_price_array[0] ) ? $current_price_array[0] : 0,
					'minor' => ! empty( $current_price_array[1] ) ? $current_price_array[1] : 0,
				],
			];
		}

		$big_cta_data = array_merge( $big_cta_data, $cta_data );

		echo $this->generate( 'cta-big', $big_cta_data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
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

		if ( $this->subscription_controller->is_free() ) {
			// Send the request again (create subscription) to fix what is wrong with this account.
			$this->subscription_controller->create_subscription( true );
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
		if ( $this->subscription_controller->is_subscription_creation_loading() ) {
			return false;
		}

		// Do not show the notice if there is no RocketCDN user token saved:
		// this usually means the user never went through the checkout/activation flow.
		if ( empty( get_option( 'rocketcdn_user_token', '' ) ) ) {
			return false;
		}

		// Show notice when webiste is not attached.
		return ! $this->subscription_controller->is_website_attached() && $this->subscription_controller->has_active_subscription();
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

	/**
	 * Display RocketCDN notice on admin dashboard if flag is set and notice hasn't been dismissed
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function maybe_display_rocketcdn_notice() {
		/**
		 * Filters showing rocketcdn admin notices
		 *
		 * @since 3.22
		 *
		 * @param bool $show_rocketcdn_notices Show rocketcdn notices, by default it's shown.
		 */
		if ( wpm_apply_filters_typed( 'boolean', 'rocket_hide_rocketcdn_notices', false ) ) {
			return;
		}

		$previous_version = $this->options->get( 'previous_version' );
		// @phpstan-ignore-next-line
		$rocket_cdn_token = get_option( 'rocketcdn_user_token', '' );

		// Don't show the notice if RocketCDN is already active (token exists).
		if ( ! empty( $rocket_cdn_token ) ) {
			return;
		}

		// Fresh install, show new install notice.
		if ( empty( $previous_version ) ) {
			$message = sprintf(
			// translators: %1$s opening <strong> tag, %2$s closing </strong> tag.
				esc_html__(
					'%1$sNew in WP Rocket: Faster loading for your key pages%2$s',
					'rocket'
				),
				'<p><strong>',
				'</strong></p>'
			);

			$message .= sprintf(
			// translators: %1$s opening <p> tag, %2$s closing </p> tag.
				esc_html__(
					'%1$sYou can now use Content Delivery, powered by RocketCDN, to speed up your homepage and 2 more pages, at no extra cost.%2$s',
					'rocket'
				),
				'<p>',
				'</p>'
			);

			$notice_info = [
				'new_version'     => '3.22.0',
				'dismiss_button'  => 'rocketcdn_install_notice',
				'dismiss_message' => __( 'Dismiss', 'rocket' ),
				'message'         => $message,
				'action'          => 'rocketcdn_install_page',
				'status'          => 'success',
				'track_event'     => true,
			];

			Utils::display_update_notice( $notice_info, true );

			return;
		}

		$message = sprintf(
		// translators: %1$s opening <strong> tag, %2$s closing </strong> tag.
			esc_html__(
				'%1$sUse RocketCDN for free to boost up to 3 pages 🚀%2$s',
				'rocket'
			),
			'<p><strong>',
			'</strong></p>'
		);

		$message .= sprintf(
		// translators: %1$s opening <p> tag, %2$s closing </p> tag.
			esc_html__(
				'%1$sAs a WP Rocket user, you can now activate RocketCDN for free on up to 3 pages. Choose your top pages and speed up their performance worldwide!%2$s',
				'rocket'
			),
			'<p>',
			'</p>'
		);

		$notice_info = [
			'new_version'      => '3.22.0',
			'dismiss_button'   => 'rocket_update_notice',
			'dismiss_message'  => __( 'Check it later', 'rocket' ),
			'message'          => $message,
			'action'           => 'rocketcdn_upgrade_page',
			'previous_version' => $previous_version,
			'track_event'      => true,
		];

		Utils::display_update_notice( $notice_info, true );
	}

	/**
	 * Tracks the "Start with my homepage" CTA click from the RocketCDN promo admin notice.
	 *
	 * Fires before rocket_dismiss_boxes() redirects, so we can track before the exit.
	 *
	 * @return void
	 */
	public function track_notice_homepage_cta_click(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified later by rocket_dismiss_boxes().
		$box = isset( $_GET['box'] ) ? sanitize_key( wp_unslash( $_GET['box'] ) ) : '';

		if ( 'rocketcdn_install_notice' !== $box ) {
			return;
		}

		/**
		 * Fires when the homepage cta button is clicked.
		 *
		 * @param string $source The source of the click, either 'add_homepage_button' or 'admin_notices'.
		 */
		do_action( 'rocket_rocketcdn_add_homepage', 'admin_notices' );
	}

	/**
	 * Tracks when a RocketCDN admin notice is dismissed.
	 *
	 * Fires before rocket_dismiss_boxes() redirects, so we can track before the exit.
	 *
	 * @return void
	 */
	public function track_rocketcdn_notice_dismissed(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified later by rocket_dismiss_boxes().
		$box = isset( $_GET['box'] ) ? sanitize_key( wp_unslash( $_GET['box'] ) ) : '';

		$rocketcdn_boxes = [ 'rocketcdn_install_notice', 'rocket_update_notice' ];

		if ( ! in_array( $box, $rocketcdn_boxes, true ) ) {
			return;
		}

		// Track Mixpanel event immediately.
		$this->track_event( 'RocketCDN Admin Notice Dismissed' );
	}
}
