<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Handles IE conditionals comments in the HTML to prevent their content from being processed during optimization
 *
 * @since 3.1
 * @author Remy Perona
 */
class IE_Conditionals_Subscriber implements Subscriber_Interface {
	/**
	 * Stores IE conditionals
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var array
	 */
	private $conditionals = [];

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [
				[ 'extract_ie_conditionals', 1 ],
				[ 'inject_ie_conditionals', 20 ],
			],
		];
	}

	/**
	 * Extracts IE conditionals tags and replace them with placeholders
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function extract_ie_conditionals( $html ) {
		preg_match_all( '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', $html, $conditionals_match );

		$html = preg_replace( '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '{{WP_ROCKET_CONDITIONAL}}', $html );

		foreach ( $conditionals_match[0] as $conditional ) {
			$this->conditionals[] = $conditional;
		}

		return $html;
	}

	/**
	 * Replaces WP Rocket placeholders with IE conditional tags
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function inject_ie_conditionals( $html ) {
		foreach ( $this->conditionals as $conditional ) {
			if ( false === strpos( $html, '{{WP_ROCKET_CONDITIONAL}}' ) ) {
				break;
			}

			$html = preg_replace( '/{{WP_ROCKET_CONDITIONAL}}/', $conditional, $html, 1 );
		}

		return $html;
	}
}
