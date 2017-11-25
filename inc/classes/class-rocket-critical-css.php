<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Handles the critical CSS generation process.
 *
 * @since 2.11
 * @author Remy Perona
 */
class Rocket_Critical_CSS {
	/**
	 * Background Process instance
	 *
	 * @since 2.11
	 * @var object $process Background Process instance.
	 * @access protected
	 */
	public $process;

	/**
	 * Items for which we generate a critical CSS
	 *
	 * @since 2.11
	 * @var array $items An array of items.
	 * @access protected
	 */
	public $items = array();

	/**
	 * The single instance of the class.
	 *
	 * @since 2.11
	 * @var object
	 */
	protected static $_instance;

	/**
	 * Get the main instance.
	 *
	 * Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @return object Main instance.
	 */
	public static function get_instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Class constructor.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function __construct() {
		$this->process = new Rocket_Background_Critical_CSS_Generation();
		$this->items[] = array(
			'type' => 'front_page',
			'url'  => home_url( '/' ),
		);
	}

	/**
	 * Initializes class and hooks.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function init() {
		add_action( 'admin_post_rocket_generate_critical_css', array( $this, 'init_critical_css_generation' ) );
		add_action( 'update_option_' . WP_ROCKET_SLUG, array( $this, 'generate_critical_css_on_activation' ), 11, 2 );
	}

	/**
	 * Performs the critical CSS generation
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function process_handler() {
		$this->process->cancel_process();
		$this->set_items();

		foreach ( $this->items as $item ) {
			$this->process->push_to_queue( $item );
		}

		$transient = array(
			'generated' => 0,
			'total'     => count( $this->items ),
			'items'     => array(),
		);

		set_transient( 'rocket_critical_css_generation_process_running', $transient, HOUR_IN_SECONDS );
		update_rocket_option( 'critical_css', array() );
		$this->process->save()->dispatch();
	}

	/**
	 * Launches the critical CSS generation from admin
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @see process_handler()
	 */
	public function init_critical_css_generation() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_generate_critical_css' ) ) {
			wp_nonce_ays( '' );
		}

		$this->process_handler();

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		die();
	}

	/**
	 * Launches the critical CSS generation when activating the async CSS option
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @see process_handler()
	 *
	 * @param array $old_value Previous values for WP Rocket settings.
	 * @param array $value     New values for WP Rocket settings.
	 */
	public function generate_critical_css_on_activation( $old_value, $value ) {
		if ( ! empty( $_POST[ WP_ROCKET_SLUG ] ) && isset( $old_value['async_css'], $value['async_css'] ) && ( $old_value['async_css'] !== $value['async_css'] ) && 1 === (int) $value['async_css'] ) {
			$this->process_handler();
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
			array(
				'public'             => true,
				'publicly_queryable' => true,
			)
		);

		/**
		 * Filters the post types excluded from critical CSS generation
		 *
		 * @since 2.11
		 * @author Remy Perona
		 *
		 * @param array $excluded_post_types An array of post types names.
		 * @return array
		 */
		$excluded_post_types = apply_filters( 'rocket_cpcss_excluded_post_types', array() );

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
			array(
				'public'             => true,
				'publicly_queryable' => true,
			)
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
		$excluded_taxonomies = apply_filters( 'rocket_cpcss_excluded_taxonomies', array(
			'post_format',
			'product_shipping_class',
		) );

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
		if ( 'page' === get_option( 'show_on_front' ) && ! empty( get_option( 'page_for_posts' ) ) ) {
			$this->items[] = array(
				'type' => 'home',
				'url'  => get_permalink( get_option( 'page_for_posts' ) ),
			);
		}

		$post_types = $this->get_public_post_types();

		foreach ( $post_types as $post_type ) {
			$this->items[] = array(
				'type' => $post_type->post_type,
				'url'  => get_permalink( $post_type->ID ),
			);
		}

		$taxonomies = $this->get_public_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			$this->items[] = array(
				'type' => $taxonomy->taxonomy,
				'url'  => get_term_link( (int) $taxonomy->ID, $taxonomy->taxonomy ),
			);
		}
	}
}
