<?php
namespace WP_Rocket\ThirdParty\Plugins\PageBuilder;

use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility file for Elementor Pro plugin
 *
 * @since 3.9.2
 */
class ElementorPro implements Subscriber_Interface {
	/**
	 * WP_Filesystem_Direct instance.
	 *
	 * @var \WP_Filesystem_Direct
	 */
	private $filesystem;


	/**
	 * Delay JS HTML class.
	 *
	 * @var HTML
	 */
	private $delayjs_html;


	/**
	 * Constructor
	 *
	 * @param \WP_Filesystem_Direct $filesystem The Filesystem object.
	 * @param HTML                  $delayjs_html DelayJS HTML class.
	 */
	public function __construct( $filesystem, HTML $delayjs_html ) {
		$this->filesystem   = $filesystem;
		$this->delayjs_html = $delayjs_html;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			return [];
		}

		return [
			'rocket_buffer' => [ 'add_fix_animation_script', 28 ],
		];
	}

	/**
	 * Add Fix Elementor Pro animations script.
	 *
	 * @since 3.9.2
	 *
	 * @param string $html HTML content.
	 *
	 * @return string HTML with Fix Elementor Pro animations script.
	 */
	public function add_fix_animation_script( $html ) {
		if ( ! $this->delayjs_html->is_allowed() ) {
			return $html;
		}
		$pattern = '/<\/body*>/i';

		$fix_elementor_animation_script = $this->filesystem->get_contents( rocket_get_constant( 'WP_ROCKET_PATH' ) . 'assets/js/elementor-animation.js' );

		if ( false !== $fix_elementor_animation_script ) {
			$html = preg_replace( $pattern, "<script>{$fix_elementor_animation_script}</script>$0", $html, 1 );
		}

		return $html;
	}
}
