<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::cpcss_section
 * @uses   ::rocket_direct_filesystem
 * @uses   ::is_rocket_post_excluded_option
 *
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_CpcssSection extends FilesystemTestCase {
	protected      $path_to_test_data = '/inc/Engine/CriticalPath/AdminSubscriber/cpcssSectionIntegration.php';
	private        $async_css;
	private        $post_id;
	private static $user_id;

	public static function wpSetUpBeforeClass( $factory ) {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_manage_options' );
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );
		delete_post_meta( $this->post_id, '_rocket_exclude_async_css' );
		unset( $GLOBALS['post'] );

		parent::tearDown();
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_after_options_metabox' );

		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayCPCSSSection( $config, $expected ) {
		wp_set_current_user( static::$user_id );
		set_current_screen( 'edit-post' );

		$this->async_css = $config['options']['async_css'];
		$this->post_id   = $config['post']['ID'];

		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );

		if ( $config['is_option_excluded'] ) {
			add_post_meta( $this->post_id, '_rocket_exclude_async_css', $config['is_option_excluded'], true );
		}

		$GLOBALS['post'] = (object) [
			'ID'          => $this->post_id,
			'post_status' => $config['post']['post_status'],
			'post_type'   => $config['post']['post_type'],
		];

		Functions\when( 'wp_create_nonce' )->justReturn( 'wp_rest_nonce' );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);
	}

	public function setCPCSSOption() {
		return $this->async_css;
	}
}
