<?php
namespace WP_Rocket\Engine\Optimization\Minify\JS;

use WP_Rocket\Dependencies\Minify\JS as MinifyJS;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
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

		$assets_local_cache = new AssetsLocalCache( rocket_get_constant( 'WP_ROCKET_MINIFY_CACHE_PATH' ), $this->filesystem );
		$container          = apply_filters( 'rocket_container', null );
		$dynamic_lists      = $container->get( 'dynamic_lists' );

		if ( $this->options->get( 'minify_js' ) && $this->options->get( 'minify_concatenate_js' ) ) {
			$this->set_processor_type( new Combine( $this->options, new MinifyJS(), $assets_local_cache, $container->get( 'defer_js' ), $dynamic_lists ) );
		} elseif ( $this->options->get( 'minify_js' ) && ! $this->options->get( 'minify_concatenate_js' ) ) {
			$this->set_processor_type( new Minify( $this->options, $assets_local_cache, $dynamic_lists ) );
		}

		return $this->processor->optimize( $html );
	}

	/**
	 * Checks if is allowed to Minify/Combine JS.
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	protected function is_allowed() {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( ! (bool) $this->options->get( 'minify_js', 0 ) ) {
			return false;
		}

		return ! is_rocket_post_excluded_option( 'minify_js' );
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
