<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_DisplayErrorNotice extends TestCase {


	public function set_up()
	{
		parent::set_up();
		add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'remove_unused_css']);
	}

	public function tear_down()
	{
		parent::tear_down();
		remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'remove_unused_css']);
		delete_transient('wp_rocket_rucss_errors_count');
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		if($config['transient_exists']) {
			set_transient('wp_rocket_rucss_errors_count', true, MINUTE_IN_SECONDS);
		}

		ob_start();
		do_action('admin_notices');
		$actual = ob_get_clean();
		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $actual )
		);
	}

	public function remove_unused_css() {
		return $this->config['remove_unused_css'];
	}
}
