<?php
namespace WP_Rocket\Optimization\CSS;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Handles the critical CSS generation process.
 *
 * @since 2.11
 * @author Remy Perona
 */
class Critical_CSS {
	/**
	 * Background Process instance
	 *
	 * @since 2.11
	 * @var object $process Background Process instance.
	 * @access public
	 */
	public $process;

	/**
	 * Items for which we generate a critical CSS
	 *
	 * @since 2.11
	 * @var array $items An array of items.
	 * @access public
	 */
	public $items = [];

	/**
	 * Path to the critical CSS directory
	 *
	 * @since 2.11
	 * @var string path to the critical css directory
	 * @access public
	 */
	public $critical_css_path;

	/**
	 * Class constructor.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param Critical_CSS_Generation $process Background process instance.
	 */
	public function __construct( Critical_CSS_Generation $process ) {
		$this->process = $process;
		$this->items[] = [
			'type' => 'front_page',
			'url'  => home_url( '/' ),
		];

		$this->critical_css_path = WP_ROCKET_CRITICAL_CSS_PATH . get_current_blog_id() . '/';
	}

	/**
	 * Performs the critical CSS generation
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function process_handler() {
		/**
		 * Filters the critical CSS generation process
		 *
		 * Use this filter to prevent the automatic critical CSS generation.
		 *
		 * @since 2.11.5
		 * @author Remy Perona
		 *
		 * @param bool $do_rocket_critical_css_generation True to activate the automatic generation, false to prevent it.
		 */
		if ( ! apply_filters( 'do_rocket_critical_css_generation', true ) ) { // WPCS: prefix ok.
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
	 * Stop the critical CSS generation process
	 *
	 * @since 3.3
	 * @author Remy Perona
	 */
	public function stop_generation() {
		if ( method_exists( $this->process, 'cancel_process' ) ) {
			$this->process->cancel_process();
		}
	}

	/**
	 * Deletes critical CSS files
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function clean_critical_css() {
		try {
			$directory = new \RecursiveDirectoryIterator( $this->critical_css_path, \FilesystemIterator::SKIP_DOTS );
		} catch ( \Exception $e ) {
			// no logging yet.
			return;
		}

		try {
			$files = new \RecursiveIteratorIterator( $directory, \RecursiveIteratorIterator::CHILD_FIRST );
		} catch ( \Exception $e ) {
			// no logging yet.
			return;
		}

		if ( ! $files ) {
			return;
		}

		foreach ( $files as $file ) {
			rocket_direct_filesystem()->delete( $file );
		}
	}

	/**
	 * Gets all public post types
	 *
	 * @since 2.11
	 * @author Remy Perona
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
		 * Filters the post types excluded from critical CSS generation
		 *
		 * @since 2.11
		 * @author Remy Perona
		 *
		 * @param array $excluded_post_types An array of post types names.
		 * @return array
		 */
		$excluded_post_types = apply_filters(
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
			]
		);

		$post_types = array_diff( $post_types, $excluded_post_types );
		$post_types = esc_sql( $post_types );
		$post_types = "'" . implode( "','", $post_types ) . "'";

		$rows = $wpdb->get_results( // WPCS: unprepared SQL ok.
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
	 * Gets all public taxonomies
	 *
	 * @since 2.11
	 * @author Remy Perona
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
		 * Filters the taxonomies excluded from critical CSS generation
		 *
		 * @since 2.11
		 * @author Remy Perona
		 *
		 * @param array $excluded_taxonomies An array of taxonomies names.
		 * @return array
		 */
		$excluded_taxonomies = apply_filters(
			'rocket_cpcss_excluded_taxonomies',
			[
				'post_format',
				'product_shipping_class',
				'karma-slider-category',
				'truethemes-gallery-category',
				'coupon_campaign',
			]
		);

		$taxonomies = array_diff( $taxonomies, $excluded_taxonomies );
		$taxonomies = esc_sql( $taxonomies );
		$taxonomies = "'" . implode( "','", $taxonomies ) . "'";

		$rows = $wpdb->get_results( // WPCS: unprepared SQL ok.
			"
			SELECT MAX( term_id ) AS ID, taxonomy
			FROM (
				SELECT term_id, taxonomy
				FROM $wpdb->term_taxonomy
				WHERE taxonomy IN ( $taxonomies )
				AND count > 0
			) AS taxonomies
			GROUP BY taxonomy
			"
		);

		return $rows;
	}

	/**
	 * Sets the items for which we generate critical CSS
	 *
	 * @since 2.11
	 * @author Remy Perona
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
		 * Filters the array containing the items to send to the critical CSS generator
		 *
		 * @since 2.11.4
		 * @author Remy Perona
		 *
		 * @param Array $this->items Array containing the type/url pair for each item to send.
		 */
		$this->items = apply_filters( 'rocket_cpcss_items', $this->items );
	}

	/**
	 * Determines if critical CSS is available for the current page
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @return bool|string False if critical CSS file doesn't exist, file path otherwise
	 */
	public function get_current_page_critical_css() {
		if ( is_home() && 'page' === get_option( 'show_on_front' ) ) {
			$name = 'home.css';
		} elseif ( is_front_page() ) {
			$name = 'front_page.css';
		} elseif ( is_category() ) {
			$name = 'category.css';
		} elseif ( is_tag() ) {
			$name = 'post_tag.css';
		} elseif ( is_tax() ) {
			$taxonomy = get_queried_object()->taxonomy;
			$name     = $taxonomy . '.css';
		} elseif ( is_singular() ) {
			$post_type = get_post_type();
			$name      = $post_type . '.css';
		} else {
			$name = 'front_page.css';
		}

		$file = $this->critical_css_path . $name;

		if ( ! rocket_direct_filesystem()->is_readable( $file ) ) {
			$critical_css = get_rocket_option( 'critical_css', '' );
			if ( ! empty( $critical_css ) ) {
				return 'fallback';
			}

			return false;
		}

		return $file;
	}
}
