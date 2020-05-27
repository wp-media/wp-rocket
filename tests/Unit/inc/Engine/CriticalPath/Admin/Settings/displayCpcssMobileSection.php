<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\CriticalPath\Admin\Settings;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;  
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\GenerateTrait;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::cpcss_section
 *
 * @group  CriticalPath
 */
class Test_DisplayCpcssMobileSection extends TestCase {
	use GenerateTrait;

	protected static $mockCommonWpFunctionsInSetUp = true;

	protected function setUp() {
		parent::setUp();

		$this->setUpMocks();

		$this->settings = Mockery::mock( Settings::class . '[generate]', [
				$this->options,
				$this->beacon,
				Mockery::mock( CriticalCSS::class ),
				WP_ROCKET_PLUGIN_ROOT . 'views/cpcss/',
			]
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayCPCSSMobileSection( $config, $expected ) {
		foreach ( $config['options'] as $option_key => $option ) {
			$this->options
				->shouldReceive( 'get' )
				->with( $option_key, 0 )
				->andReturn( $option );
		}

		$config['beacon'] = isset( $config['beacon'] ) ? $config['beacon'] : '';

		if ( ! empty( $config['beacon'] ) ) {
			$this->beacon->shouldReceive( 'get_suggest' )
						->once()
						->andReturn( $config['beacon'] );
		}

		Functions\when( 'current_user_can' )->justReturn( $config['current_user_can'] );

		$this->settings->shouldReceive( 'generate' )
				   ->with( 'activate-cpcss-mobile', ['beacon' => $config['beacon'] ] )
				   ->andReturn( '' );

		ob_start();
		$this->settings->display_cpcss_mobile_section();
		ob_get_clean();

	}
}
