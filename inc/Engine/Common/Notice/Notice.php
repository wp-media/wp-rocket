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
	 * @param string $version New plugin version.
	 * @return void
	 */
	public function display_update_notice( $version ): void {
		$previous_version = $this->options->get( 'previous_version' );

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

		if ( in_array( 'rocket_update_notice', (array) $boxes, true ) ) {
			return;
		}

		$message = sprintf(
		// translators: %1$s opening <strong> tag, %2$s closing </strong> tag.
			esc_html__(
				'%1$sUse RocketCDN for free to boost up to 3 pages 🚀%2$s',
				'rocket'
			),
			'<p><strong>',
			'</strong></p>'
		);

		$message .= sprintf(
		// translators: %1$s opening <p> tag, %2$s closing </p> tag.
			esc_html__(
				'%1$sAs a WP Rocket user, you can now activate RocketCDN for free on up to 3 pages. Choose your top pages and speed up their performance worldwide!%2$s',
				'rocket'
			),
			'<p>',
			'</p>'
		);

		rocket_notice_html(
			[
				'status'                 => 'info',
				'message'                => $message,
				'action'                 => 'rocketcdn_pages',
				'dismiss_button'         => 'rocket_update_notice',
				'dismiss_button_message' => __( 'Will check it later', 'rocket' ),
				'dismiss_button_class'   => 'button button-secondary',
			]
		);
	}
}
