<?php
namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Bridge implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.3.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_lazyload_background_images' => 'return_false',
			'update_option_qode_options_proya'  => [ 'maybe_clear_cache', 10, 2 ],
		];
	}

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Maybe clear WP Rocket cache when Bridge custom CSS/JS is updated
	 *
	 * @since 3.3.7
	 *
	 * @param array $old_value Previous option values.
	 * @param array $new_value New option values.
	 * @return void
	 */
	public function maybe_clear_cache( $old_value, $new_value ) {
		$clear = false;

		if ( $this->options->get( 'minify_css', 0 ) ) {
			if ( isset( $old_value['custom_css'], $new_value['custom_css'] ) && $old_value['custom_css'] !== $new_value['custom_css'] ) {
				$clear = true;
			}

			if ( isset( $old_value['custom_svg_css'], $new_value['custom_svg_css'] ) && $old_value['custom_svg_css'] !== $new_value['custom_svg_css'] ) {
				$clear = true;
			}
		}

		if ( $this->options->get( 'minify_js', 0 ) ) {
			if ( isset( $old_value['custom_js'], $new_value['custom_js'] ) && $old_value['custom_js'] !== $new_value['custom_js'] ) {
				$clear = true;
			}
		}

		if ( $clear ) {
			rocket_clean_domain();
			rocket_clean_minify();
		}
	}
}
