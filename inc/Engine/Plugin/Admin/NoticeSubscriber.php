<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Plugin\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class NoticeSubscriber implements Subscriber_Interface {

	const ACTIVATION_NOTICE_KEY = 'rocket_new_user_activation_notice';

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
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'rocket' ) );
		}

		/**
		 * Fires when the add homepage button is clicked.
		 */
		do_action( 'rocket_insights_add_homepage_notice' );

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
	 * Fires the activation notice action for domain-specific subscribers to handle.
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

		/**
		 * Fires to display activation notice.
		 *
		 * @param string $message Default activation notce.
		 * @param string $dismiss_key Dismiss key.
		 */
		do_action( 'rocket_display_activation_notice', $message, self::ACTIVATION_NOTICE_KEY );
	}

	/**
	 * Display major release notice.
	 *
	 * @return void
	 */
	private function display_major_release_notice(): void {
		/**
		 * Fires to display major release notice.
		 *
		 * @param string $version The current major version.
		 */
		do_action( 'rocket_display_major_release_notice', $this->extract_major( WP_ROCKET_VERSION ) );
	}
}
