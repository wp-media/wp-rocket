<?php

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber::maybe_revert_uid_for_nonce_actions
 * @group   ConvertPlug
 */
class Test_ExcludedFromRucss extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame( $expected, apply_filters( 'rocket_rucss_inline_atts_exclusions', $config['excluded'] ));
	}

}
