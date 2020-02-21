<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\Varnish\Subscriber;

use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Addon\Varnish\Subscriber;
use WP_Rocket\Addon\Varnish\Varnish;

/**
 * @covers WP_Rocket\Addon\Varnish\Subscriber::clean_domain
 * @group  Varnish
 * @group  Addon
 */
class Test_CleanDomain extends TestCase {

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

		$varnish_subscriber->clean_domain( 'wp-rocket/cache', '', 'http://example.org' );
	}

	public function testShouldPurgeOnceWhenVarnishEnabled() {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[
				'varnish_auto_purge',
				0,
				1,
			],
		];

		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		$varnish = Mockery::mock( Varnish::class );
		$varnish->shouldReceive( 'purge' )->once()->with( 'http://example.org/?regex' );

		$varnish_subscriber = new Subscriber( $varnish, $options );

		$varnish_subscriber->clean_domain( 'wp-rocket/cache', '', 'http://example.org' );
	}
}
