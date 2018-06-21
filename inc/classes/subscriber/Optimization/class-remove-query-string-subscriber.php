<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Optimization\Remove_Query_String;
use WP_Rocket\Subscriber\Optimization\Minify_Subscriber;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Hooks into WordPress to remove query strings for static files
 *
 * @since 3.1
 * @author Remy Perona
 */
class Remove_Query_String_Subscriber extends Minify_Subscriber {
	/**
	 * Remove Query String instance.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var Remove_Query_String
	 */
	protected $remove_query_string;

	/**
	 * Crawler instance.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var HtmlPageCrawler
	 */
	protected $crawler;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Remove_Query_String $remove_query_string Remove Query String instance.
	 * @param HtmlPageCrawler     $crawler             Crawler instance.
	 */
	public function __construct( Remove_Query_String $remove_query_string, HtmlPageCrawler $crawler ) {
		$this->remove_query_string = $remove_query_string;
		$this->crawler             = $crawler;
	}

	/**
	 * @inheritDoc
	 */
	public static function init( Remove_Query_String $remove_query_string, HtmlPageCrawler $crawler ) {
		$self = new self( $remove_query_string, $crawler );

		add_filter( 'rocket_buffer', [ $self, 'process' ], 19 );
	}

	/**
	 * Filters the HTML to fetch static files with a query string and remove it
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

		list( $html, $conditionals ) = $this->extract_ie_conditionals( $html );

		$this->remove_query_string->set_crawler( $this->crawler, $html );

		return $this->inject_ie_conditionals( $this->remove_query_string->optimize(), $conditionals );
	}

	/**
	 * @inheritDoc
	 */
	protected function is_allowed() {
		return $this->remove_query_string->is_allowed();
	}
}
