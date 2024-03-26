<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Saas\Admin\Notices;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Saas\Admin\Notices;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Saas\Admin\Notices::add_localize_script_data
 *
 * @group  Saas
 */
class Test_AddLocalizeScriptData extends TestCase {
	private $options;
	private $notices;
	private $atf_context;

	public function setUp(): void {
		parent::setUp();

		$this->options  = Mockery::mock( Options_Data::class );
		$this->atf_context  = Mockery::mock( ContextInterface::class );
		$this->notices = new Notices( $this->options, Mockery::mock( Beacon::class ), $this->atf_context );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options->shouldReceive( 'get' )
			->once()
			->with( 'remove_unused_css', 0 )
			->andReturn( $config['remove_unused_css'] );

		$this->atf_context->shouldReceive( 'is_allowed' )
			->andReturn( $config['atf'] );

		Functions\when( 'get_transient' )->justReturn( $config['transient'] );

		$this->assertSame(
			$expected,
			$this->notices->add_localize_script_data( $config['data'] )
		);
	}
}
