<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\WebP\Subscriber;

use Mockery;
use WP_Rocket\Addon\WebP\Subscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Subscriber as CDNSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

class Test_ChangeYoutubeThumbnail extends TestCase
{
	private $subscriber;
	private $options;
	private $cdn;

	public function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->cdn = Mockery::mock( CDNSubscriber::class );
		$this->subscriber = new Subscriber(
			$this->options,
			Mockery::mock( Options::class ),
			$this->cdn,
			''
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options->expects()->get( 'cache_webp', 0 )->andReturn($config['has_webp']);
		$this->assertSame($expected, $this->subscriber->change_youtube_thumbnail($config['extension']));
	}
}
