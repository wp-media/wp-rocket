<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\PreloadFonts\Context\Context;

use Mockery;
use WP_Rocket\Engine\Media\PreloadFonts\Context\Context;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering WP_Rocket\Engine\Media\PreloadFonts\Context\Context::get_exclusions
 *
 * @group PreloadFonts
 */
class Test_GetExclusions extends TestCase {
	private $options;
	private $data_manager;
	private $context;

	protected function setUp(): void {
		parent::setUp();
		$this->options = Mockery::mock( Options_Data::class );
		$this->data_manager = Mockery::mock( DataManager::class );
		$this->context = new Context( $this->options, $this->data_manager );
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	/**
	* @dataProvider configTestData
	*/
	public function testShouldReturnExpected( $config, $expected ) {
		$this->data_manager->shouldReceive( 'get_lists' )
			->atMost()
			->once()
			->andReturn( $config['get_lists'] );

		$this->assertSame( $expected, $this->context->get_exclusions() );
	}
}
