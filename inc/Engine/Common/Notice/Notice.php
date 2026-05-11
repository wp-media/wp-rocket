<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\Notice;

use WP_Rocket\Admin\Options_Data;

class Notice {
	/**
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Creates an instance of the Notice object.
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}
	/**
	 * Display an update notice when the plugin is updated.
	 *
	 * @param array $notice_info Notice information {.
	 * @type string $version Version of the plugin.
	 * @type string $message Notice message.
	 * @type string $action Notice action.
	 * @type string $dismiss_message Dismiss message button title.
	 * @type string $dismiss_button Dismiss button.
	 *  }
	 * @return void
	 */
	public function display_update_notice( array $notice_info ): void {
		$previous_version = $this->options->get( 'previous_version' );
		$version          = $notice_info['version'] ?? '';

		// Bail-out for fresh install.
		if ( empty( $previous_version ) ) {
			return;
		}

		// Bail-out if previous version is greater than or equal to the new version.
		if ( version_compare( $previous_version, $version, '>=' ) ) {
			return;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( $notice_info['dismiss_button'], (array) $boxes, true ) ) {
			return;
		}

		rocket_notice_html(
			[
				'status'                 => 'info',
				'message'                => $notice_info['message'],
				'action'                 => $notice_info['action'],
				'dismiss_button'         => $notice_info['dismiss_button'],
				'dismiss_button_message' => $notice_info['dismiss_message'],
				'dismiss_button_class'   => 'button button-secondary',
			]
		);
	}
}
