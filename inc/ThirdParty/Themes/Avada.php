<?php
namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for Avada theme
 */
class Avada implements Subscriber_Interface {
	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! self::is_avada() ) {
			return [];
		}

		return [
			'avada_clear_dynamic_css_cache'        => 'clean_domain',
			'rocket_exclude_defer_js'              => 'exclude_defer_js',
			'rocket_maybe_disable_lazyload_helper' => 'maybe_disable_lazyload',
			'fusion_cache_reset_after'             => 'clean_domain',
			'update_option_fusion_options'         => [ 'maybe_deactivate_lazyload', 10, 2 ],
			'rocket_wc_product_gallery_delay_js_exclusions' => 'exclude_delay_js',
		];
	}

	/**
	 * Check if is Avada theme.
	 *
	 * @return boolean
	 */
	private static function is_avada() {
		$current_theme = wp_get_theme();
		return 'avada' === strtolower( $current_theme->get( 'Name' ) ) || 'avada' === strtolower( $current_theme->get_template() );
	}

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * When Avada theme purge its own cache.
	 * Clear WP Rocket cache when Avada dynamic CSS is updated.
	 *
	 * @return void
	 */
	public function clean_domain() {
		rocket_clean_domain();
	}

	/**
	 * Deactivate WP Rocket lazyload if Avada lazyload is enabled.
	 *
	 * @since 3.3.4
	 *
	 * @param string $old_value Previous Avada option value.
	 * @param string $value     New Avada option value.
	 * @return void
	 */
	public function maybe_deactivate_lazyload( $old_value, $value ) {
		if (
			empty( $old_value['lazy_load'] )
			||
			( ! empty( $value['lazy_load'] ) && 'avada' === $value['lazy_load'] )
		) {
			update_rocket_option( 'lazyload', 0 );
		}
	}

	/**
	 * Excludes Avada Google Maps JS files from defer JS
	 *
	 * @param array $exclude_defer_js Array of JS filepaths to be excluded.
	 * @return array
	 */
	public function exclude_defer_js( $exclude_defer_js ) {
		$exclude_defer_js[] = '/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js';
		$exclude_defer_js[] = 'maps.googleapis.com';

		return $exclude_defer_js;
	}

	/**
	 * Disable WP Rocket lazyload field if Avada lazyload is enabled
	 *
	 * @since 3.3.4
	 * @param  array $disable_images_lazyload Array with plugins which disable lazyload functionality.
	 * @return array
	 */
	public function maybe_disable_lazyload( $disable_images_lazyload ) {
		$avada_options = get_option( 'fusion_options' );

		if ( empty( $avada_options['lazy_load'] ) ) {
			return $disable_images_lazyload;
		}

		if ( ! empty( $avada_options['lazy_load'] && 'avada' !== $avada_options['lazy_load'] ) ) {
			return $disable_images_lazyload;
		}

		$disable_images_lazyload[] = __( 'Avada', 'rocket' );
		return $disable_images_lazyload;
	}

	/**
	 * Excludes some Avada JS from delay JS execution  when WC product gallery has images
	 *
	 * @since 3.10.2
	 *
	 * @param array $exclusions Array of exclusion patterns.
	 *
	 * @return array
	 */
	public function exclude_delay_js( $exclusions ): array {
		$base_path = wp_parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH );

		if ( empty( $base_path ) ) {
			return $exclusions;
		}

		$exclusions[] = $base_path . '/includes/lib/assets/min/js/library/jquery.flexslider.js';
		$exclusions[] = $base_path . '/assets/min/js/general/avada-woo-product-images.js';

		return $exclusions;
	}
}
