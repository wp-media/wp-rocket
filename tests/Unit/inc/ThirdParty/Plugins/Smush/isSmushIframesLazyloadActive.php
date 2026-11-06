<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Smush;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\Smush;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Smush::is_smush_iframes_lazyload_active
 * @group ThirdParty
 * @group Smush
 */
class Test_IsSmushIframesLazyloadActive extends SmushSubscriberTestCase {
	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	public function testShouldNotDisableWPRocketLazyLoad() {
		$subscriber = new Smush( Mockery::mock( Options::class ), Mockery::mock( Options_Data::class ) );

		// Disabled.
		$this->mock_is_smush_lazyload_enabled(
			false,
			[
				'jpeg'   => true,
				'png'    => true,
				'gif'    => true,
				'svg'    => true,
				'iframe' => true,
			]
		);

		$this->assertEmpty( $subscriber->is_smush_iframes_lazyload_active( [] ) );

		// No image formats.
		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => true,
				'png'    => true,
				'gif'    => true,
				'svg'    => true,
				'foo'    => true,
				'iframe' => false,
			]
		);

		$this->assertEmpty( $subscriber->is_smush_iframes_lazyload_active( [] ) );

		// Empty formats.
		$this->mock_is_smush_lazyload_enabled(
			true,
			[]
		);

		$this->assertEmpty( $subscriber->is_smush_iframes_lazyload_active( [] ) );
	}

	public function testShouldDisableWPRocketLazyLoadWhenIframeFormat() {

		$subscriber = new Smush( Mockery::mock( Options::class ), Mockery::mock( Options_Data::class ) );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => false,
				'png'    => false,
				'gif'    => false,
				'svg'    => false,
				'foo'    => false,
				'iframe' => true,
			]
		);

		$this->assertContains( 'Smush', $subscriber->is_smush_iframes_lazyload_active( [] ) );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'iframe' => true,
			]
		);

		$this->assertContains( 'Smush', $subscriber->is_smush_iframes_lazyload_active( [] ) );
	}
}
