<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Optimization\CSS;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use \MatthiasMullie\Minify;

/**
 * Minify/Combine CSS subscriber
 *
 * @since 3.1
 * @author Remy Perona
 */
class Minify_CSS_Subscriber extends Minify_Subscriber {
	/**
	 * Custom Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Options         $options Plugin options.
	 * @param HtmlPageCrawler $crawler Crawler instance.
	 */
	public static function init( Options $options, HtmlPageCrawler $crawler ) {
		$self = new self( $options, $crawler );

		add_filter( 'rocket_buffer', [ $self, 'process' ], 16 );
		add_filter( 'rocket_css_url', [ $self, 'fix_ssl_minify' ] );
		add_filter( 'rocket_css_url', [ $self, 'i18n_multidomain_url' ] );
	}

	/**
	 * @inheritDoc
	 */
	public function process( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		list( $html, $conditionals ) = $this->extract_ie_conditionals( $html );

		$crawler = $this->crawler;
		$crawler = $crawler::create( $html );

		if ( $this->options->get( 'minify_css' ) && $this->options->get( 'minify_concatenate_css' ) ) {
			$this->set_optimization_type( new CSS\Combine( $crawler, $this->options, new Minify\CSS() ) );
		} elseif ( $this->options->get( 'minify_css' ) && ! $this->options->get( 'minify_concatenate_css' ) ) {
			$this->set_optimization_type( new CSS\Minify( $crawler, $this->options ) );
		}

		return $this->inject_ie_conditionals( $this->optimize(), $conditionals );
	}

	/**
	 * @inheritDoc
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
