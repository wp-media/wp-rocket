<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\Menu;

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

		if ( 'local' === wp_get_environment_type() ) {
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
}
