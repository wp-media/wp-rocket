<?php

namespace WP_Rocket\Tests\Integration;

trait CriticalCssLoopbackTrait {
	/**
	 * Mocks the loopback CriticalCSSGeneration::dispatch() sends to admin-ajax.php, keyed for the current user and blog.
	 *
	 * @return array
	 */
	protected function critical_css_loopback_fixture(): array {
		$url = add_query_arg(
			[
				'action' => 'rocket_critical_css_generation',
				'nonce'  => wp_create_nonce( 'rocket_critical_css_generation' ),
			],
			admin_url( 'admin-ajax.php' )
		);

		return [
			esc_url_raw( $url ) => [
				'response' => [ 'code' => 200 ],
				'body'     => '',
			],
		];
	}
}
