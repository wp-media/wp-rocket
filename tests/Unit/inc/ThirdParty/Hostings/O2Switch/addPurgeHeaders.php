<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\O2Switch;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\O2Switch;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\O2Switch::add_purge_headers
 *
 * @group  O2Switch
 * @group  ThirdParty
 */
class Test_AddPurgeHeaders extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ) {
		$constants = isset( $input['constants'] ) ? $input['constants'] : [] ;
		foreach ( $constants as $constant => $value ) {
			$this->constants[$constant] = $value;
		}

		$headers = isset( $input['headers'] ) ? $input['headers'] : [] ;

		$o2switch = new O2Switch();

		$this->assertSame(
			$expected,
			$o2switch->add_purge_headers( $headers )
		);
	}
}
