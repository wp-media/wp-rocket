<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\Config\ConfigSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\Config\ConfigSubscriber::match_pattern_with_permalink_structure
 */
class Test_MatchPatternWithPermalinkStructure extends TestCase {
    
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( ! empty( $config['patterns'] ) ) {
            $this->set_permalink_structure( $config['permalink']['structure'] );
		}

		$this->assertSame( 
			$expected,
            apply_filters( 'get_rocket_option_cache_reject_uri', $config['patterns'] )
		);
	}
}
