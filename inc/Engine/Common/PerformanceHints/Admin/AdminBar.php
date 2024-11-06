<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Admin_Bar;
use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\Admin\Settings\AdminBarMenuTrait;

class AdminBar extends Abstract_Render {
	use AdminBarMenuTrait;

	/**
	 * Array of factories
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * Constructor
	 *
	 * @param array  $factories Array of factories.
	 * @param string $template_path Template path.
	 */
	public function __construct( array $factories, $template_path ) {
		parent::__construct( $template_path );

		$this->factories = $factories;
	}

	/**
	 * Add performance hints data to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clear_performance_menu_item( WP_Admin_Bar $wp_admin_bar ): void {
		if ( empty( $this->factories ) ) {
			return;
		}

		$title  = __( 'Clear Priority Elements', 'rocket' );
		$action = 'rocket_clean_performance_hints';

		$this->add_menu_to_admin_bar(
			$wp_admin_bar,
			'clear-performance-hints',
			$title,
			$action
		);
	}

	/**
	 * Add clear performance hints URL data to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clear_url_performance_hints_menu_item( WP_Admin_Bar $wp_admin_bar ) {
		global $post;

		/**
		 * Filters the rocket `clear performance hints data of this url` option on admin bar menu.
		 *
		 * @since 3.17
		 *
		 * @param bool  $should_skip Should skip adding `clear performance hints of this url` option in admin bar.
		 * @param type  $post Post object.
		 */
		if ( wpm_apply_filters_typed( 'boolean', 'rocket_skip_admin_bar_clean_performance_hints_option', false, $post ) ) {
			return;
		}

		$action = 'rocket_clean_performance_hints_url';

		$title = __( 'Clear Priority Elements of this URL', 'rocket' );

		$this->add_url_menu_item_to_admin_bar(
			$wp_admin_bar,
			'clear-performance-hints-data-url',
			$title,
			$action,
			! empty( $this->factories )
		);
	}

	/**
	 * Display the dashboard button to clear performance hints data
	 *
	 * @return void
	 */
	public function display_dashboard_button() {
		$context = ! empty( $this->factories );

		$this->dashboard_button(
			$context,
			__( 'Priority Elements', 'rocket' ),
			esc_html__( 'Clear', 'rocket' ),
			'rocket_clean_performance_hints',
			__( 'This action will clear the Critical Images and Lazily Rendered Content.', 'rocket' )
		);
	}
}
