<?php
namespace WP_Rocket\Engine\Optimization\QueryString;

use WP_Rocket\deprecated\DeprecatedClassTrait;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Hooks into WordPress to remove query strings for static files.
 *
 * @since  3.1
 * @since  3.6 Deprecated.
 * @author Remy Perona
 * @deprecated
 */
class RemoveSubscriber implements Subscriber_Interface {
	use DeprecatedClassTrait;

	/**
	 * Remove Query String instance.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var Remove
	 */
	protected $remove_query_string;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Remove $remove_query_string Remove Query String instance.
	 */
	public function __construct( Remove $remove_query_string ) {
		self::deprecated_class( '3.6' );
		$this->remove_query_string = $remove_query_string;
	}

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
			'rocket_buffer' => [ 'process', 30 ],
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
	 * Checks if is allowed to remove query strings for static files.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	protected function is_allowed() {
		return $this->remove_query_string->is_allowed();
	}
}
