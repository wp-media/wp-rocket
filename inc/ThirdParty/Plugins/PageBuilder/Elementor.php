<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\PageBuilder;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility file for Elementor plugin
 */
class Elementor implements Subscriber_Interface {
	/**
	 * WP Rocket options.
	 *
	 * @var Options_Data
	 */
	private $options;

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
	 * @param Options_Data          $options WP Rocket options.
	 * @param \WP_Filesystem_Direct $filesystem The Filesystem object.
	 * @param HTML                  $delayjs_html DelayJS HTML class.
	 */
	public function __construct( Options_Data $options, $filesystem, HTML $delayjs_html ) {
		$this->options      = $options;
		$this->filesystem   = $filesystem;
		$this->delayjs_html = $delayjs_html;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return [];
		}

		return [
			'wp_rocket_loaded'                    => 'remove_widget_callback',
			'added_post_meta'                     => [ 'maybe_clear_cache', 10, 3 ],
			'deleted_post_meta'                   => [ 'maybe_clear_cache', 10, 3 ],
			'elementor/core/files/clear_cache'    => 'clear_cache',
			'update_option__elementor_global_css' => 'clear_cache',
			'delete_option__elementor_global_css' => 'clear_cache',
			'rocket_buffer'                       => [ 'add_fix_animation_script', 28 ],
			'rocket_exclude_js'                   => 'exclude_js',
		];
	}

	/**
	 * Remove the callback to clear the cache on widget update
	 *
	 * @return void
	 */
	public function remove_widget_callback() {
		remove_filter( 'widget_update_callback', 'rocket_widget_update_callback' );
	}

	/**
	 * Clears WP Rocket caches if the combine CSS option is active.
	 *
	 * @param int    $meta_id   The meta ID.
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key.
	 * @return void
	 */
	public function maybe_clear_cache( $meta_id, $object_id, $meta_key ) {
		if ( '_elementor_css' !== $meta_key ) {
			return;
		}

		if ( ! $this->options->get( 'minify_concatenate_css' ) ) {
			return;
		}

		$this->clear_cache();
	}

	/**
	 * Clear WP Rocket caches when Elementor changes the CSS
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
	 * Excludes JS files from minify/combine JS
	 *
	 * @since 3.10.9
	 *
	 * @param array $excluded_files Array of excluded patterns.
	 *
	 * @return array
	 */
	public function exclude_js( $excluded_files ): array {
		if ( ! $this->options->get( 'minify_concatenate_js', false ) ) {
			return $excluded_files;
		}

		if ( ! $this->options->get( 'cache_logged_user', false ) ) {
			return $excluded_files;
		}

		if ( ! is_user_logged_in() ) {
			return $excluded_files;
		}

		$excluded_files[] = '/wp-includes/js/dist/hooks.min.js';

		return $excluded_files;
	}
}
