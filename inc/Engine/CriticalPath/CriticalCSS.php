<?php

namespace WP_Rocket\Engine\CriticalPath;

use FilesystemIterator;
use UnexpectedValueException;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;

/**
 * Handles the critical CSS generation process.
 *
 * @since 2.11
 */
class CriticalCSS {
	/**
	 * Background Process instance.
	 *
	 * @var CriticalCSSGeneration
	 */
	public $process;

	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Items for which we generate a critical CSS
	 *
	 * @var array
	 */
	public $items = [];

	/**
	 * Path to the critical CSS directory
	 *
	 * @var string
	 */
	private $critical_css_path;

	/**
	 * Instance of the filesystem handler.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Creates an instance of CriticalCSS.
	 *
	 * @since 2.11
	 *
	 * @param CriticalCSSGeneration $process    Background process instance.
	 * @param Options_Data          $options    Instance of options data handler.
	 * @param WP_Filesystem_Direct  $filesystem Instance of the filesystem handler.
	 */
	public function __construct( CriticalCSSGeneration $process, Options_Data $options, $filesystem ) {
		$this->process = $process;
		$this->options = $options;
		$this->items[] = [
			'type' => 'front_page',
			'url'  => home_url( '/' ),
		];

		$this->critical_css_path = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' ) . get_current_blog_id() . '/';
		$this->filesystem        = $filesystem;
	}

	/**
	 * Returns the current site critical CSS path.
	 *
	 * @since 3.3.5
	 *
	 * @return string
	 */
	public function get_critical_css_path() {
		return $this->critical_css_path;
	}

	/**
	 * Performs the critical CSS generation.
	 *
	 * @since 2.11
	 */
	public function process_handler() {
		/**
		 * Filters the critical CSS generation process.
		 *
		 * Use this filter to prevent the automatic critical CSS generation.
		 *
		 * @since 2.11.5
		 *
		 * @param bool $do_rocket_critical_css_generation True to activate the automatic generation, false to prevent it.
		 */
		if ( ! apply_filters( 'do_rocket_critical_css_generation', true ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			return;
		}

		if ( get_transient( 'rocket_critical_css_generation_process_running' ) ) {
			return;
		}

		$this->clean_critical_css();

		$this->stop_generation();

		$this->set_items();

		array_map( [ $this->process, 'push_to_queue' ], $this->items );

		$transient = [
			'generated' => 0,
			'total'     => count( $this->items ),
			'items'     => [],
		];

		set_transient( 'rocket_critical_css_generation_process_running', $transient, HOUR_IN_SECONDS );
		$this->process->save()->dispatch();
	}

	/**
	 * Stop the critical CSS generation process.
	 *
	 * @since 3.3
	 */
	public function stop_generation() {
		if ( method_exists( $this->process, 'cancel_process' ) ) {
			$this->process->cancel_process();
		}
	}

	/**
	 * Deletes critical CSS files.
	 *
	 * @since 2.11
	 * @since 3.6 Replaced glob().
	 */
	public function clean_critical_css() {
		try {
			$files = new FilesystemIterator( $this->critical_css_path );

			foreach ( $files as $file ) {
				if ( $this->filesystem->is_file( $file ) ) {
					$this->filesystem->delete( $file );
				}
			}
		} catch ( UnexpectedValueException $e ) {
			// No logging yet.
			return;
		}
	}

	/**
	 * Gets all public post types.
	 *
	 * @since 2.11
	 */
	public function get_public_post_types() {
		global $wpdb;

		$post_types = get_post_types(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);

		$post_types[] = 'page';

		/**
		 * Filters the post types excluded from critical CSS generation.
		 *
		 * @since 2.11
		 *
		 * @param array $excluded_post_types An array of post types names.
		 *
		 * @return array
		 */
		$excluded_post_types = (array) apply_filters(
			'rocket_cpcss_excluded_post_types',
			[
				'elementor_library',
				'oceanwp_library',
				'tbuilder_layout',
				'tbuilder_layout_part',
				'slider',
				'karma-slider',
				'tt-gallery',
				'xlwcty_thankyou',
				'fusion_template',
				'blocks',
				'jet-woo-builder',
				'fl-builder-template',
			]
		);

		$post_types = array_diff( $post_types, $excluded_post_types );
		$post_types = esc_sql( $post_types );
		$post_types = "'" . implode( "','", $post_types ) . "'";

		$rows = $wpdb->get_results(
			"
		    SELECT MAX(ID) as ID, post_type
		    FROM (
		        SELECT ID, post_type
		        FROM $wpdb->posts
				WHERE post_type IN ( $post_types )
		        AND post_status = 'publish'
		        ORDER BY post_date DESC
		    ) AS posts
		    GROUP BY post_type"
		);

		return $rows;
	}

	/**
	 * Gets all public taxonomies.
	 *
	 * @since  2.11
	 */
	public function get_public_taxonomies() {
		global $wpdb;

		$taxonomies = get_taxonomies(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);

		/**
		 * Filters the taxonomies excluded from critical CSS generation.
		 *
		 * @since  2.11
		 *
		 * @param array $excluded_taxonomies An array of taxonomies names.
		 *
		 * @return array
		 */
		$excluded_taxonomies = (array) apply_filters(
			'rocket_cpcss_excluded_taxonomies',
			[
				'post_format',
				'product_shipping_class',
				'karma-slider-category',
				'truethemes-gallery-category',
				'coupon_campaign',
				'element_category',
				'mediamatic_wpfolder',
				'attachment_category',
			]
		);

		$taxonomies = array_diff( $taxonomies, $excluded_taxonomies );
		$taxonomies = esc_sql( $taxonomies );
		$taxonomies = "'" . implode( "','", $taxonomies ) . "'";

		$rows = $wpdb->get_results(
			"SELECT MAX( term_id ) AS ID, taxonomy
			FROM (
				SELECT term_id, taxonomy
				FROM $wpdb->term_taxonomy
				WHERE taxonomy IN ( $taxonomies )
				AND count > 0
			) AS taxonomies
			GROUP BY taxonomy"
		);

		return $rows;
	}

	/**
	 * Sets the items for which we generate critical CSS.
	 *
	 * @since  2.11
	 */
	public function set_items() {
		$page_for_posts = get_option( 'page_for_posts' );

		if ( 'page' === get_option( 'show_on_front' ) && ! empty( $page_for_posts ) ) {
			$this->items[] = [
				'type' => 'home',
				'url'  => get_permalink( get_option( 'page_for_posts' ) ),
			];
		}

		$post_types = $this->get_public_post_types();

		foreach ( $post_types as $post_type ) {
			$this->items[] = [
				'type' => $post_type->post_type,
				'url'  => get_permalink( $post_type->ID ),
			];
		}

		$taxonomies = $this->get_public_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			$this->items[] = [
				'type' => $taxonomy->taxonomy,
				'url'  => get_term_link( (int) $taxonomy->ID, $taxonomy->taxonomy ),
			];
		}

		/**
		 * Filters the array containing the items to send to the critical CSS generator.
		 *
		 * @since  2.11.4
		 *
		 * @param array $this ->items Array containing the type/url pair for each item to send.
		 */
		$this->items = (array) apply_filters( 'rocket_cpcss_items', $this->items );
	}

