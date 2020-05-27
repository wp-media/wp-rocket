<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::cpcss_section
 * @uses   ::rocket_direct_filesystem
 * @uses   ::is_rocket_post_excluded_option
 *
 * @group  AdminOnly
 * @group  CriticalPath
 * @group  CriticalPathAdminSubscriber
 */
class Test_CpcssSection extends TestCase {
	private        $async_css_mobile;
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
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );
		add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'setCPCSSMobileOption' ] );


		set_current_screen( 'edit-post' );
	}

	public function tearDown() {
		unset( $GLOBALS['post'] );

		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );
		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'setCPCSSMobileOption' ] );
		delete_post_meta( $this->post_id, '_rocket_exclude_async_css' );
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testShouldDisplayCPCSSSection( $config, $expected ) {
		wp_set_current_user( static::$user_id );

		$this->async_css_mobile = $config['options']['async_css_mobile'];
		$this->async_css        = $config['options']['async_css'];
		$this->post_id          = $config['post']->ID;
		$GLOBALS['post']        = $config['post'];

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

	public function setCPCSSMobileOption() {
		return $this->async_css_mobile;
	}

	public function setCPCSSOption() {
		return $this->async_css;
	}

	public function dataProvider() {
		$dir  = WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/CriticalPath/Admin/Post/';
		$data = $this->getTestData( $dir, str_replace( '.php', '', basename( __FILE__ ) ) );

		return isset( $data['test_data'] )
			? $data['test_data']
			: $data;
	}
}
