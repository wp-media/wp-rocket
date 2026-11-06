<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::delete_cpcss
 *
 * @group  Subscribers
 * @group  CriticalPath
 * @group  vfs
 */
class Test_DeleteCpcss extends TestCase {
	use SubscriberTrait;

	public function setUp() : void {
		parent::setUp();
		$this->setUpTests();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDeleteCPCSS( $config, $expected ) {
		$post_id             = isset( $config['post']['id'] ) ? $config['post']['id'] : 0;
		$post_type           = isset( $config['post']['type'] ) ? $config['post']['type'] : 'post';
		$config['async_css'] = isset( $config['async_css'] ) ? $config['async_css'] : 0;

		Functions\when( 'current_user_can' )->justReturn( $config['current_user_can'] );

		if ( isset( $config['async_css'] ) ) {
			$this->options
				->shouldReceive( 'get' )
				->with( 'async_css', 0 )
				->andReturn( $config['async_css'] );
		}

		if ( ! $config['current_user_can'] || ! $config['async_css'] ) {
			Functions\expect( 'get_post_type' )->never();
		} else {
			Functions\expect( 'get_post_type' )
				->with( $post_id )
				->once()
				->andReturn( $post_type );
		}

		if ( isset( $config['async_css_mobile'] ) ) {
			$this->options
				->shouldReceive( 'get' )
				->with( 'async_css_mobile', 0 )
				->andReturn( $config['async_css_mobile'] );
		}

		if ( $expected['desktop'] ) {
			$this->processor_service
				->shouldReceive( 'process_delete' )
				->with( $config['desktop'] );
		}

		if ( $expected['mobile'] ) {
			$this->processor_service
				->shouldReceive( 'process_delete' )
				->with( $config['mobile'] );
		}

		$this->subscriber->delete_cpcss( $post_id );
	}
}