	/**
	 * Gets the CPCSS content to use on the current page.
	 *
	 * @since 3.6
	 *
	 * @return bool|string
	 */
	public function get_critical_css_content() {
		$filename = $this->get_current_page_critical_css();

		if ( empty( $filename ) ) {
			return $this->options->get( 'critical_css', '' );
		}

		return $this->filesystem->get_contents( $filename );
	}

	/**
	 * Gets the CPCSS filepath for the current page.
	 *
	 * @since  2.11
	 *
	 * @return string Filepath if the file exists, empty string otherwise.
	 */
	public function get_current_page_critical_css() {
		$files = $this->get_critical_css_filenames();

		if (
			$this->is_async_css_mobile()
			&&
			wp_is_mobile()
			&&
			$this->filesystem->is_readable( $this->critical_css_path . $files['mobile'] )
		) {
			return $this->critical_css_path . $files['mobile'];
		}

		if ( $this->filesystem->is_readable( $this->critical_css_path . $files['default'] ) ) {
			return $this->critical_css_path . $files['default'];
		}

		return '';
	}

	/**
	 * Gets the CPCSS filenames for the current URL type.
	 *
	 * @since 3.6
	 *
	 * @return array
	 */
	private function get_critical_css_filenames() {
		$default = [
			'default' => 'front_page.css',
			'mobile'  => 'front_page-mobile.css',
		];

		if ( is_home() && 'page' === get_option( 'show_on_front' ) ) {
			return [
				'default' => 'home.css',
				'mobile'  => 'home-mobile.css',
			];
		}

		if ( is_front_page() ) {
			return $default;
		}

		if ( is_category() ) {
			return [
				'default' => 'category.css',
				'mobile'  => 'category-mobile.css',
			];
		}

		if ( is_tag() ) {
			return [
				'default' => 'post_tag.css',
				'mobile'  => 'post_tag-mobile.css',
			];
		}

		if ( is_tax() ) {
			$taxonomy = get_queried_object()->taxonomy;

			return [
				'default' => "{$taxonomy}.css",
				'mobile'  => "{$taxonomy}-mobile.css",
			];
		}

		if ( is_singular() ) {
			return $this->get_singular_cpcss_filenames();
		}

		return $default;
	}

	/**
	 * Gets the filenames for a singular content.
	 *
	 * @since 3.6
	 *
	 * @return array
	 */
	private function get_singular_cpcss_filenames() {
		$post_type  = get_post_type();
		$post_id    = get_the_ID();
		$post_cpcss = [
			'default' => "posts/{$post_type}-{$post_id}.css",
			'mobile'  => "posts/{$post_type}-{$post_id}-mobile.css",
		];

		if (
			$this->is_async_css_mobile()
			&&
			! $this->filesystem->exists( $this->critical_css_path . $post_cpcss['mobile'] )
		) {
			$post_cpcss['mobile'] = $post_cpcss['default'];
		}

		if ( $this->filesystem->exists( $this->critical_css_path . $post_cpcss['default'] ) ) {
			return $post_cpcss;
		}

		return [
			'default' => "{$post_type}.css",
			'mobile'  => "{$post_type}-mobile.css",
		];
	}

	/**
	 * Checks if we are in a situation where we need the mobile CPCSS.
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	private function is_async_css_mobile() {
		if ( ! $this->options->get( 'do_caching_mobile_files', 0 ) ) {
			return false;
		}

		return (bool) $this->options->get( 'async_css_mobile', 0 );
	}
}
