<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\Settings;

use WP_Admin_Bar;

trait AdminBarMenuTrait {
	/**
	 * Admin menu to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
	 * @param string       $id The menu id.
	 * @param string       $title The menu title.
	 * @param string       $action Menu action.
	 */
	protected function add_menu_to_admin_bar(
		WP_Admin_Bar $wp_admin_bar,
		string $id,
		string $title,
		string $action
	) {
		if ( ! rocket_valid_key() ) {
			return;
		}

		if ( ! is_admin() ) {
			return;
		}

		$referer = '';
		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );
			$referer     = '&_wp_http_referer=' . rawurlencode( remove_query_arg( 'fl_builder', $referer_url ) );
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wp-rocket',
				'id'     => $id,
				'title'  => $title,
				'href'   => wp_nonce_url( admin_url( "admin-post.php?action={$action}{$referer}" ), $action ),
			]
		);
	}

	/**
	 * Admin menu to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
	 * @param string       $id The menu id.
	 * @param string       $title The menu title.
	 * @param string       $action Menu action.
	 * @param bool         $context Context.
	 *
	 * @return void
	 */
	protected function add_url_menu_item_to_admin_bar(
		WP_Admin_Bar $wp_admin_bar,
		string $id,
		string $title,
		string $action,
		bool $context
	) {
		global $post;

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

		if ( ! $context ) {
			return;
		}

		$referer = '';

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );

			/**
			 * Filters to act on the referer url for the admin bar.
			 *
			 * @param string $uri Current uri.
			 */
			$referer = wpm_apply_filters_typed( 'string', 'rocket_admin_bar_referer', esc_url( $referer_url ) );
			$referer = '&_wp_http_referer=' . rawurlencode( remove_query_arg( 'fl_builder', $referer ) );
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wp-rocket',
				'id'     => $id,
				'title'  => $title,
				'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . $referer ), $action ),
			]
		);
	}

	/**
	 * Add button to dashboard
	 *
	 * @param bool   $context The feature context.
	 * @param string $title The button title.
	 * @param string $label Button label.
	 * @param string $action Button action.
	 * @param string $description Button description text.
	 *
	 * @return void
	 */
	public function dashboard_button( bool $context, string $title, string $label, string $action, string $description = '' ): void {
		if ( ! $context ) {
			return;
		}

		$data = [
			'action'      => $action,
			'title'       => $title,
			'label'       => $label,
			'description' => $description,
		];

		echo $this->generate( 'sections/clean-section', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
