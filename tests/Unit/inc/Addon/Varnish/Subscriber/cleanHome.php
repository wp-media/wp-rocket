<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\Varnish\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use stdClass;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Addon\Varnish\Subscriber;
use WP_Rocket\Addon\Varnish\Varnish;

/**
 * @covers WP_Rocket\Addon\Varnish\Subscriber::clean_home
 * @group  Varnish
 * @group  Addon
 */
class Test_CleanHome extends TestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/i18n.php';
	}

	public function testShouldDoNothingWhenVarnishDisabled() {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[
				'varnish_auto_purge',
				0,
				0,
			],
		];

		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		$varnish = Mockery::mock( Varnish::class );
		$varnish->shouldNotReceive( 'purge' );

		$varnish_subscriber = new Subscriber( $varnish, $options );

		$varnish_subscriber->clean_home( 'wp-rocket/cache', '' );
	}

	public function testShouldPurgeHomeAndPaginationWhenVarnishEnabled() {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[
				'varnish_auto_purge',
				0,
				1,
			],
		];

		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		Functions\when( 'get_rocket_i18n_home_url' )->justReturn( 'http://example.org/' );
		$GLOBALS['wp_rewrite']                  = new stdClass();
		$GLOBALS['wp_rewrite']->pagination_base = 'page/';

		$varnish = Mockery::mock( Varnish::class );
		$varnish->shouldReceive( 'purge' )->once()->with( 'http://example.org/' );
		$varnish->shouldReceive( 'purge' )->once()->with( 'http://example.org/page/?regex' );

		$varnish_subscriber = new Subscriber( $varnish, $options );

		$varnish_subscriber->clean_home( 'wp-rocket/cache', '' );
	}
}
