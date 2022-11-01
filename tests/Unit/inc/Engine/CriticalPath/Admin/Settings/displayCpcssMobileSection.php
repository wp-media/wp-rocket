<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\CriticalPath\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\AdminTrait;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::cpcss_section
 *
 * @group  CriticalPath
 */
class Test_DisplayCpcssMobileSection extends TestCase {
	use AdminTrait;

	private $settings;

	protected function setUp() : void {
		parent::setUp();

		$this->setUpMocks();
		Functions\stubTranslationFunctions();

		$this->settings = Mockery::mock( Settings::class . '[generate]', [
				$this->options,
				$this->beacon,
				$this->critical_css,
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
				->allows()->get( $option_key, 0 )
				->andReturns( $option );
		}

		$config['beacon'] = isset( $config['beacon'] ) ? $config['beacon'] : '';

		if ( ! empty( $config['beacon'] ) ) {
			$this->beacon
				->allows()->get_suggest( 'async' )
				->andReturns( $config['beacon'] );
		}

		Functions\when( 'current_user_can' )->justReturn( $config['current_user_can'] );

		if ( $expected ) {
			$this->settings->expects()
			->generate( 'activate-cpcss-mobile', ['beacon' => $config['beacon'] ] )
			->once()
			->andReturns( '' );
		} else {
			$this->settings->expects()->generate()->never();
		}

		ob_start();
		$this->settings->display_cpcss_mobile_section();
		ob_get_clean();

	}
}
