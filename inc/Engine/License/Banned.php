<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\License;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\License\API\User;

class Banned extends Abstract_Render {
	/**
	 * User client API instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * URL to purchase a WP Rocket license.
	 *
	 * @var string
	 */
	private $purchase_url = 'https://wp-rocket.me/order/?add-to-cart=191&coupon_code=back2rocket';

	/**
	 * Constructor.
	 *
	 * @param User   $user User client API instance.
	 * @param string $template_path Path to the templates.
	 */
	public function __construct( User $user, $template_path ) {
		parent::__construct( $template_path );
		$this->user = $user;
	}

	/**
	 * Displays the banned website banner on the WP Rocket settings page.
	 *
	 * This method checks whether the banned banner should be shown, confirms the user is viewing the settings page,
	 * constructs a message identifying loss of access to features, and renders the banner template.
	 *
	 * @return void
	 */
	public function maybe_display_banned_banner(): void {
		if ( ! $this->can_display() ) {
			return;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		$message = sprintf(
			// translators: %1$s = <strong>, %2$s = </strong>, %3$s = <strong>, %4$s = </strong>.
			esc_html__( 'As your license is no longer active, you lost access to WP Rocket\'s powerful features to %1$sboost speed%2$s and deliver a %3$stop-notch user experience%4$s.', 'rocket' ),
			'<strong>',
			'</strong>',
			'<strong>',
			'</strong>'
		);

        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->generate(
			'banned-website-banner',
			[
				'purchase_url' => $this->purchase_url,
				'message'      => $message,
			]
		);
	}

	/**
	 * Displays a banned license notice on admin pages except the WP Rocket settings page.
	 *
	 * This method checks if the current user should see the banned site notice,
	 * ensures it is not the WP Rocket settings page, and then displays a dismissible
	 * error notice informing the user of the banned state.
	 *
	 * @return void
	 */
	public function maybe_display_banned_notice(): void {
		if ( ! $this->can_display() ) {
			return;
		}

		if ( 'settings_page_wprocket' === get_current_screen()->id ) {
			return;
		}

		rocket_notice_html(
			[
				'dismissible' => '',
				'status'      => 'error',
				// translators: %1$s = <strong>, %2$s = WP Rocket plugin name, %3$s = </strong>, %4$s = <a>, %5$s = </a>.
				'message'     => sprintf( __( '%1$s%2$s%3$s: Your license has been revoked and your site is no longer optimized for speed. %4$sGet WP Rocket at 20%% off%5$s', 'rocket' ), '<strong>', WP_ROCKET_PLUGIN_NAME, '</strong>', '<a href="' . $this->purchase_url . '" target="_blank" rel="noopener noreferrer">', '</a>' ),
			]
		);
	}

	/**
	 * Adds a banned license notification bubble to the WP Rocket menu item.
	 *
	 * Displays a notification bubble in the WP Rocket menu title when the website's license is banned.
	 *
	 * @param string $menu_title The current menu title.
	 * @return string Modified menu title with banned notification bubble if applicable.
	 */
	public function maybe_add_banned_bubble( $menu_title ): string {
		if ( ! $this->can_display() ) {
			return $menu_title;
		}

		return $menu_title . ' <span class="rocket-banned-bubble"></span>';
	}

	/**
	 * Determines whether the banned notice can be displayed to the current user.
	 *
	 * Checks if the user's license is not expired, the site/user is banned,
	 * and the current user has the 'rocket_manage_options' capability.
	 *
	 * @return bool True if the notice can be displayed, false otherwise.
	 */
	private function can_display(): bool {
		if ( $this->user->is_license_expired() ) {
			return false;
		}

		if ( ! $this->user->is_banned() ) {
			return false;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		return true;
	}
}
