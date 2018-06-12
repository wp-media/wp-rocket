<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Admin\Options_Data as Options;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Undocumented class
 */
abstract class Minify_Subscriber {
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
	 * Crawler instance
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var HtmlPageCrawler
	 */
	protected $crawler;

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
	 * @param Options         $options Plugin options.
	 * @param HtmlPageCrawler $crawler Crawler instance.
	 */
	public function __construct( Options $options, HtmlPageCrawler $crawler ) {
		$this->options = $options;
		$this->crawler = $crawler;
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
	 * @return string
	 */
	protected function optimize() {
		return $this->optimizer->optimize();
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

		if ( in_array( rocket_extract_url_component( $url, PHP_URL_HOST ), get_rocket_cnames_host( $this->get_zones() ), true ) ) {
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

		if ( $url_host === $_SERVER['HTTP_HOST'] ) {
			return $url;
		}

		$cnames = \get_rocket_cdn_cnames( $this->get_zones() );
		$cnames = array_map( 'rocket_remove_url_protocol', $cnames );

		if ( ! in_array( $_SERVER['HTTP_HOST'], \get_rocket_i18n_host(), true ) ) {
			return $url;
		}

		if ( in_array( $url_host, $cnames, true ) ) {
			return $url;
		}

		return str_replace( $url_host, $_SERVER['HTTP_HOST'], $url );
	}

	/**
	 * Extracts IE conditionals tags and replace them with placeholders
	 *
	 * @since 1.0
	 *
	 * @param string $html HTML content.
	 * @return array
	 */
	protected function extract_ie_conditionals( $html ) {
		preg_match_all( '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', $html, $conditionals_match );
		$html = preg_replace( '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '{{WP_ROCKET_CONDITIONAL}}', $html );

		$conditionals = array();
		foreach ( $conditionals_match[0] as $conditional ) {
			$conditionals[] = $conditional;
		}

		return array( $html, $conditionals );
	}

	/**
	 * Replaces WP Rocket placeholders with IE condtional tags
	 *
	 * @since 1.0
	 *
	 * @param string $html HTML content.
	 * @param array  $conditionals An array of IE conditional tags.
	 * @return string
	 */
	protected function inject_ie_conditionals( $html, $conditionals ) {
		foreach ( $conditionals as $conditional ) {
			if ( false === strpos( $html, '{{WP_ROCKET_CONDITIONAL}}' ) ) {
				continue;
			}

			$html = preg_replace( '/{{WP_ROCKET_CONDITIONAL}}/', $conditional, $html, 1 );
		}

		return $html;
	}
}
