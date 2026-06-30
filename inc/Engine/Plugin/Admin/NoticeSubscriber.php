<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Plugin\Admin;

use WP_Rocket\Engine\Admin\RocketInsights\Controller as RocketInsightsController;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Event_Management\Subscriber_Interface;

class NoticeSubscriber implements Subscriber_Interface {

	const ACTIVATION_NOTICE_KEY = 'rocket_new_user_activation_notice';

	/**
	 * Rocket Insights controller instance.
	 *
	 * @var RocketInsightsController
	 */
	private $rocket_insights;

	/**
	 * Constructor.
	 *
	 * @param RocketInsightsController $rocket_insights Rocket Insights controller instance.
	 */
	public function __construct( RocketInsightsController $rocket_insights ) {
		$this->rocket_insights = $rocket_insights;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'admin_notices' => 'maybe_display_post_activation_notice',
			'admin_post_rocket_insights_add_homepage_notice' => 'handle_add_homepage',
		];
	}

	/**
	 * Display wp-rocket notices after activation or major release. Only one notice is displayed per request.
	 *
	 * @return void
	 */
	public function maybe_display_post_activation_notice(): void {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		// Show this notice to new users after activation.
		if ( $this->is_new_user() ) {
			$this->display_activation_notice();

			return;
		}

		// Show this notice to existing users after a major release.
		if ( $this->is_major_release() ) {
			$this->display_major_release_notice();
		}
	}

	/**
	 * Adds the homepage to Rocket Insights and dismisses the activation notice.
	 *
	 * @return void
	 */
	public function handle_add_homepage(): void {
		if (
			! isset( $_GET['_wpnonce'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'rocket_insights_add_homepage_notice' )
		) {
			wp_nonce_ays( 'rocket_insights_add_homepage_notice' );
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'rocket' ) );
		}

		$this->rocket_insights->add_homepage( 'activation_notice' );

		$user_id = get_current_user_id();
		$boxes   = (array) get_user_meta( $user_id, 'rocket_boxes', true );

		if ( ! in_array( self::ACTIVATION_NOTICE_KEY, $boxes, true ) ) {
			$boxes[] = self::ACTIVATION_NOTICE_KEY;
			update_user_meta( $user_id, 'rocket_boxes', $boxes );
		}

		wp_safe_redirect( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '#rocket_insights' ) );
		exit;
	}

	/**
	 * Returns true when no previous version is recorded, indicating a fresh install.
	 *
	 * @return bool
	 */
	private function is_new_user(): bool {
		return empty( $this->get_previous_version() );
	}

	/**
	 * Returns true when the site should display the major release notice.
	 *
	 * @return bool
	 */
	private function is_major_release(): bool {
		$previous = $this->get_previous_version();

		if ( empty( $previous ) ) {
			return false;
		}

		$boxes = (array) get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if (
			in_array( 'rocket_update_notice', $boxes, true ) ||
			in_array( 'rocketcdn_install_notice', $boxes, true )
		) {
			return false;
		}

		return version_compare(
			$this->extract_major( $previous ),
			$this->extract_major( WP_ROCKET_VERSION ),
			'<='
		);
	}

	/**
	 * Returns the previous_version value read directly from the options table.
	 *
	 * Options_Data is returning a stale empty string on the first page load until page refresh.
	 *
	 * @return string
	 */
	private function get_previous_version(): string {
		$settings = get_option( WP_ROCKET_SLUG, [] );

		return (string) ( $settings['previous_version'] ?? '' );
	}

	/**
	 * Extracts the major.minor segment (X.Y) from a version string.
	 *
	 * @param string $version Full version string (e.g. "3.21.4").
	 * @return string Major.minor string (e.g. "3.21").
	 */
	private function extract_major( string $version ): string {
		$parts = explode( '.', $version );

		return $parts[0] . '.' . ( $parts[1] ?? '0' );
	}

	/**
	 * Create a unique key for the release based on the version.
	 *
	 * @return string
	 */
	private function get_major_release_notice_key(): string {
		return 'rocket_major_release_notice_' . str_replace( '.', '_', $this->extract_major( WP_ROCKET_VERSION ) );
	}

	/**
	 * Renders the new-user activation notice if it has not been dismissed.
	 *
	 * @return void
	 */
	private function display_activation_notice(): void {
		$message = sprintf(
			/* translators: %1$s = <strong>, %2$s = plugin name, %3$s = </strong> */
			__( '%1$s %2$s is good to go! %3$s Your website is already faster.', 'rocket' ),
			'<strong>',
			WP_ROCKET_PLUGIN_NAME,
			'</strong>'
			);

		// This filter is documented in inc/Engine/Admin/RocketInsights/Context/Context.php.
		$rocket_insights_enabled = wpm_apply_filters_typed( 'boolean', 'rocket_rocket_insights_enabled', true );

		if ( $rocket_insights_enabled ) {
			$message = sprintf(
				// translators: %1$s = opening strong tags, %2$s = plugin name, %3$s = closing strong, %4$s = opening link tag, %5$s = closing link.
				esc_html__( '%1$s%2$s is good to go!%3$s Your website is already faster. Visit %4$sRocket Insights%5$s to check your performance, get recommendations, and keep your site fast.', 'rocket' ),
				'<strong>',
				WP_ROCKET_PLUGIN_NAME,
				'</strong>',
				'<a href="' . esc_url( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '#rocket_insights' ) ) . '">',
				'</a>'
			);
		}

		$action_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=rocket_insights_add_homepage_notice' ),
				'rocket_insights_add_homepage_notice'
			);

		$notice_info = [
			'dismiss_button'  => self::ACTIVATION_NOTICE_KEY,
			'dismiss_message' => __( 'Dismiss', 'rocket' ),
			'message'         => $message,
			'action'          => $this->build_action_html( __( 'Start with my homepage', 'rocket' ), $action_url ),
			'status'          => 'success',
		];

		Utils::display_update_notice( $notice_info, true );
	}

	/**
	 * Builds a primary action button HTML for a notice.
	 *
	 * @param string $label Button label text.
	 * @param string $url   Button URL.
	 * @return string
	 */
	private function build_action_html( string $label, string $url ): string {
		return '<a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
	}

	/**
	 * Renders the major release notice.
	 *
	 * @return void
	 */
	private function display_major_release_notice(): void {
		$rocket_cdn_token = get_option( 'rocketcdn_user_token', '' );

		if ( ! empty( $rocket_cdn_token ) ) {
			return;
		}

		$message = sprintf(
			// translators: %1$s = opening strong+paragraph tags, %2$s = closing strong+paragraph tags.
			esc_html__(
				'%1$sUse RocketCDN for free to boost up to 3 pages 🚀%2$s',
				'rocket'
			),
			'<p><strong>',
			'</strong></p>'
		);

		$message .= sprintf(
			// translators: %1$s = opening paragraph tag, %2$s = closing paragraph tag.
			esc_html__(
				'%1$sAs a WP Rocket user, you can now activate RocketCDN for free on up to 3 pages. Choose your top pages and speed up their performance worldwide!%2$s',
				'rocket'
			),
			'<p>',
			'</p>'
		);

		$notice_key   = $this->get_major_release_notice_key();
		$redirect_url = admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '&rocket_source=notice_rocketcdn_upgrade#page_cdn' );
		$action_url   = wp_nonce_url(
			admin_url(
				'admin-post.php?action=rocket_ignore&box=' . $notice_key
				. '&redirect=' . rawurlencode( $redirect_url )
			),
			'rocket_ignore_' . $notice_key
		);

		$notice_info = [
			'new_version'     => WP_ROCKET_VERSION,
			'dismiss_button'  => $notice_key,
			'dismiss_message' => __( 'Check it later', 'rocket' ),
			'message'         => $message,
			'action'          => $this->build_action_html( __( 'Add your pages now', 'rocket' ), $action_url ),
			'status'          => 'info',
			'track_event'     => true,
		];

		Utils::display_update_notice( $notice_info, true );
	}
}
