<?php
namespace WP_Rocket\Engine\Media\Emojis;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Event subscriber to control Emoji behavior.
 *
 * @since  3.7
 */
class EmojisSubscriber implements Subscriber_Interface {

	/**
	 * The Options Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * EmojisSubscriber constructor.
	 *
	 * @param Options_Data $options An Options Data instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.7
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'init' => 'disable_emoji',
		];
	}

	/**
	 * Disable the emoji functionality to reduce then number of external HTTP requests.
	 *
	 * @since 3.7 Moved to new architecture.
	 * @since 2.7
	 */
	public function disable_emoji() {
		if ( ! $this->can_disable_emoji() ) {
			return;
		}

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		add_filter( 'emoji_svg_url', '__return_false' );
	}

	/**
	 * Remove the tinymce emoji plugin.
	 *
	 * @since 3.8 deprecated
	 * @since 3.7 Moved to new architecture.
	 * @since 2.7
	 *
	 * @param array $plugins Plugins loaded for TinyMCE.
	 *
	 * @return array
	 */
	public function disable_emoji_tinymce( array $plugins ) {
		_deprecated_function( 'WP_Rocket\Engine\Media\Emojis\EmojisSubscriber::disable_emoji_tinymce', '3.8' );

		if ( ! $this->can_disable_emoji() ) {
			return $plugins;
		}

		return array_diff( $plugins, [ 'wpemoji' ] );
	}

	/**
	 * Check for emoji option enabled & not bypassed.
	 *
	 * @since 3.7
	 *
	 * @return bool
	 */
	private function can_disable_emoji() {
		return ! rocket_bypass() && (bool) $this->options->get( 'emoji', 0 );
	}
}
