<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Saas\Admin;

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
	 * RUCSS optimize url context.
	 *
	 * @var ContextInterface
	 */
	private $rucss_url_context;

	/**
	 * Constructor
	 *
	 * @param Options_Data     $options Options data instance.
	 * @param ContextInterface $rucss_url_context RUCSS optimize url context.
	 * @param string           $template_path Template path.
	 */
	public function __construct( Options_Data $options, ContextInterface $rucss_url_context, $template_path ) {
		parent::__construct( $template_path );

		$this->options           = $options;
		$this->rucss_url_context = $rucss_url_context;
	}

	/**
	 * Add clean SaaS data to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clean_saas_menu_item( $wp_admin_bar ) {
		$title  = __( 'Clear Used CSS', 'rocket' );
		$action = 'rocket_clean_saas';

		if ( 'local' === wp_get_environment_type() ) {
			return;
		}

		if (
			'local' === wp_get_environment_type()
			&&
			(bool) $this->options->get( 'remove_unused_css', 0 )
		) {
			return;
		}

		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		$this->add_menu_to_admin_bar(
			$wp_admin_bar,
			'clean-saas',
			$title,
			$action
		);
	}

	/**
	 * Add clean SaaS URL data to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clean_url_menu_item( WP_Admin_Bar $wp_admin_bar ) {
		if ( 'local' === wp_get_environment_type() && $this->rucss_url_context->is_allowed() ) {
			return;
		}

		global $post;

		/**
		 * Filters the rocket `clear used css of this url` option on admin bar menu.
		 *
		 * @since 3.12.1
		 *
		 * @param bool  $should_skip Should skip adding `clear used css of this url` option in admin bar.
		 * @param type  $post Post object.
		 */
		if ( wpm_apply_filters_typed( 'boolean', 'rocket_skip_admin_bar_clear_used_css_option', false, $post ) ) {
			return;
		}

		$action = 'rocket_clean_saas_url';

		$title = __( 'Clear Used CSS of this URL', 'rocket' );

		$this->add_url_menu_item_to_admin_bar(
			$wp_admin_bar,
			'clear-saas-url',
			$title,
			$action,
			$this->rucss_url_context->is_allowed()
		);
	}

	/**
	 * Display the dashboard button to clean SaaS features
	 *
	 * @return void
	 */
	public function display_dashboard_button() {
		if (
			'local' === wp_get_environment_type()
			&&
			$this->rucss_url_context->is_allowed()
		) {
			return;
		}

		$this->dashboard_button(
			$this->rucss_url_context->is_allowed(),
			__( 'Used CSS', 'rocket' ),
			esc_html__( 'Clear', 'rocket' ),
			'rocket_clean_saas',
			esc_html__( 'This action will clear the used CSS files.', 'rocket' )
		);
	}
}
