<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Optimization\JS;
use WP_Rocket\Admin\Options_Data as Options;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use \MatthiasMullie\Minify;

/**
 * Minify/Combine JS subscriber
 *
 * @since 3.1
 * @author Remy Perona
 */
class Minify_JS_Subscriber extends Minify_Subscriber {
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

		add_filter( 'rocket_buffer', [ $self, 'process' ], 14 );
		add_filter( 'rocket_js_url', [ $self, 'fix_ssl_minify' ] );
		add_filter( 'rocket_js_url', [ $self, 'i18n_multidomain_url' ] );
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

		if ( $this->options->get( 'minify_js' ) && $this->options->get( 'minify_concatenate_js' ) ) {
			$this->set_optimization_type( new JS\Combine( $crawler, $this->options, new Minify\JS() ) );
		} elseif ( $this->options->get( 'minify_js' ) && ! $this->options->get( 'minify_concatenate_js' ) ) {
			$this->set_optimization_type( new JS\Minify( $crawler, $this->options ) );
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
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public function get_zones() {
		return [ 'all', 'css_and_js', 'js' ];
	}
}
