<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\PreconnectExternalDomains\Database\Rows\PreconnectExternalDomains;

use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Row\PreconnectExternalDomains;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Rows\PreconnectExternalDomains::has_preconnect_external_domains
 *
 * @group  PreconnectExternalDomains
 */
class Test_HasPrconnectExternalDomains extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected($config, $expected) {
		$preconnect_external_domains = new PreconnectExternalDomains((object) $config);

		$this->assertSame( $expected, $preconnect_external_domains->has_preconnect_external_domains() );
	}
}
