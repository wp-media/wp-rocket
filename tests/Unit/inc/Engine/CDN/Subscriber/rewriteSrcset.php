<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Drivers\DriverInterface;
use WP_Rocket\Engine\CDN\Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::rewrite_srcset
 * @group  CDN
 */
class Test_RewriteSrcset extends TestCase {
	private $cdn;
	private $options;

	public function setUp(): void {
		parent::setUp();

		$this->cdn     = Mockery::mock( CDN::class );
		$this->options = Mockery::mock( Options_Data::class );

		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'is_rocket_post_excluded_option' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'https://example.org' );
		Functions\when( 'add_query_arg' )->justReturn( '' );
		Functions\when( 'wpm_apply_filters_typed' )->alias(
			function( $type, $hook, $value ) {
				return $value;
			}
		);
	}

	public function testShouldReturnOriginalHtmlWhenDriverReturnsFalse() {
		$driver = Mockery::mock( DriverInterface::class );
		$driver->shouldReceive( 'should_rewrite_url' )->andReturn( false );

		$subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			Mockery::mock( Options::class ),
			$driver
		);

		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( true );

		$html = '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x, https://example.org/wp-content/uploads/image-2x.jpg 2x">';

		$this->cdn->shouldNotReceive( 'rewrite_srcset' );

		$this->assertSame( $html, $subscriber->rewrite_srcset( $html ) );
	}

	public function testShouldRewriteSrcsetWhenDriverReturnsTrue() {
		$driver = Mockery::mock( DriverInterface::class );
		$driver->shouldReceive( 'should_rewrite_url' )->andReturn( true );

		$subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			Mockery::mock( Options::class ),
			$driver
		);

		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( true );

		$html      = '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x">';
		$rewritten = '<img srcset="https://cdn.example.org/wp-content/uploads/image.jpg 1x">';

		$this->cdn->shouldReceive( 'rewrite_srcset' )
			->once()
			->with( $html )
			->andReturn( $rewritten );

		$this->assertSame( $rewritten, $subscriber->rewrite_srcset( $html ) );
	}
}
