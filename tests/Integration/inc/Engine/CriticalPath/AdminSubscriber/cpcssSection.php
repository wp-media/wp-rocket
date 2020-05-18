<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::cpcss_section
 * @uses   ::rocket_direct_filesystem
 * @uses   ::is_rocket_post_excluded_option
 *
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_CpcssSection extends TestCase {
	private        $async_css;
	private        $post_id;
	private static $user_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		CapTrait::setAdminCap();
		self::$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public function setUp() {
		parent::setUp();

		$this->set_permalink_structure( '/%postname%/' );
		Functions\when( 'wp_create_nonce' )->justReturn( 'wp_rest_nonce' );
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );


		set_current_screen( 'edit-post' );
	}

	public function tearDown() {
		unset( $GLOBALS['post'] );

		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );
		delete_post_meta( $this->post_id, '_rocket_exclude_async_css' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayCPCSSSection( $config, $expected ) {
		wp_set_current_user( static::$user_id );

		$this->async_css = $config['options']['async_css'];
		$this->post_id   = $config['post']->ID;
		$GLOBALS['post'] = $config['post'];

		if ( $config['is_option_excluded'] ) {
			add_post_meta( $this->post_id, '_rocket_exclude_async_css', $config['is_option_excluded'], true );
		}

		ob_start();
		do_action( 'rocket_after_options_metabox' );
		$actual = ob_get_clean();
		if ( ! empty( $actual ) ) {
			$actual = $this->format_the_html( $actual );
		}

		$this->assertSame(
			$this->format_the_html( $expected['html'] ),
			$actual
		);
	}

	public function setCPCSSOption() {
		return $this->async_css;
	}
}
