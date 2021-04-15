<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

class Subscriber implements Subscriber_Interface {

	/**
	 * UsedCss instance
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
	public static function get_subscribed_events() : array {
		return [
			'rocket_buffer'                          => [ 'treeshake', 12 ],
			'rocket_rucss_retries_cron'              => 'rucss_retries',
			'rocket_disable_preload_fonts'           => 'maybe_disable_preload_fonts',
			'rocket_rucss_exclude_link_style_remove' => [ 'exclude_safelist_files', 10, 2 ],
		];
	}

	/**
	 * Apply TreeShaked CSS to the current HTML page.
	 *
	 * @param string $html  HTML content.
	 *
	 * @return string  HTML content.
	 */
	public function treeshake( string $html ) : string {
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
	 * Disables the preload fonts if RUCSS is enabled + CPCSS disabled
	 *
	 * @since 3.9
	 *
	 * @param bool $value Value for the disable preload fonts filter.
	 *
	 * @return bool
	 */
	public function maybe_disable_preload_fonts( $value ): bool {
		if (
			$this->used_css->is_allowed()
			&&
			! $this->used_css->cpcss_enabled()
		) {
			return true;
		}

		return $value;
	}

	/**
	 * Exclude this link CSS file from being removed from the page if it matches with the safelist files.
	 *
	 * @param bool   $exclude_status Status of exclusion.
	 * @param string $url Url of linked CSS to be checked.
	 *
	 * @return bool
	 */
	public function exclude_safelist_files( bool $exclude_status, string $url ) {
		return $exclude_status || $this->used_css->safelist_files_contains( $url );
	}

}
