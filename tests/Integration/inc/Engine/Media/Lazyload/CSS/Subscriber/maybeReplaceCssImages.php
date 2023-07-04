<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Tests\Integration\FilterTrait;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::maybe_replace_css_images
 */
class Test_maybeReplaceCssImages extends FilesystemTestCase {

	use FilterTrait;

	protected $path_to_test_data = '/inc/Engine/Media/Lazyload/CSS/Subscriber/integration/maybeReplaceCssImages.php';

	public function set_up()
	{
		parent::set_up();
		$this->unregisterAllCallbacksExcept('rocket_buffer', 'maybe_replace_css_images');
	}

	public function tear_down()
	{
		$this->restoreWpFilter('rocket_buffer');
		parent::tear_down();
	}

	/**
     * @dataProvider providerTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {

        $this->assertSame($expected, apply_filters('rocket_buffer', $config['html']));
    	foreach($expected['files'] as $path => $content) {

			$this->assertSame($content['exists'], $this->filesystem->exists($path));

			if(! $content['exists']) {
				continue;
			}

			$this->assertSame($content['content'], $this->filesystem->get_contents($path));

		}
	}
}
