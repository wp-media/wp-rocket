<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

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
	use ProviderTrait;

	protected static $provider_class = 'Post';
	private $async_css_mobile;
	private $async_css;
	private $post_id;
	private static $user_id;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::setAdminCap();
		self::$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public function set_up() {
		parent::set_up();

		$this->set_permalink_structure( '/%postname%/' );
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );
		add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'setCPCSSMobileOption' ] );


		set_current_screen( 'edit-post' );
	}

	public function tear_down() {
		unset( $GLOBALS['post'] );

		parent::tear_down();

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );
		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'setCPCSSMobileOption' ] );
		delete_post_meta( $this->post_id, '_rocket_exclude_async_css' );
	}

	/**
	 * @dataProvider providerTestData
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
}
