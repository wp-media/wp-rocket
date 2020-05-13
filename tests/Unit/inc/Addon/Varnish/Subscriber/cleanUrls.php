<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\Varnish\Subscriber;

use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Addon\Varnish\Subscriber;
use WP_Rocket\Addon\Varnish\Varnish;

/**
 * @covers WP_Rocket\Addon\Varnish\Subscriber::clean_urls
 * @group  Varnish
 * @group  Addon
 */
class Test_CleanUrls extends TestCase {
	private $options;
	private $subscriber;
	private $varnish;

	public function setUp() {
		$this->options    = Mockery::mock( Options_Data::class );
		$this->varnish    = Mockery::mock( Varnish::class );
		$this->subscriber = new Subscriber( $this->varnish, $this->options );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldDoExpected( $config, $urls, $expected_clean_urls ) {

		Filters\expectApplied( 'do_rocket_varnish_http_purge' )
			->once()
			->andReturn( $config['do_rocket_varnish_http_purge'] );

		if ( ! $config['do_rocket_varnish_http_purge'] ) {
			$this->options->shouldReceive( 'get' )
				->with( 'varnish_auto_purge', 0 )
				->andReturn( $config['varnish_auto_purge'] );
		}

		if ( ! empty( $expected_clean_urls ) ) {
			Functions\expect( 'get_option' )
				->with( 'permalink_structure' )
				->andReturn( $config['permalink_structure'] );

			foreach ( $expected_clean_urls as $url ) {
				$this->varnish->shouldReceive( 'purge' )->with( $url . '?regex' );
			}
		} else {
			$this->varnish->shouldNotReceive( 'purge' );
		}

		$this->subscriber->clean_urls( $urls );
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'cleanUrls' );
	}
}
