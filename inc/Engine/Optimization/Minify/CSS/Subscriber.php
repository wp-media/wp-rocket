<?php
namespace WP_Rocket\Engine\Optimization\Minify\CSS;

use MatthiasMullie\Minify\CSS as MinifyCSS;
use WP_Rocket\Engine\Optimization\Minify\AbstractMinifySubscriber;

/**
 * Minify/Combine CSS subscriber
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
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function process( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		if ( $this->options->get( 'minify_css' ) && $this->options->get( 'minify_concatenate_css' ) ) {
			$this->set_optimization_type( new Combine( $this->options, new MinifyCSS() ) );
		} elseif ( $this->options->get( 'minify_css' ) && ! $this->options->get( 'minify_concatenate_css' ) ) {
			$this->set_optimization_type( new Minify( $this->options ) );
		}

		return $this->optimize( $html );
	}

	/**
	 * Checks if is allowed to Minify/Combine CSS.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	protected function is_allowed() {
		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
		}

		if ( defined( 'DONOTMINIFYCSS' ) && DONOTMINIFYCSS ) {
			return false;
		}

		if ( ! $this->options->get( 'minify_css' ) ) {
			return false;
		}

		if ( is_rocket_post_excluded_option( 'minify_css' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns an array of CDN zones for CSS files.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public function get_zones() {
		return [ 'all', 'css_and_js', 'css' ];
	}
}
