<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\AMP;

use Mockery;
use Brain\Monkey\Filters;
use League\Container\Container;
use WP_Rocket\Admin\Options_Data;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::rewrite_cdn
 * @group  ThirdParty
 * @group  WithAmp
 */
class Test_RewriteCdn extends TestCase {
	private $amp;
	private $options;
	private $container;
	private $cdn_subscriber;

	public function setUp() {
		parent::setUp();

		$this->options        = Mockery::mock( Options_Data::class );
		$this->cdn_subscriber = Mockery::mock( Subscriber::class );
		$this->amp            = new AMP( $this->options, $this->cdn_subscriber );
	}

	public function testShouldDoExpected() {
		$html = '<html><head></head><body></body></html>';

		$this->cdn_subscriber->shouldReceive( 'rewrite' )
		              ->once()
		              ->with( $html )
					  ->andReturn( $html );
		$this->cdn_subscriber->shouldReceive( 'rewrite_srcset' )
		              ->once()
		              ->with( $html )
					  ->andReturn( $html );
		// Run it.
		$this->amp->rewrite_cdn( $html );
	}
}
