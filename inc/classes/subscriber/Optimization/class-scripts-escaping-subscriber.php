<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Handles escaping of closing HTML tags in inline scripts to prevent them from being removed by DOMDocument
 *
 * @since 3.1
 * @author Remy Perona
 */
class Scripts_Escaping_Subscriber implements Subscriber_Interface {
	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		/** This action is documented in inc/classes/subscriber/class-google-tracking-cache-busting-subscriber.php */
		if ( apply_filters( 'rocket_buffer_enable', true ) ) {
			return [
				'rocket_buffer' => [
					[ 'escape_html_in_scripts', 1 ],
					[ 'unescape_html_in_scripts', PHP_INT_MAX ],
				],
			];
		}
	}

	/**
	 * Escapes HTML closing tags contained in inline scripts to prevent them from being removed by DOMDocument
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function escape_html_in_scripts( $html ) {
		preg_match_all( '/<script[^>]*?>(.*)<\/script>/msU', $html, $matches );

		foreach ( $matches[1] as $k => $match ) {
			if ( empty( $match ) ) {
				continue;
			}

			$match = str_replace( '</', '<\/', $match );
			$html  = str_replace( $matches[1][ $k ], $match, $html );
		}

		return $html;
	}

	/**
	 * Unescapes HTML closing tags contained in inline scripts once we're done with DOMDocument
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function unescape_html_in_scripts( $html ) {
		preg_match_all( '/<script[^>]*?>(.*)<\/script>/msU', $html, $matches );

		foreach ( $matches[1] as $k => $match ) {
			if ( empty( $match ) ) {
				continue;
			}

			$match = str_replace( '<\/', '</', $match );
			$html  = str_replace( $matches[1][ $k ], $match, $html );
		}

		return $html;
	}
}
