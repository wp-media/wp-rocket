<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Optimization\CSS;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Combine Google Fonts subscriber
 *
 * @since 3.1
 * @author Remy Perona
 */
class Combine_Google_Fonts_Subscriber extends Minify_Subscriber {
	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		if ( apply_filters( 'rocket_buffer_enable', true ) ) {
			return [
				'rocket_buffer' => [ 'process', 13 ],
			];
		}
	}

	/**
	 * Processes the HTML to combine found Google fonts
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

		$crawler = $this->crawler;
		$crawler = $crawler::create( $html );

		$this->set_optimization_type( new CSS\Combine_Google_Fonts( $crawler ) );

		return $this->optimize();
	}

	/**
	 * @inheritDoc
	 */
	protected function is_allowed() {
		if ( ! $this->options->get( 'minify_google_fonts' ) ) {
			return false;
		}

		return true;
	}
}
