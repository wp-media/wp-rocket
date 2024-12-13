<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Clean;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Clean instance
	 *
	 * @var Clean
	 */
	private $clean;

	/**
	 * Constructor
	 *
	 * @param Clean $clean Clean instance.
	 */
	public function __construct( Clean $clean ) {
		$this->clean = $clean;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_after_clean_domain'        => 'clean_fonts_css',
			'switch_theme'                     => 'clean_fonts',
			'rocket_domain_options_changed'    => [
				[ 'clean_fonts_css' ],
				[ 'clean_fonts' ],
			],
			'update_option_wp_rocket_settings' => [
				[ 'clean_on_option_change', 10, 2 ],
				[ 'clean_on_cdn_change', 11, 2 ],
			],
		];
	}

	/**
	 * Clean fonts CSS files stored locally
	 *
	 * @return void
	 */
	public function clean_fonts_css() {
		$this->clean->clean_fonts_css();
	}

	/**
	 * Clean fonts files stored locally
	 *
	 * @return void
	 */
	public function clean_fonts() {
		$this->clean->clean_fonts();
	}

	/**
	 * Clean CSS & fonts files stored locally on option change
	 *
	 * @param mixed $old_value Old option value.
	 * @param mixed $value     New option value.
	 *
	 * @return void
	 */
	public function clean_on_option_change( $old_value, $value ) {
		$this->clean->clean_on_option_change( $old_value, $value );
	}

	/**
	 * Clean CSS & fonts files stored locally on CDN change
	 *
	 * @param mixed $old_value Old option value.
	 * @param mixed $value     New option value.
	 *
	 * @return void
	 */
	public function clean_on_cdn_change( $old_value, $value ) {
		$this->clean->clean_on_cdn_change( $old_value, $value );
	}
}
