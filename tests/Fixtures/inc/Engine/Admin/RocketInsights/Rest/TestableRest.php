<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Rest;

use WP_Rocket\Engine\Admin\RocketInsights\Rest;

/**
 * Thin concrete subclass that exposes get_url_validation_payload for unit testing.
 */
class TestableRest extends Rest {

	/**
	 * Expose the protected get_url_validation_payload method for testing.
	 *
	 * @param string $url The URL to validate.
	 * @return array The validation payload.
	 */
	public function expose_get_url_validation_payload( string $url ): array {
		return $this->get_url_validation_payload( $url );
	}
}
