<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * DeferJS instance
	 *
	 * @var DeferJS
	 */
	private $defer_js;

	/**
	 * Instantiate the class
	 *
	 * @param DeferJS $defer_js DeferJS instance.
	 */
	public function __construct( DeferJS $defer_js ) {
		$this->defer_js = $defer_js;
	}

	/**
	 * Returns array of events this listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [
			'rocket_buffer'                      => [
				[ 'defer_js', 24 ],
				[ 'defer_inline_js', 25 ],
			],
			'rocket_exclude_js'                  => 'exclude_jquery_combine',
			'rocket_minify_excluded_external_js' => 'exclude_jquery_combine',
		];
	}

	/**
	 * Adds the defer attribute to JS files
	 *
	 * @since 3.8
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function defer_js( string $html ) : string {
		return $this->defer_js->defer_js( $html );
	}

	/**
	 * Defers inline JS containing jQuery calls
	 *
	 * @since 3.8
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function defer_inline_js( string $html ) : string {
		return $this->defer_js->defer_inline_js( $html );
	}

	/**
	 * Excludes jQuery from combine JS when defer and combine are enabled
	 *
	 * @since 3.8
	 *
	 * @param array $excluded_files Array of excluded files from combine JS.
	 * @return array
	 */
	public function exclude_jquery_combine( array $excluded_files ) : array {
		return $this->defer_js->exclude_jquery_combine( $excluded_files );
	}
}
