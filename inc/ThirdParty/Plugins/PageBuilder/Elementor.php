<?php
namespace WP_Rocket\ThirdParty\Plugins\PageBuilder;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;


/**
 * Compatibility file for Elementor plugin
 *
 * @since 3.3.1
 * @author Remy Perona
 */
class Elementor implements Subscriber_Interface {
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
	 * @since 3.3.1
	 * @author Remy Perona
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
	 * @since  3.3.1
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return [];
		}

		return [
			'wp_rocket_loaded'                    => 'remove_widget_callback',
			'rocket_exclude_css'                  => 'exclude_post_css',
			'elementor/core/files/clear_cache'    => 'clear_cache',
			'update_option__elementor_global_css' => 'clear_cache',
			'delete_option__elementor_global_css' => 'clear_cache',
			'rocket_buffer'                       => [ 'add_fix_animation_script', 28 ],
		];
	}

	/**
	 * Remove the callback to clear the cache on widget update
	 *
	 * @since 3.3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function remove_widget_callback() {
		remove_filter( 'widget_update_callback', 'rocket_widget_update_callback' );
	}

	/**
	 * Clear WP Rocket caches when Elementor changes the CSS
	 *
	 * @since 3.3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function clear_cache() {
		if ( ! $this->elementor_use_external_file() ) {
			return;
		}

		rocket_clean_domain();
		rocket_clean_minify( 'css' );
	}

	/**
	 * Checks whether elementor is set use external CSS file or not.
	 *
	 * @since 3.3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function elementor_use_external_file() {
		return 'internal' !== get_option( 'elementor_css_print_method' );
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

	/**
	 * Excludes Elementor CSS from minify/combine
	 *
	 * @since 3.10.9
	 *
	 * @param array $excluded Array of excluded patterns.
	 *
	 * @return array
	 */
	public function exclude_post_css( $excluded ): array {
		if ( ! $this->elementor_use_external_file() ) {
			return $excluded;
		}

		$upload   = wp_get_upload_dir();
		$basepath = wp_parse_url( $upload['baseurl'], PHP_URL_PATH );

		if ( empty( $basepath ) ) {
			return $excluded;
		}

		$excluded[] = $basepath . '/elementor/css/(.*).css';

		return $excluded;
	}
}
