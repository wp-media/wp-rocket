<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PWA;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PWA;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PWA::exclude_service_worker
 *
 * @group PWA
 * @group ThirdParty
 */
class Test_ExcludeServiceWorker extends TestCase {
	public static $function_exists = false;

	private $pwa;

	protected function setUp(): void {
		parent::setUp();

		$this->pwa = new PWA();
	}

	public function testShouldDoNothingWhenFunctionDoesNotExist() {
		$this->assertSame(
			[],
			$this->pwa->exclude_service_worker( [] )
		);
	}

	public function testShouldAddExclusionWhenFunctionExists() {
		self::$function_exists = true;

		$this->assertSame(
			[
				'/wp.serviceworker/?',
			],
			$this->pwa->exclude_service_worker( [] )
		);
	}
}

namespace WP_Rocket\ThirdParty\Plugins;

function function_exists( $function ) {
    if ( $function === 'wp_get_service_worker_url' ) {
        return \WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PWA\Test_ExcludeServiceWorker::$function_exists;
    }

    return \function_exists( $function );
}
