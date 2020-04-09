<?php
namespace WP_Rocket\Engine\Optimization\GoogleFonts;

use WP_Rocket\Engine\Optimization\Minify\AbstractMinifySubscriber;

/**
 * Combine Google Fonts subscriber
 *
 * @since 3.1
 * @author Remy Perona
 */
class Subscriber extends AbstractMinifySubscriber {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.1
	 * @author Remy Perona
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
	 * Processes the HTML to combine found Google fonts
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function process( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		$this->set_optimization_type( new Combine() );

		return $this->optimize( $html );
	}

	/**
	 * Checks if files can combine found Google fonts.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 */
	protected function is_allowed() {
		return (bool) $this->options->get( 'minify_google_fonts', 0 );
	}
}
