<?php
namespace WP_Rocket\Subscriber\Media;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the WebP support
 *
 * @since 3.4
 * @author Remy Perona
 */
class Webp_Subscriber implements Subscriber_Interface {
	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer'                   => [ 'convert_to_webp', 23 ],
			'rocket_webp_section_description' => 'webp_section_description',
		];
	}

	/**
	 * Converts images extension to WebP if the file exists
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function convert_to_webp( $html ) {
		if ( ! $this->options->get( 'cache_webp' ) ) {
			return $html;
		}

		if ( ! preg_match_all( '#["\'\s](?<attr>(?:data-[a-z0-9_-]*)?(?:href|src|srcset|content))\s*=\s*["\']\s*(?<value>(?:https?:/)?/[^"\']+(?:\.png|\.jpe?g|gif)[^"\']*?)\s*["\']#', $html, $matches, PREG_SET_ORDER ) ) {
			return $html;
		}

		foreach ( $matches as $match ) {
			if ( $match['attr'] ) {
				continue;
			}
		}

		return $html;
	}

	/**
	 * Modifies the WebP section description of WP Rocket settings
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param string $description Section description.
	 * @return string
	 */
	public function webp_section_description( $description ) {
		$webp_plugin = $this->check_webp_plugin();

		if ( ! $webp_plugin ) {
			return $description;
		}

		$description = sprintf(
			// Translators: %1$s = plugin name, %2$s = opening link tag, %3$s = closing link tag.
			__( 'You are using %1$s to convert and serve images as WebP. %2$sMore info%3$s', 'rocket' ),
			$webp_plugin,
			'<a href="">',
			'</a>'
		);

		return $description;
	}

	/**
	 * Checks for the existence of a WebP plugin
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @return bool|
	 */
	private function check_webp_plugin() {
		if ( \rocket_is_plugin_active( 'imagify/imagify.php' ) ) {
			return 'Imagify';
		}

		return false;
	}
}
