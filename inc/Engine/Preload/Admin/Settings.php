<?php

namespace WP_Rocket\Engine\Preload\Admin;

use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Admin\Options_Data;

class Settings {

	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * PreloadUrl instance
	 *
	 * @var PreloadUrl
	 */
	private $preload_url;

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options Instance of options handler.
	 * @param PreloadUrl   $preload_url PreloadUrl instance.
	 */
	public function __construct( Options_Data $options, PreloadUrl $preload_url ) {
		$this->options     = $options;
		$this->preload_url = $preload_url;
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

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( 'preload_notice', (array) $boxes, true ) ) {
			return;
		}

		$message = sprintf(
			// translators: %1$s = plugin name.
			__( '%1$s: The preload service is now active. After the initial preload it will continue to cache all your pages whenever they are purged. No further action is needed.', 'rocket' ),
			'<strong>WP Rocket</strong>'
		);

		rocket_dismiss_box( 'preload_notice' );

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

	/**
	 * Preload the homepage
	 *
	 * @return void
	 */
	public function preload_homepage() {
		$this->preload_url->preload_url( home_url() );
	}
}
