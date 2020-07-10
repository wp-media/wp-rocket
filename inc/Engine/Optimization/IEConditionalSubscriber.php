<?php

namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Handles IE conditionals comments in the HTML to prevent their content from being processed during optimization.
 *
 * @since  3.6 Changes template tag to be an HTML comment.
 * @since  3.1
 */
class IEConditionalSubscriber implements Subscriber_Interface {

	/**
	 * Stores IE conditionals.
	 *
	 * @since  3.1
	 *
	 * @var array
	 */
	private $conditionals = [];

	/**
	 * HTML IE conditional pattern.
	 *
	 * @since 3.6.2
	 *
	 * @var string
	 */
	const IE_PATTERN = '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is';

	/**
	 * HTML IE conditional template tag.
	 *
	 * @since 3.6.2
	 *
	 * @var string
	 */
	const WP_ROCKET_CONDITIONAL = '<!--{{WP_ROCKET_CONDITIONAL}}-->';

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @since  3.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [
				[ 'extract_ie_conditionals', 1 ],
				[ 'inject_ie_conditionals', 34 ],
			],
		];
	}

	/**
	 * Extracts IE conditionals tags and replace them with placeholders.
	 *
	 * @since  3.1
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function extract_ie_conditionals( $html ) {
		preg_match_all( self::IE_PATTERN, $html, $conditionals_match );

		if ( ! $conditionals_match ) {
			return $html;
		}

		foreach ( $conditionals_match[0] as $conditional ) {
			$this->conditionals[] = $conditional;
		}

		return preg_replace( self::IE_PATTERN, self::WP_ROCKET_CONDITIONAL, $html );
	}

	/**
	 * Replaces WP Rocket placeholders with IE conditional tags.
	 *
	 * @since  3.1
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function inject_ie_conditionals( $html ) {
		if ( ! $this->has_conditional_tag( $html ) ) {
			return $html;
		}

		foreach ( $this->conditionals as $conditional ) {
			// Prevent scripts containing things like "\\s" to be striped of a backslash when put back in content.
			if ( preg_match( '@^(?<opening><!--\[if[^\]]*?\]>\s*?(?:<!-->)?\s*<script(?:\s[^>]*?>))\s*(?<content>.*?)\s*(?<closing></script>\s*(?:<!--)?\s*?<!\[endif\]-->)$@is', $conditional, $matches ) ) {
				$conditional = $matches['opening'] . preg_replace( '#(?<!\\\\)(\\$|\\\\)#', '\\\\$1', $matches['content'] ) . $matches['closing'];
			}

			$html = $this->replace_conditional_tag( $html, $conditional );
		}

		return $html;
	}

	/**
	 * Checks if the template tag for the IE conditional exists in the given HTML string.
	 *
	 * @since 3.6.2
	 *
	 * @param string $html HTML content.
	 *
	 * @return bool true if at least one exists; else false.
	 */
	private function has_conditional_tag( $html ) {
		return ( false !== strpos( $html, self::WP_ROCKET_CONDITIONAL ) );
	}

	/**
	 * Replaces the template tag with the original IE conditional HTML.
	 *
	 * @since 3.6.2
	 *
	 * @param string $html     HTML content.
	 * @param string $original Original IE conditional HTML.
	 *
	 * @return string
	 */
	private function replace_conditional_tag( $html, $original ) {
		$template_tag_position = strpos( $html, self::WP_ROCKET_CONDITIONAL );

		return substr_replace(
			$html,
			$original,
			$template_tag_position,
			strlen( self::WP_ROCKET_CONDITIONAL )
		);
	}
}
