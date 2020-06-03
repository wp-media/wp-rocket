<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\AdvancedCache::notice_content_not_ours
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_notice_html
 * @uses   ::rocket_notice_writing_permissions
 *
 * @group  AdvancedCache
 */
class Test_NoticeContentNotOurs extends TestCase {

	public function tearDown() {
		unset( $GLOBALS['pagenow'], $_GET['activate'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEchoNotice( $config, $expected ) {
		$GLOBALS['pagenow']             = $config['pagenow'];
		$_GET['activate']               = $config['activate'];
		$this->wp_rocket_advanced_cache = $config['constant'];

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( $config['cap'] );

		if ( $config['cap'] ) {
			Functions\expect( 'rocket_valid_key' )
				->once()
				->andReturn( $config['valid_key'] );
		} else {
			Functions\expect( 'rocket_valid_key' )->never();
		}

		if ( $config['cap'] && $config['valid_key'] && ! $this->wp_rocket_advanced_cache ) {

			Functions\expect( 'rocket_notice_writing_permissions' )
				->once()
				->with( 'wp-content/advanced-cache.php' )
				->andReturn( $config['message'] );

			Functions\expect( 'rocket_notice_html' )
				->once()
				->with(
					[
						'status'      => 'error',
						'dismissible' => '',
						'message'     => $config['message'],
					]
				)
				->andReturnNull();

		} else {
			Functions\expect( 'rocket_notice_writing_permissions' )->never();
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$advanced_cache = new AdvancedCache( '', null );
		$advanced_cache->notice_content_not_ours();
	}
}
