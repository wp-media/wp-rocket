<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_js
 *
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludeJs extends TestCase {
	private $elementor;

	public function setUp(): void {
		parent::setUp();

		$this->elementor = new Elementor( null, Mockery::mock( HTML::class ));
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'minify_concatenate_js', false )
			->andReturn( $config['combine_js'] );

		$this->options->shouldReceive( 'get' )
			->with( 'cache_logged_user', false )
			->andReturn( $config['user_cache'] );

		Functions\when( 'is_user_logged_in' )->justReturn( $config['logged_in'] );

		$this->assertSame(
			$expected,
			$this->elementor->exclude_js( [] )
		);
	}
}
