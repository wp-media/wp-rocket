<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Plugin\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Controller as RocketInsightsController;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Event_Management\Subscriber_Interface;

class NoticeSubscriber implements Subscriber_Interface {

	const ACTIVATION_NOTICE_KEY = 'rocket_new_user_activation_notice';
	const MAJOR_RELEASE_VERSION = '3.22.0';

	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Rocket Insights controller instance.
	 *
	 * @var RocketInsightsController
	 */
	private $rocket_insights;

	/**
	 * Constructor.
	 *
	 * @param Options_Data             $options         WP Rocket options instance.
	 * @param RocketInsightsController $rocket_insights Rocket Insights controller instance.
	 */
	public function __construct( Options_Data $options, RocketInsightsController $rocket_insights ) {
		$this->options         = $options;
		$this->rocket_insights = $rocket_insights;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'admin_init'    => 'suppress_wp_activation_notice',
			'admin_notices' => 'maybe_display_post_activation_notice',
			'admin_post_rocket_insights_add_homepage_notice' => 'handle_add_homepage',
		];
	}

	/**
	 * Suppresses WordPress's native "Plugin activated" notice.
	 *
	 * @return void
	 */
	public function suppress_wp_activation_notice(): void {
		if ( ! get_transient( 'rocket_just_activated' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_GET['activate'] );
		delete_transient( 'rocket_just_activated' );
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
	 * Handles the "Start with my homepage" admin-post action.
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
		return empty( $this->options->get( 'previous_version', '' ) );
	}

	/**
	 * Returns true when the site should display the major release notice.
	 *
	 * Two conditions trigger it (no DB writes required):
	 * 1. Direct boundary crossing: previous version is older than the major release.
	 * 2. Within-series update: previous version is in the same X.Y series, meaning the
	 *    boundary was already crossed in a prior upgrade. The notice stays active until
	 *    the user explicitly dismisses it (handled by rocket_boxes in display_update_notice).
	 *
	 * @return bool
	 */
	private function is_major_release(): bool {
		$previous = $this->options->get( 'previous_version', '' );

		if ( empty( $previous ) ) {
			return false;
		}

		if ( version_compare( $previous, self::MAJOR_RELEASE_VERSION, '<' ) ) {
			return true;
		}

		return $this->extract_major( $previous ) === $this->extract_major( self::MAJOR_RELEASE_VERSION );
	}

	/**
	 * Extracts the major.minor segment (X.Y) from a version string.
	 *
	 * @param string $version Full version string (e.g. "3.21.4").
	 * @return string Major.minor string (e.g. "3.21").
	 */
	private function extract_major( string $version ): string {
		$parts = explode( '.', $version );
		return ( $parts[0] ?? '0' ) . '.' . ( $parts[1] ?? '0' );
	}

	/**
	 * Derives the major release notice dismiss key from MAJOR_RELEASE_VERSION.
	 * Updating the version constant is the only change needed per major release —
	 * the key (and therefore dismiss state) is automatically scoped to that version.
	 *
	 * @return string e.g. "rocket_major_release_notice_3_22"
	 */
	private function get_major_release_notice_key(): string {
		return 'rocket_major_release_notice_' . str_replace( '.', '_', $this->extract_major( self::MAJOR_RELEASE_VERSION ) );
	}

	/**
	 * Renders the new-user activation notice if it has not been dismissed.
	 *
	 * @return void
	 */
	private function display_activation_notice(): void {
		$boxes = (array) get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( self::ACTIVATION_NOTICE_KEY, $boxes, true ) ) {
			return;
		}

		$message = sprintf(
			// translators: %1$s = opening strong+paragraph tags, %2$s = site name, %3$s = closing strong opening paragraph, %4$s = opening link tag, %5$s = closing link+paragraph tags.
			esc_html__( '%1$s%2$s is good to go!%3$s Your website is already faster. Visit %4$sRocket Insights%5$s to check your performance, get recommendations, and keep your site fast.', 'rocket' ),
			'<p><strong>',
			esc_html( get_bloginfo( 'name' ) ),
			'</strong></p><p>',
			'<a href="' . esc_url( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '#rocket_insights' ) ) . '">',
			'</a></p>'
		);

		rocket_notice_html(
			[
				'id'                     => 'rocket-notice-' . sanitize_html_class( self::ACTIVATION_NOTICE_KEY ),
				'status'                 => 'success',
				'message'                => $message,
				'action'                 => $this->build_activation_action_html( __( 'Start with my homepage', 'rocket' ) ),
				'dismiss_button'         => self::ACTIVATION_NOTICE_KEY,
				'dismiss_button_message' => __( 'Dismiss', 'rocket' ),
			]
		);
	}

	/**
	 * Builds the activation notice primary button HTML.
	 *
	 * @param string $label Button label text.
	 * @return string
	 */
	private function build_activation_action_html( string $label ): string {
		return '<a class="button button-primary" href="' . esc_url(
			wp_nonce_url(
				admin_url( 'admin-post.php?action=rocket_insights_add_homepage_notice' ),
				'rocket_insights_add_homepage_notice'
			)
		) . '">' . esc_html( $label ) . '</a>';
	}

	/**
	 * Renders the major release notice using the existing Utils display helper.
	 * Content is currently the RocketCDN announcement and may change per release.
	 *
	 * @return void
	 */
	private function display_major_release_notice(): void {
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

		$notice_info = [
			'new_version'     => self::MAJOR_RELEASE_VERSION,
			'dismiss_button'  => $this->get_major_release_notice_key(),
			'dismiss_message' => __( 'Check it later', 'rocket' ),
			'message'         => $message,
			'action'          => $this->build_major_release_action_html(),
			'status'          => 'info',
			'track_event'     => true,
		];

		Utils::display_update_notice( $notice_info, true );
	}

	/**
	 * Builds the "Add your pages now" button HTML for the major release notice.
	 * Clicking it dismisses the notice and redirects to the CDN settings tab.
	 *
	 * @return string
	 */
	private function build_major_release_action_html(): string {
		$notice_key   = $this->get_major_release_notice_key();
		$redirect_url = admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '&rocket_source=notice_rocketcdn_upgrade#page_cdn' );
		$dismiss_url  = wp_nonce_url(
			admin_url(
				'admin-post.php?action=rocket_ignore&box=' . $notice_key
				. '&redirect=' . rawurlencode( $redirect_url )
			),
			'rocket_ignore_' . $notice_key
		);

		return '<a class="button button-primary" href="' . esc_url( $dismiss_url ) . '">'
			. esc_html__( 'Add your pages now', 'rocket' )
			. '</a>';
	}
}
