<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Beacon\Beacon;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Support\Data;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Beacon\Beacon::get_suggest
 * @group  Beacon
 */
class Test_GetSuggest extends TestCase {
	private $beacon;
	private $options;

	public function setUp() : void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->beacon  = new Beacon( $this->options, 'views/settings', Mockery::mock( Data::class ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnSuggestData( $locale, $doc_id, $expected) {
		Functions\when( 'get_user_locale' )->justReturn( $locale );

		$this->assertSame(
			$expected,
			$this->beacon->get_suggest( $doc_id )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'get-suggest' );
	}
}
