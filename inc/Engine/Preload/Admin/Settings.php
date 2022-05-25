<?php

namespace WP_Rocket\Engine\Preload\Admin;

use WP_Rocket\Admin\Options_Data;

class Settings {

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Initialise Settings.
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Display a notice if the preload is running.
	 *
	 * @return void
	 */
	public function maybe_display_preload_notice() {
		if ( ! $this->can_display_notice() ) {
			return;
		}
		$transient = get_transient( 'rocket_rucss_processing' );

		if ( false === $transient ) {
			return;
		}

		$message = sprintf(
		// translators: %1$s = plugin name, %2$s = number of seconds.
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
	 * Checks if we can display the RUCSS notices.
	 *
	 * @since 3.11
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
	 * Determines if Remove Unused CSS option is enabled.
	 *
	 * @since 3.9
	 *
	 * @return boolean
	 */
	public function is_enabled() : bool {
		return (bool) $this->options->get( 'sitemap_preload', 0 );
	}
}
