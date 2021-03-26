<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Frontend;

use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;

class APIClient extends AbstractAPIClient {

	/**
	 * SAAS main API path.
	 *
	 * @var string
	 */
	protected $request_path = 'api';

	/**
	 * Calls Central Saas API.
	 *
	 * @param string $html    HTML content.
	 * @param string $url     HTML url.
	 * @param array  $options Array with options sent to Saas API.
	 *
	 * @return array
	 */
	public function optimize( string $html, string $url, array $options ): array {
		$args = [
			'body'    => [
				'html'   => $html,
				'url'    => $url,
				'config' => $options,
			],
			'timeout' => 5,
		];

		$sent = $this->handle_post( $args );

		if ( ! $sent ) {
			return [
				'code'    => $this->response_code,
				'message' => $this->error_message,
			];
		}

		$default = [
			'code'     => 400,
			'message'  => 'Bad json',
			'contents' => [
				'shakedCSS'      => '',
				'unProcessedCss' => [],
			],
		];

		$result = json_decode( $this->response_body, true );
		$result = wp_parse_args( (array) $result, $default );

		return [
			'code'            => $result['code'],
			'message'         => $result['message'],
			'css'             => $result['contents']['shakedCSS'],
			'unprocessed_css' => ( is_array( $result['contents']['unProcessedCss'] ) ? $result['contents']['unProcessedCss'] : [] ),
		];
	}
}
