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
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::rewrite
 * @group  CDN
 */
class Test_Rewrite extends TestCase {
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

		$html = '<img src="https://example.org/wp-content/uploads/image.jpg">';

		$this->cdn->shouldNotReceive( 'rewrite' );

		$this->assertSame( $html, $subscriber->rewrite( $html ) );
	}

	public function testShouldRewriteHtmlWhenDriverReturnsTrue() {
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

		$html      = '<img src="https://example.org/wp-content/uploads/image.jpg">';
		$rewritten = '<img src="https://cdn.example.org/wp-content/uploads/image.jpg">';

		$this->cdn->shouldReceive( 'rewrite' )
			->once()
			->with( $html )
			->andReturn( $rewritten );

		$this->assertSame( $rewritten, $subscriber->rewrite( $html ) );
	}
}
