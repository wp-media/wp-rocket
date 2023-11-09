<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::display_cpcss_mobile_section
 *
 * @group  AdminOnly
 * @group  CriticalPath
 * @group  CriticalPathAdminSubscriber
 */
class Test_DisplayCpcssMobileSection extends TestCase {
	use ProviderTrait;
	protected static $provider_class = 'Settings';

	private static $admin_user_id;
	private static $editor_user_id;

	private $options = [];

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::setAdminCap();

		//create an editor user that has the capability
		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		//create an editor user that has no capability
		self::$editor_user_id = static::factory()->user->create( [ 'role' => 'editor' ] );
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_settings_tools_content', 'display_cpcss_mobile_section', 10 );

		set_current_screen( 'settings_page_wprocket' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_settings_tools_content' );

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'setAsyncCssOption' ] );
		remove_filter( 'pre_get_rocket_option_cache_mobile', [ $this, 'setCacheMobileOption' ] );
		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'setDoCachingMobileFilesOption' ] );
		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'setAsyncCssMobileOption' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayCpcssMobileSection( $config, $expected ) {
		if ( $config['current_user_can'] ) {
			$user_id = static::$admin_user_id;
		} else {
			$user_id = static::$editor_user_id;
		}
		wp_set_current_user( $user_id );

		$this->options = $config['options'];

		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'setAsyncCssOption' ] );
		add_filter( 'pre_get_rocket_option_cache_mobile', [ $this, 'setCacheMobileOption' ] );
		add_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'setDoCachingMobileFilesOption' ] );
		add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'setAsyncCssMobileOption' ] );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_settings_tools_content' );

		return $this->format_the_html( ob_get_clean() );
	}

	public function setAsyncCssOption() {
		return $this->options['async_css'];
	}

	public function setCacheMobileOption() {
		return $this->options['cache_mobile'];
	}

	public function setDoCachingMobileFilesOption() {
		return $this->options['do_caching_mobile_files'];
	}

	public function setAsyncCssMobileOption() {
		return $this->options['async_css_mobile'];
	}
}
