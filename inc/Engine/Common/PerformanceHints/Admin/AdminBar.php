<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Admin_Bar;
use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Settings\AdminBarMenuTrait;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class AdminBar extends Abstract_Render {
	use AdminBarMenuTrait;

	/**
	 * Options data instance.
	 *
	 * @var Options_data
	 */
	private $options;

	/**
	 * ATF context.
	 *
	 * @var ContextInterface
	 */
	private $atf_context;


	/**
	 * Constructor
	 *
	 * @param Options_Data     $options Options data instance.
	 * @param ContextInterface $atf_context ATF context.
	 * @param string           $template_path Template path.
	 */
	public function __construct( Options_Data $options, ContextInterface $atf_context, $template_path ) {
		parent::__construct( $template_path );

		$this->options     = $options;
		$this->atf_context = $atf_context;
	}

	/**
	 * Add performance hints data to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clear_performance_menu_item( WP_Admin_Bar $wp_admin_bar ): void {
		// TODO:Add lrc context check here
		if ( ! $this->atf_context->is_allowed() ) {
			return;
		}

		$title  = __( 'Clear Performance Hints data', 'rocket' );
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

		if (
			'local' === wp_get_environment_type()
			&&
			$this->atf_context->is_allowed()
		) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		if (
			$post
			&&
			! rocket_can_display_options()
		) {
			return;
		}

		if ( ! $this->atf_context->is_allowed() ) {
			return;
		}

		/**
		 * Filters the rocket `clear performance hints data of this url` option on admin bar menu.
		 *
		 * @since 3.17
		 *
		 * @param bool  $should_skip Should skip adding `clear performance hints of this url` option in admin bar.
		 * @param type  $post Post object.
		 */
		if ( apply_filters( 'rocket_skip_admin_bar_clean_performance_hints_option', false, $post ) ) {
			return;
		}

		$action = 'rocket_clean_performance_hints_url';

		$title = __( 'Clear performance hints data of this URL', 'rocket' );

		$this->add_url_menu_item_to_admin_bar(
			$wp_admin_bar,
			'clear-performance-hints-data-url',
			$title,
			$action,
			$this->atf_context->is_allowed()
		);
	}

	/**
	 * Display the dashboard button to clear performance hints data
	 *
	 * @return void
	 */
	public function display_dashboard_button() {
		$this->dashboard_button(
			$this->atf_context->is_allowed(),
			__( 'Performance Hints', 'rocket' ),
			esc_html__( 'Clear', 'rocket' ),
			'rocket_clean_performance_hints'
		);
	}
}
