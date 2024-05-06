<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Saas\Admin;

use WP_Admin_Bar;
use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class AdminBar extends Abstract_Render {
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
	 * RUCSS optimize url context.
	 *
	 * @var ContextInterface
	 */
	private $rucss_url_context;

	/**
	 * Constructor
	 *
	 * @param Options_Data     $options Options data instance.
	 * @param ContextInterface $atf_context ATF context.
	 * @param ContextInterface $rucss_url_context RUCSS optimize url context.
	 * @param string           $template_path Template path.
	 */
	public function __construct( Options_Data $options, ContextInterface $atf_context, ContextInterface $rucss_url_context, $template_path ) {
		parent::__construct( $template_path );

		$this->options           = $options;
		$this->atf_context       = $atf_context;
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
		if ( ! rocket_valid_key() ) {
			return;
		}

		if ( 'local' === wp_get_environment_type() ) {
			return;
		}

		if ( ! is_admin() ) {
			return;
		}

		if (
			! $this->atf_context->is_allowed()
			&&
			! (bool) $this->options->get( 'remove_unused_css', 0 )
		) {
			return;
		}

		$title = __( 'Clear Critical Images', 'rocket' );

		if (
			(bool) $this->options->get( 'remove_unused_css', 0 )
			&&
			current_user_can( 'rocket_remove_unused_css' )
		) {
			$title = __( 'Clear Used CSS', 'rocket' );
		}

		$referer = '';
		$action  = 'rocket_clean_saas';

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );
			$referer     = '&_wp_http_referer=' . rawurlencode( remove_query_arg( 'fl_builder', $referer_url ) );
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wp-rocket',
				'id'     => 'clean-saas',
				'title'  => $title,
				'href'   => wp_nonce_url( admin_url( "admin-post.php?action={$action}{$referer}" ), $action ),
			]
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
		global $post;

		if ( 'local' === wp_get_environment_type() ) {
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

		if ( ! $this->atf_context->is_allowed()
			&&
			! $this->rucss_url_context->is_allowed()
		) {
			return;
		}

		/**
		 * Filters the rocket `clear used css of this url` option on admin bar menu.
		 *
		 * @since 3.12.1
		 *
		 * @param bool  $should_skip Should skip adding `clear used css of this url` option in admin bar.
		 * @param type  $post Post object.
		 */
		if ( apply_filters( 'rocket_skip_admin_bar_clear_used_css_option', false, $post ) ) {
			return;
		}

		$referer = '';
		$action  = 'rocket_clean_saas_url';

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );

			/**
			 * Filters to act on the referer url for the admin bar.
			 *
			 * @param string $uri Current uri.
			 */
			$referer = (string) apply_filters( 'rocket_admin_bar_referer', esc_url( $referer_url ) );
			$referer = '&_wp_http_referer=' . rawurlencode( remove_query_arg( 'fl_builder', $referer ) );
		}
		$title = __( 'Clear Critical Images of this URL', 'rocket' );

		if ( $this->rucss_url_context->is_allowed() ) {
			$title = __( 'Clear Used CSS of this URL', 'rocket' );
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wp-rocket',
				'id'     => 'clear-saas-url',
				'title'  => $title,
				'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . $referer ), $action ),
			]
		);
	}

	/**
	 * Display the dashboard button to clean SaaS features
	 *
	 * @return void
	 */
	public function display_dashboard_button() {
		if ( 'local' === wp_get_environment_type() ) {
			return;
		}

		if ( ! $this->atf_context->is_allowed()
			&&
			! $this->rucss_url_context->is_allowed()
		) {
			return;
		}

		$title = __( 'Critical Images Cache', 'rocket' );
		$label = esc_html__( 'Clear Critical Images', 'rocket' );

		if ( $this->rucss_url_context->is_allowed() ) {
			$title = __( 'Remove Used CSS Cache', 'rocket' );
			$label = esc_html__( 'Clear Used CSS', 'rocket' );
		}

		$data = [
			'action' => 'rocket_clean_saas',
			'title'  => $title,
			'label'  => $label,
		];

		echo $this->generate( 'sections/clean-section', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
