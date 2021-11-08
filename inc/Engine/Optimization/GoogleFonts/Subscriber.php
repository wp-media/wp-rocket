<?php
namespace WP_Rocket\Engine\Optimization\GoogleFonts;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Combine Google Fonts subscriber
 *
 * @since 3.1
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Plugin options.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Combine instance.
	 *
	 * @var AbstractGFOptimization
	 */
	private $combine;

	/**
	 * CombineV2 instance.
	 *
	 * @var AbstractGFOptimization
	 */
	private $combine_v2;

	/**
	 * Instantiate the subscriber.
	 *
	 * @param AbstractGFOptimization $combine Combine instance.
	 * @param AbstractGFOptimization $combine_v2 Combine V2 instance.
	 * @param Options_Data           $options Options_Data instance.
	 */
	public function __construct( AbstractGFOptimization $combine, AbstractGFOptimization $combine_v2, Options_Data $options ) {
		$this->combine    = $combine;
		$this->combine_v2 = $combine_v2;
		$this->options    = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'wp_resource_hints' => [ 'preconnect', 10, 2 ],
			'rocket_buffer'     => [ 'process', 18 ],
		];
	}

	/**
	 * Adds google fonts URL to preconnect
	 *
	 * @since 3.5.3
	 *
	 * @param array  $urls          URLs to print for resource hints.
	 * @param string $relation_type The relation type the URLs are printed for, e.g. 'preconnect' or 'prerender'.
	 * @return array
	 */
	public function preconnect( array $urls, $relation_type ) {
		if ( ! $this->is_allowed() ) {
			return $urls;
		}

		if ( 'preconnect' !== $relation_type ) {
			return $urls;
		}

		$urls[] = [
			'href' => 'https://fonts.gstatic.com',
			1      => 'crossorigin',
		];

		return $urls;
	}

	/**
	 * Processes the HTML to combine found Google fonts.
	 * Handles both Google Fonts API v2 and v1.
	 *
	 * @since 3.1
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function process( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		// Combine Google Font API V2.
		$html = $this->combine_v2->optimize( $html );
		// Combine Google Font API V1.
		$html = $this->combine->optimize( $html );

		if ( ! $this->combine->has_google_fonts() && ! $this->combine_v2->has_google_fonts() ) {
			$html = preg_replace( '/<link\s+(?:[^>]+[\s"\'])?href\s*=\s*[\'"]https:\/\/fonts\.gstatic\.com[\'"](?:[^>]+[\s"\'])?\s?\/?>/', '', $html );
		}

		return $html;
	}

	/**
	 * Checks if files can combine found Google fonts.
	 *
	 * @since 3.1
	 */
	protected function is_allowed() {
		if ( rocket_bypass() ) {
			return false;
		}

		return (bool) $this->options->get( 'minify_google_fonts', 0 );
	}
}
