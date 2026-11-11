<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\RapidLoad;

/**
 * Test class covering WP_Rocket\ThirdParty\Plugins\Optimization\RapidLoad::rocket_clean_cache_on_deactivation
 */
class Test_RocketCleanCacheOnDeactivation extends TestCase {
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new RapidLoad();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {

        Functions\expect( 'get_option' )
			->once()
			->with( 'autoptimize_uucss_settings' )
			->andReturn( $config['autoptimize_uucss_settings'] );

		if ( isset( $config['file'] ) ) {
            if ( $expected['file'] !== $config['file'] ) {
                Functions\expect( 'rocket_dismiss_box' )->never();
			    Functions\expect( 'rockrocket_clean_domainet_dismiss_box' )->never();
            }
            else{
                Functions\expect('rocket_dismiss_box')
                    ->once()
                    ->with('rocket_warning_plugin_modification')
                    ->andReturnNull();

			    Functions\expect( 'rocket_clean_domain' )->once()->andReturnNull();
            }
		}

        $this->subscriber->rocket_clean_cache_on_deactivation( $config['file'] );
	}
}
