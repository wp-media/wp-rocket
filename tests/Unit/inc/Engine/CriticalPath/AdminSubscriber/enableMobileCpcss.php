<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::enable_mobile_cpcss
 * @group  CriticalPath
 */
class Test_EnableMobileCpcss extends TestCase {
	private $beacon;
	private $options;
	private $subscriber;

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'check_ajax_referer' )->justReturn( true );

		$this->beacon     = Mockery::mock( Beacon::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new AdminSubscriber(
			$this->options,
			$this->beacon,
			'wp-content/cache/critical-css/',
			'wp-content/plugins/wp-rocket/views/cpcss'
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEnableMobileCpcss( $config, $update ) {
		Functions\when( 'current_user_can' )->justReturn( $config[ 'rocket_manage_options' ] );

		if ( ! $update ) {
			Functions\expect( 'wp_send_json_error' )->once();
			$this->options->shouldReceive( 'set' )->never();
		} else {
			Functions\expect( 'rocket_get_constant' )
				->with( 'WP_ROCKET_SLUG', 'wp_rocket_settings' )
				->andReturn( 'wp_rocket_settings' );

			$this->options->shouldReceive( 'get_options' )->andReturn( $this->options );
			$this->options->shouldReceive( 'set' )->with( 'async_css_mobile', 1 );

			Functions\expect( 'update_option' )->with( 'wp_rocket_settings', $this->options )->once();
			Functions\expect( 'wp_send_json_success' )->once();
		}

		$this->subscriber->enable_mobile_cpcss();
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'enableMobileCpcss' );
	}
}
