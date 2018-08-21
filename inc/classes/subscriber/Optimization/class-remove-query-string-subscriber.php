<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Optimization\Remove_Query_String;
use WP_Rocket\Subscriber\Optimization\Minify_Subscriber;

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
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Remove_Query_String $remove_query_string Remove Query String instance.
	 */
	public function __construct( Remove_Query_String $remove_query_string ) {
		$this->remove_query_string = $remove_query_string;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [ 'process', 19 ],
		];
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

		$html = $this->remove_query_string->remove_query_strings_css( $html );
		$html = $this->remove_query_string->remove_query_strings_js( $html );

		return $html;
	}

	/**
	 * @inheritDoc
	 */
	protected function is_allowed() {
		return $this->remove_query_string->is_allowed();
	}
}
