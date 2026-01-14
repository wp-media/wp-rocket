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
	public function maybe_display_banned_banner() {
		if ( ! $this->can_display_notice() ) {
			return;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		$purchase_url = '';
		$message      = sprintf(
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
				'purchase_url' => $purchase_url,
				'message'      => $message,
			]
		);
        // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
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
	public function maybe_display_banned_notice() {
		if ( ! $this->can_display_notice() ) {
			return;
		}

		if ( 'settings_page_wprocket' === get_current_screen()->id ) {
			return;
		}

		rocket_notice_html(
			[
				'dismissible' => '',
				'status'      => 'error',
				// translators: %s is WP Rocket plugin name.
				'message'     => sprintf( __( '<strong>%s</strong>:', 'rocket' ), WP_ROCKET_PLUGIN_NAME ),
			]
		);
	}

	/**
	 * Determines whether the banned notice can be displayed to the current user.
	 *
	 * Checks if the user's license is not expired, the site/user is banned,
	 * and the current user has the 'rocket_manage_options' capability.
	 *
	 * @return bool True if the notice can be displayed, false otherwise.
	 */
	private function can_display_notice(): bool {
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
