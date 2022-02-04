<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

class Subscriber implements Subscriber_Interface {
	/**
	 * UsedCSS instance
	 *
	 * @var UsedCSS
	 */
	private $used_css;

	/**
	 * Instantiate the class
	 *
	 * @param UsedCSS $used_css UsedCSS instance.
	 */
	public function __construct( UsedCSS $used_css ) {
		$this->used_css = $used_css;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_buffer'             => [ 'treeshake', 12 ],
			'rocket_rucss_retries_cron' => 'rucss_retries',
			'rocket_preload_fonts'      => 'remove_unused_fonts',
		];
	}

	/**
	 * Apply TreeShaked CSS to the current HTML page.
	 *
	 * @param string $html  HTML content.
	 *
	 * @return string  HTML content.
	 */
	public function treeshake( string $html ): string {
		return $this->used_css->treeshake( $html );
	}

	/**
	 * Retries to regenerate the used css.
	 *
	 * @return void
	 */
	public function rucss_retries() {
		$this->used_css->retries_pages_with_unprocessed_css();
	}

	/**
	 * Removes the unused fonts from the fonts preload
	 *
	 * @since 3.11
	 *
	 * @param string[] $fonts Array of fonts to preload.
	 *
	 * @return array
	 */
	public function remove_unused_fonts( $fonts ): array {
		return $this->used_css->remove_unused_fonts( $fonts );
	}
}
