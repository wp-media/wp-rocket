<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\WarmUp;

use WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient as BaseAPIClient;
use WP_Rocket\Engine\Common\Utils;

class APIClient extends BaseAPIClient {

	/**
	 * Send the link to Above the fold SaaS.
	 *
	 * @param string $url Url to be sent.
	 * @return array
	 */
	public function add_to_atf_queue( string $url ): array {
		$is_home = Utils::is_home( $url );
		
		$url = add_query_arg(
			[
				'wpr_imagedimensions' => 1,
			],
			$url
		);

		$config = [
			'optimization_list' => '',
			'is_home'           => $is_home,
		];

		return $this->add_to_queue( $url, $config );
	}
}
