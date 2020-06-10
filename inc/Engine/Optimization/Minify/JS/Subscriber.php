<?php
namespace WP_Rocket\Engine\Optimization\Minify\JS;

use MatthiasMullie\Minify\JS as MinifyJS;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\Minify\AbstractMinifySubscriber;

/**
 * Minify/Combine JS subscriber
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
			'rocket_js_url' => [
				[ 'fix_ssl_minify' ],
				[ 'i18n_multidomain_url' ],
			],
			'rocket_buffer' => [ 'process', 22 ],
		];

		return $events;
	}

	/**
	 * Processes the HTML to Minify/Combine JS.
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

		if ( $this->options->get( 'minify_js' ) && $this->options->get( 'minify_concatenate_js' ) ) {
			$this->set_optimization_type( new Combine( $this->options, new MinifyJS(), new AssetsLocalCache( WP_ROCKET_MINIFY_CACHE_PATH ) ) );
		} elseif ( $this->options->get( 'minify_js' ) && ! $this->options->get( 'minify_concatenate_js' ) ) {
			$this->set_optimization_type( new Minify( $this->options ) );
		}

		return $this->optimize( $html );
	}

	/**
	 * Checks if is allowed to Minify/Combine JS.
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	protected function is_allowed() {
		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
		}

		if ( defined( 'DONOTMINIFYJS' ) && DONOTMINIFYJS ) {
			return false;
		}

		if ( ! $this->options->get( 'minify_js' ) ) {
			return false;
		}

		if ( is_rocket_post_excluded_option( 'minify_js' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns an array of CDN zones for JS files.
	 *
	 * @since 3.1
	 *
	 * @return array
	 */
	public function get_zones() {
		return [ 'all', 'css_and_js', 'js' ];
	}
}
