<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Uncode;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\ThirdParty\Themes\Uncode;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Uncode::exclude_js
 *
 * @group Uncode
 * @group Themes
 */
class Test_ExcludeJs extends FilesystemTestCase {
	private $event;
	private $subscriber;
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Uncode/excludeJs.php';

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		add_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
		add_filter( 'template_directory_uri', [ $this, 'set_template_uri' ] );

		$container = apply_filters( 'rocket_container', '' );
		$this->event = $container->get( 'event_manager' );
	}

	public function tear_down() {
		global $wp_theme_directories;
		unset( $wp_theme_directories['virtual'] );

		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		remove_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
		remove_filter( 'template_directory_uri', [ $this, 'set_template_uri' ] );

		$this->event->remove_subscriber( $this->subscriber );

		parent::tear_down();
	}

	public function set_template_uri() {
		return 'http://example.org/wp-content/themes/uncode';
	}

	public function set_stylesheet() {
		return 'uncode';
	}

	public function set_stylesheet_root() {
		global $wp_theme_directories;

		$wp_theme_directories['virtual'] = $this->filesystem->getUrl( 'wp-content/themes/' );

		return $this->filesystem->getUrl( 'wp-content/themes/' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->subscriber = new Uncode();

		$this->event->add_subscriber( $this->subscriber );

		$actual = apply_filters( 'rocket_exclude_js', $config['exclusions'] );

		foreach ( $expected as $item ) {
			$this->assertContains( $item, $actual );
		}
	}
}
