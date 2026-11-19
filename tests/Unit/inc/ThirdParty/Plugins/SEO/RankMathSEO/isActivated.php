<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\RankMathSEO;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO::is_activated
 *
 * @group RankMathSEO
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests RankMathSEO::is_activated() against the presence/absence of RANK_MATH_FILE.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['rank_math_file'] ) {
			$this->constants['RANK_MATH_FILE'] = $config['rank_math_file'];
		}

		$this->assertSame( $expected, RankMathSEO::is_activated() );
	}
}
