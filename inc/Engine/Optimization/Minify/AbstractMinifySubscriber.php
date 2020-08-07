<?php
namespace WP_Rocket\Engine\Optimization\Minify;

use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Parent class for minify subscribers.
 */
abstract class AbstractMinifySubscriber implements Subscriber_Interface {
	/**
	 * Plugin options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Processor instance.
	 *
	 * @var ProcessorInterface
	 */
	protected $processor;

	/**
	 * Filesystem instance
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Creates an instance of inheriting class.
	 *
	 * @since 3.1
	 *
	 * @param Options_Data         $options   Plugin options.
	 * @param WP_Filesystem_Direct $filesystem Filesystem instance.
	 */
	public function __construct( Options_Data $options, $filesystem ) {
		$this->options    = $options;
		$this->filesystem = $filesystem;
	}

	/**
	 * Sets the type of processor to use
	 *
	 * @since 3.1
	 *
	 * @param ProcessorInterface $processor Processor instance.
	 * @return void
	 */
	protected function set_processor_type( ProcessorInterface $processor ) {
		$this->processor = $processor;
	}

	/**
	 * Processes the HTML to perform an optimization and return the new content
	 *
	 * @since 3.1
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	abstract public function process( $html );

	/**
	 * Checks if files can be optimized
	 *
	 * @since 3.1
	 */
	abstract protected function is_allowed();

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

		// This filter is documented in inc/Engine/Admin/Settings/Settings.php.
		if ( in_array( wp_parse_url( $url, PHP_URL_HOST ), apply_filters( 'rocket_cdn_hosts', [], ( $this->get_zones() ) ), true ) ) {
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
		if ( ! rocket_has_i18n() ) {
			return $url;
		}

		$url_host = wp_parse_url( $url, PHP_URL_HOST );

		if ( isset( $_SERVER['HTTP_HOST'] ) && $url_host === $_SERVER['HTTP_HOST'] ) {
			return $url;
		}

		if ( ! in_array( $_SERVER['HTTP_HOST'], get_rocket_i18n_host(), true ) ) {
			return $url;
		}

		// This filter is documented in inc/Engine/Admin/Settings/Settings.php.
		$cdn_hosts = apply_filters( 'rocket_cdn_hosts', [], ( $this->get_zones() ) );

		if ( in_array( $url_host, $cdn_hosts, true ) ) {
			return $url;
		}

		return str_replace( $url_host, sanitize_text_field( $_SERVER['HTTP_HOST'] ), $url ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}
}
