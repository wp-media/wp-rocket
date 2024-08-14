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
	 * ATF context.
	 *
	 * @var ContextInterface
	 */
	private $atf_context;

	/**
	 * LRC context.
	 *
	 * @var ContextInterface
	 */
	private $lrc_context;


	/**
	 * Constructor
	 *
	 * @param ContextInterface $atf_context ATF context.
	 * @param ContextInterface $lrc_context LRC context.
	 * @param string           $template_path Template path.
	 */
	public function __construct( ContextInterface $atf_context, ContextInterface $lrc_context, $template_path ) {
		parent::__construct( $template_path );

		$this->atf_context = $atf_context;
		$this->lrc_context = $lrc_context;
	}

	/**
	 * Add performance hints data to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clear_performance_menu_item( WP_Admin_Bar $wp_admin_bar ): void {
		if (
			! $this->atf_context->is_allowed()
			&& ! $this->lrc_context->is_allowed()
		) {
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

		/**
		 * Filters the rocket `clear performance hints data of this url` option on admin bar menu.
		 *
		 * @since 3.17
		 *
		 * @param bool  $should_skip Should skip adding `clear performance hints of this url` option in admin bar.
		 * @param type  $post Post object.
		 */
		if ( wpm_apply_filters_typed( 'boolean', 'rocket_skip_admin_bar_clean_performance_hints_option', true, $post ) ) {
			return;
		}

		$action = 'rocket_clean_performance_hints_url';

		$title = __( 'Clear performance hints data of this URL', 'rocket' );

		$this->add_url_menu_item_to_admin_bar(
			$wp_admin_bar,
			'clear-performance-hints-data-url',
			$title,
			$action,
			$this->atf_context->is_allowed() || $this->lrc_context->is_allowed()
		);
	}

	/**
	 * Display the dashboard button to clear performance hints data
	 *
	 * @return void
	 */
	public function display_dashboard_button() {
		$context = $this->lrc_context->is_allowed() || $this->atf_context->is_allowed();

		$this->dashboard_button(
			$context,
			__( 'Performance Hints', 'rocket' ),
			esc_html__( 'Clear', 'rocket' ),
			'rocket_clean_performance_hints',
			__( 'This action will clear data for Optimize Critical Images and Lazy Render Content.', 'rocket' )
		);
	}
}
