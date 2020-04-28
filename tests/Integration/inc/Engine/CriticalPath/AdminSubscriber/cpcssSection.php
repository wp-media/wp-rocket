<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AdminSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::cpcss_section
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_CpcssSection extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/AdminSubscriber/cpcssSectionIntegration.php';

	private $async_css;
	private $post_id;

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption'] );
		delete_post_meta( $this->post_id, '_rocket_exclude_async_css' );
		unset( $GLOBALS['post'] );

		parent::tearDown();
	}

	private function getActualHtml() {
		ob_start();
		rocket_display_cache_options_meta_boxes();

		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayCPCSSSection( $config, $expected ) {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_manage_options' );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );

		wp_set_current_user( $user_id );
		set_current_screen( 'edit-post' );

		$this->async_css = $config['options']['async_css'];
		$this->post_id   = $config['post']['ID'];

		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption'] );

		if ( $config['is_option_excluded'] ) {
			add_post_meta( $config['post']['ID'], '_rocket_exclude_async_css', $config['is_option_excluded'], true );
		}

		$GLOBALS['post'] = (object) [
			'ID'          => $config['post']['ID'],
			'post_status' => $config['post']['post_status'],
			'post_type'   => $config['post']['post_type']
		];

		$this->assertTrue( $this->filesystem->exists( 'wp-content/cache/critical-css/1/posts/post-2.css' ) );
		$this->assertTrue( $this->filesystem->exists( 'wp-content/plugins/wp-rocket/views/metabox/cpcss/container.php' ) );
		$this->assertContains(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);
	}

	public function setCPCSSOption() {
		return $this->async_css;
	}
}
