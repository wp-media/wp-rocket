<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\FilterTrait;

/**
 * @covers \WP_Rocket\Engine\Preload\Subscriber::exclude_private_post_uri
 * @group  Preload
 */
class Test_ExcludePrivatePostUri extends AdminTestCase
{
	use FilterTrait;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

    public function set_up() {
        parent::set_up();
        $this->set_permalink_structure( "/%postname%/" );

		$this->unregisterAllCallbacksExcept('rocket_preload_exclude_urls', 'exclude_private_post_uri');

	}

    public function tear_down() {
		$this->restoreWpFilter( 'rocket_preload_exclude_urls' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
        if ( $config['have_posts'] ) {
            wp_insert_post( [ 'post_title' => 'test 4', 'post_status' => 'private' ] );
        }

        $this->assertSame( $expected, apply_filters( 'rocket_preload_exclude_urls', $config['regex'] ) );
	}
}
