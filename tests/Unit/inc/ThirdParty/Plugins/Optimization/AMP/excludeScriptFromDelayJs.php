<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\AMP;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::exclude_script_from_delay_js
 * @group  ThirdParty
 * @group  WithAmp
 */
class Test_ExcludeScriptFromDelayJs extends TestCase
{

	private $amp;
	private $options;
	private $cdn_subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->options        = Mockery::mock( Options_Data::class );
		$this->cdn_subscriber = Mockery::mock( Subscriber::class );
		$this->amp            = new AMP( $this->options, $this->cdn_subscriber );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected($config, $expected) {
		$this->assertEquals($expected['excluded'], $this->amp->exclude_script_from_delay_js($config['excluded']));
	}

}
