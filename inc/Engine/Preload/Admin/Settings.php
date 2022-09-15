<?php

namespace WP_Rocket\Engine\Preload\Admin;

use WP_Rocket\Admin\Options_Data;

class Settings {

	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options Instance of options handler.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Maybe display the preload notice.
	 *
	 * @return void
	 */
	public function maybe_display_preload_notice() {
		if ( ! $this->can_display_notice() ) {
			return;
		}

		if ( false === get_transient( 'wpr_preload_running' ) ) {
			return;
		}

		$message = sprintf(
			// translators: %1$s = plugin name.
			__( '%1$s: The preload service is now active. After the initial preload it will continue to cache all your pages whenever they are purged. No further action is needed.', 'rocket' ),
			'<strong>WP Rocket</strong>'
		);

		rocket_notice_html(
			[
				'status'  => 'info',
				'message' => $message,
				'id'      => 'rocket-notice-preload-processing',
			]
		);
	}

	/**
	 * Checks if we can display the Preload notices.
	 *
	 * @return bool
	 */
	private function can_display_notice(): bool {
		$screen = get_current_screen();

		if (
			isset( $screen->id )
			&&
			'settings_page_wprocket' !== $screen->id
		) {
			return false;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		return $this->is_enabled();
	}

	/**
	 * Determines if Preload option is enabled.
	 *
	 * @return boolean
	 */
	public function is_enabled() : bool {
		return (bool) $this->options->get( 'manual_preload', 0 );
	}
}
