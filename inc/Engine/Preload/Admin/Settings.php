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

		return $this->is_enabled();
	}

	/**
	 * Display missing as table notice if they are not present.
	 *
	 * @return void
	 */
	public function maybe_display_as_missed_tables_notice() {
		$as_tools_link = menu_page_url( 'action-scheduler', false );
		$message       = sprintf(
		// translators: %1$s = plugin name, %2$s = opening anchor tag, %3$s = closing anchor tag.
			__( '%1$s: We detected missing database table related to Action Scheduler. Please visit the following %2$sURL%3$s to recreate it, as it is needed for WP Rocket to work correctly.', 'rocket' ),
			'<strong>WP Rocket</strong>',
			'<a href="' . $as_tools_link . '">',
			'</a>'
		);
		rocket_notice_html(
			[
				'status'  => 'error',
				'message' => $message,
				'id'      => 'rocket-notice-as-missed-tables',
			]
		);
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
