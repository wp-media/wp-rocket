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
	 * Appends the "Priority elements" action to the quick actions of the Dashboard sidebar.
	 *
	 * @since 3.24
	 *
	 * @param array $actions Quick actions data.
	 *
	 * @return array
	 */
	public function add_clear_performance_hints_quick_action( array $actions ): array {
		if ( ! rocket_valid_key() || ! current_user_can( 'rocket_manage_options' ) || ! $this->is_allowed() ) {
			return $actions;
		}

		$referer = rawurlencode( admin_url( 'options-general.php?page=wprocket' ) . '#dashboard' );

		$actions[] = [
			'id'           => 'rocket_clean_performance_hints',
			'title'        => __( 'Priority elements', 'rocket' ),
			'description'  => __( 'Reset stored optimizations related to Automatic Lazy Rendering, Critical Images, Preconnect to External Domains, and Preload Fonts.', 'rocket' ),
			'button_label' => __( 'Reset', 'rocket' ),
			'button_url'   => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_clean_performance_hints&_wp_http_referer=' . $referer ), 'rocket_clean_performance_hints' ),
		];

		return $actions;
	}

	/**
	 * Checks if at least one performance hints factory is allowed.
	 *
	 * @return bool
	 */
	private function is_allowed(): bool {
		foreach ( $this->factories as $factory ) {
			if ( $factory->get_context()->is_allowed() ) {
				return true;
			}
		}

		return false;
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
}
