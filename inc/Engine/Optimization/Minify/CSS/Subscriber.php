<?php
namespace WP_Rocket\Engine\Optimization\Minify\CSS;

use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\Minify\AbstractMinifySubscriber;

/**
 * Minify/Combine CSS subscriber
 *
 * @since 3.1
 */
class Subscriber extends AbstractMinifySubscriber {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [
			'rocket_css_url' => [
				[ 'fix_ssl_minify' ],
				[ 'i18n_multidomain_url' ],
			],
			'rocket_buffer'  => [ 'process', 16 ],
		];

		return $events;
	}

	/**
	 * Processes the HTML to Minify/Combine CSS.
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

		$assets_local_cache = new AssetsLocalCache( rocket_get_constant( 'WP_ROCKET_MINIFY_CACHE_PATH' ), $this->filesystem );

		if ( $this->options->get( 'minify_css' ) ) {
			$this->set_processor_type( new Minify( $this->options, $assets_local_cache ) );
		}

		return $this->processor->optimize( $html );
	}

	/**
	 * Checks if is allowed to Minify/Combine CSS.
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	protected function is_allowed() {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( ! (bool) $this->options->get( 'minify_css', 0 ) ) {
			return false;
		}

		return ! is_rocket_post_excluded_option( 'minify_css' );
	}

	/**
	 * Returns an array of CDN zones for CSS files.
	 *
	 * @since 3.1
	 *
	 * @return array
	 */
	public function get_zones() {
		return [ 'all', 'css_and_js', 'css' ];
	}
}
