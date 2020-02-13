<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data as Options;

/**
 * Undocumented class
 */
abstract class Minify_Subscriber implements Subscriber_Interface {
	/**
	 * Plugin options
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Optimizer instance
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var Optimizer_Interface
	 */
	protected $optimizer;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Options $options Plugin options.
	 */
	public function __construct( Options $options ) {
		$this->options = $options;
	}

	/**
	 * Sets the type of optimizer to use
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Optimizer_Interface $optimizer Optimizer instance.
	 * @return void
	 */
	protected function set_optimization_type( $optimizer ) {
		$this->optimizer = $optimizer;
	}

	/**
	 * Processes the HTML to perform an optimization and return the new content
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	abstract public function process( $html );

	/**
	 * Checks if files can be optimized
	 *
	 * @since 3.1
	 * @author Remy Perona
	 */
	abstract protected function is_allowed();

	/**
	 * Performs the optimization
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	protected function optimize( $html ) {
		return $this->optimizer->optimize( $html );
	}

	/**
	 * Fix issue with SSL and minification
	 *
	 * @since 2.3
	 *
	 * @param string $url An url to filter to set the scheme to https if needed.
	 * @return string
	 */
	public function fix_ssl_minify( $url ) {
		if ( ! is_ssl() ) {
			return $url;
		}

		if ( 0 === strpos( $url, 'https://' ) ) {
			return $url;
		}

		// This filter is documented in inc/classes/admin/settings/class-settings.php.
		if ( in_array( rocket_extract_url_component( $url, PHP_URL_HOST ), apply_filters( 'rocket_cdn_hosts', [], ( $this->get_zones() ) ), true ) ) {
			return $url;
		}

		return str_replace( 'http://', 'https://', $url );
	}

	/**
	 * Compatibility with multilingual plugins & multidomain configuration
	 *
	 * @since 2.6.13 Regression Fix: Apply CDN on minified CSS and JS files by checking the CNAME host
	 * @since 2.6.8
	 *
	 * @param string $url Minified file URL.
	 * @return string Updated minified file URL
	 */
	public function i18n_multidomain_url( $url ) {
		if ( ! \rocket_has_i18n() ) {
			return $url;
		}

		$url_host = \rocket_extract_url_component( $url, PHP_URL_HOST );

		if ( isset( $_SERVER['HTTP_HOST'] ) && $url_host === $_SERVER['HTTP_HOST'] ) {
			return $url;
		}

		if ( ! in_array( $_SERVER['HTTP_HOST'], \get_rocket_i18n_host(), true ) ) {
			return $url;
		}

		// This filter is documented in inc/classes/admin/settings/class-settings.php.
		$cdn_hosts = apply_filters( 'rocket_cdn_hosts', [], ( $this->get_zones() ) );

		if ( in_array( $url_host, $cdn_hosts, true ) ) {
			return $url;
		}

		return str_replace( $url_host, sanitize_text_field( $_SERVER['HTTP_HOST'] ), $url ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}
}
