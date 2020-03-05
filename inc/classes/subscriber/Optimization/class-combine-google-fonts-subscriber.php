<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Optimization\CSS;

/**
 * Combine Google Fonts subscriber
 *
 * @since 3.1
 * @author Remy Perona
 */
class Combine_Google_Fonts_Subscriber extends Minify_Subscriber {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.1
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [ 'process', 18 ],
		];
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

		$this->set_optimization_type( new CSS\Combine_Google_Fonts() );

		return $this->optimize( $html );
	}

	/**
	 * Checks if files can combine found Google fonts.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 */
	protected function is_allowed() {
		if ( ! $this->options->get( 'minify_google_fonts' ) ) {
			return false;
		}

		return true;
	}
}
