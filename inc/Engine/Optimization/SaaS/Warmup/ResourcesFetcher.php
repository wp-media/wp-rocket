<?php

namespace WP_Rocket\Engine\Optimization\SaaS\Warmup;

use WP_Rocket_WP_Background_Process;

class ResourcesFetcher extends WP_Rocket_WP_Background_Process {
	const WARMUP_ENDPOINT = 'https://central-saas-pre.wp-rocket.me:30443/warmup';

	/**
	 * Action prefix
	 *
	 * @var string
	 */
	protected $prefix = 'rocket';

	/**
	 * Action name
	 *
	 * @var string
	 */
	protected $action = 'resources_warmup';

	/**
	 * Gets the resource content and sends the warmup request for it
	 *
	 * @since 3.8
	 *
	 * @param array $item Resource to warmup.
	 * @return bool
	 */
	protected function task( $item ) {
		if ( ! is_array( $item ) ) {
			return false;
		}

		if ( ! isset( $item['url'], $item['type'] ) ) {
			return false;
		}

		$response = wp_safe_remote_get(
			$item['url'],
			[
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			]
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return false;
		}

		return false;

		/**
		wp_remote_post(
			WARMUP_ENDPOINT,
			[
				[
					'url'     => $item['url'],
					'content' => $body,
					'type'    => $item['type']
				],
			]
		);
		*/
	}
}
