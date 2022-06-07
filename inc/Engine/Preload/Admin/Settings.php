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
		$transient = get_transient( 'wpr_preload_running' );

		if ( false === $transient ) {
			return;
		}

		$message = sprintf(
		// translators: %1$s = plugin name.
			__( '%1$s: Please wait. The preload service is processing your pages.', 'rocket' ),
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

		if ( ! $this->is_enabled() ) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if Preload sitemap option is enabled.
	 *
	 * @return boolean
	 */
	public function is_enabled() : bool {
		return (bool) $this->options->get( 'manual_preload', 0 );
	}
}
